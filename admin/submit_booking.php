<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect back with error message or show error page
    header('Location: ../customer/index.html?error=method_not_allowed');
    exit;
}

// Helper function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate and sanitize inputs
$lastName = sanitize($_POST['lastName'] ?? '');
$firstName = sanitize($_POST['firstName'] ?? '');
$contactNo = sanitize($_POST['contactNo'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$theme = sanitize($_POST['theme'] ?? '');
$specificTheme = sanitize($_POST['specificTheme'] ?? '');
$colorTheme = sanitize($_POST['colorTheme'] ?? '');
$dateOfEvent = sanitize($_POST['dateOfEvent'] ?? '');
$timeOfEvent = sanitize($_POST['timeOfEvent'] ?? '');
$nameOfCelebrant = sanitize($_POST['nameOfCelebrant'] ?? '');
$ageOfCelebrant = intval($_POST['ageOfCelebrant'] ?? 0);
$venueAddress = sanitize($_POST['venueAddress'] ?? '');
$package = sanitize($_POST['package'] ?? '');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Submit Booking</title>
</head>
<body>
    <form method="POST" action="submit_booking.php">
        <!-- Other form fields -->
        <label>Package:
            <select name="package" required>
                <?php
                $packages = ['full_setup'];
                foreach ($packages as $pkg) {
                    echo "<option value=\"$pkg\">$pkg</option>";
                }
                ?>
            </select>
        </label>
        <!-- Submit button -->
        <button type="submit">Submit Booking</button>
    </form>
</body>
</html>
<?php
$remarks = sanitize($_POST['remarks'] ?? '');

// Validate required fields
$requiredFields = [
    'lastName' => $lastName,
    'firstName' => $firstName,
    'contactNo' => $contactNo,
    'address' => $address,
    'specificTheme' => $specificTheme,
    'colorTheme' => $colorTheme,
    'dateOfEvent' => $dateOfEvent,
    'timeOfEvent' => $timeOfEvent,
    'nameOfCelebrant' => $nameOfCelebrant,
    'ageOfCelebrant' => $ageOfCelebrant,
    'venueAddress' => $venueAddress,
    'package' => $package,
    'remarks' => $remarks
];

foreach ($requiredFields as $field => $value) {
    if (empty($value)) {
        header('Location: ../customer/index.html?error=missing_' . $field);
        exit;
    }
}

// Validate contact number pattern (starts with 09 followed by 9 digits)
if (!preg_match('/^09\d{9}$/', $contactNo)) {
    error_log('Invalid contact number: ' . $contactNo);
    header('Location: ../customer/index.html?error=invalid_contactNo');
    exit;
}

// Validate age range
if ($ageOfCelebrant < 0 || $ageOfCelebrant > 122) {
    header('Location: ../customer/index.html?error=invalid_ageOfCelebrant');
    exit;
}

// Validate date format and check if it's not in the past
$eventDate = DateTime::createFromFormat('Y-m-d', $dateOfEvent);
$today = new DateTime();
if (!$eventDate || $eventDate < $today) {
    header('Location: ../customer/index.html?error=invalid_dateOfEvent');
    exit;
}

// Handle file uploads
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$sampleEventDesignPath = '';
if (isset($_FILES['sampleEventDesign']) && $_FILES['sampleEventDesign']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['sampleEventDesign']['tmp_name'];
    $fileName = basename($_FILES['sampleEventDesign']['name']);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validate file type
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        header('Location: ../customer/index.html?error=invalid_sampleEventDesign_type');
        exit;
    }
    
    // Validate file size (max 5MB)
    if ($_FILES['sampleEventDesign']['size'] > 5 * 1024 * 1024) {
        header('Location: ../customer/index.html?error=sampleEventDesign_too_large');
        exit;
    }
    
    $targetFile = $uploadDir . uniqid() . '_' . $fileName;
    if (move_uploaded_file($tmpName, $targetFile)) {
        $sampleEventDesignPath = 'admin/uploads/' . basename($targetFile);
    } else {
        error_log('Failed to move uploaded sampleEventDesign file.');
        header('Location: ../customer/index.html?error=upload_sampleEventDesign_failed');
        exit;
    }
}

$imagePaymentPath = '';
if (isset($_FILES['imagePayment']) && $_FILES['imagePayment']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['imagePayment']['tmp_name'];
    $fileName = basename($_FILES['imagePayment']['name']);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validate file type
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        header('Location: ../customer/index.html?error=invalid_imagePayment_type');
        exit;
    }
    
    // Validate file size (max 5MB)
    if ($_FILES['imagePayment']['size'] > 5 * 1024 * 1024) {
        header('Location: ../customer/index.html?error=imagePayment_too_large');
        exit;
    }
    
    $targetFile = $uploadDir . uniqid() . '_' . $fileName;
    if (move_uploaded_file($tmpName, $targetFile)) {
        $imagePaymentPath = 'admin/uploads/' . basename($targetFile);
    } else {
        error_log('Failed to move uploaded imagePayment file.');
        header('Location: ../customer/index.html?error=upload_imagePayment_failed');
        exit;
    }
} else {
    header('Location: ../customer/index.html?error=missing_imagePayment');
    exit;
}

// Insert data into bookings table
try {
    $conn = getDbConnection();

    $stmt = $conn->prepare("INSERT INTO bookings (last_name, first_name, contact_no, address, theme, specific_theme, color_theme, date_of_event, time_of_event, name_of_celebrant, age_of_celebrant, venue_address, package, sample_event_design, image_payment, remarks, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $status = 'Pending';

    if (!$stmt) {
        throw new Exception('DB Prepare failed: ' . $conn->error);
    }

    // Fixed: Correct number of parameter types (17 parameters = 17 type specifiers)
    $stmt->bind_param(
        'ssssssssssissssss',
        $lastName,
        $firstName,
        $contactNo,
        $address,
        $theme,
        $specificTheme,
        $colorTheme,
        $dateOfEvent,
        $timeOfEvent,
        $nameOfCelebrant,
        $ageOfCelebrant,
        $venueAddress,
        $package,
        $sampleEventDesignPath,
        $imagePaymentPath,
        $remarks,
        $status
    );

    error_log('Bind param executed with values: ' . json_encode([
        $lastName,
        $firstName,
        $contactNo,
        $address,
        $theme,
        $specificTheme,
        $colorTheme,
        $dateOfEvent,
        $timeOfEvent,
        $nameOfCelebrant,
        $ageOfCelebrant,
        $venueAddress,
        $package,
        $sampleEventDesignPath,
        $imagePaymentPath,
        $remarks,
        $status
    ]));

    if ($stmt->execute()) {
        $bookingId = $conn->insert_id;
        error_log('Booking successfully inserted with ID: ' . $bookingId);
        header('Location: ../customer/index.html?success=1&booking_id=' . $bookingId);
    } else {
        throw new Exception('DB Execute failed: ' . $stmt->error);
    }

    $stmt->close();
    
} catch (Exception $e) {
    error_log('Booking submission error: ' . $e->getMessage());
    header('Location: ../customer/index.html?error=db_error');
    exit;
}
?>