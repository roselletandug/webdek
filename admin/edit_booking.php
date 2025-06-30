<?php
require_once 'db.php';

// Start output buffering to catch accidental output
ob_start();
header('Content-Type: application/json');

try {
    $conn = getDbConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
        exit;
    }

    // Validate required field
    if (empty($_POST['customer_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Customer ID is required.']);
        exit;
    }

    // Sanitize and extract form values
    $id = intval($_POST['customer_id']); // Use customer_id as the ID
    $lastName = $_POST['last_name'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $contactNo = $_POST['contact_no'] ?? '';
    $address = $_POST['address'] ?? '';
    $theme = $_POST['theme'] ?? '';
    $specificTheme = $_POST['specific_theme'] ?? '';
    $colorTheme = $_POST['color_theme'] ?? '';
    $dateOfEvent = $_POST['date_of_event'] ?? '';
    $timeOfEvent = $_POST['time_of_event'] ?? '';
    $nameOfCelebrant = $_POST['name_of_celebrant'] ?? '';
    $ageOfCelebrant = intval($_POST['age_of_celebrant'] ?? 0);
    $venueAddress = $_POST['venue_address'] ?? '';
    $package = $_POST['package'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $status = $_POST['status'] ?? '';

    // Update the record - use remarks as the reason field
    $stmt = $conn->prepare("UPDATE bookings SET 
        last_name = ?, 
        first_name = ?, 
        contact_no = ?, 
        address = ?, 
        theme = ?, 
        specific_theme = ?, 
        color_theme = ?, 
        date_of_event = ?, 
        time_of_event = ?, 
        name_of_celebrant = ?, 
        age_of_celebrant = ?, 
        venue_address = ?, 
        package = ?, 
        remarks = ?, 
        status = ?,
        reason = ?
        WHERE customer_id = ?");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        'ssssssssssisssssi',
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
        $remarks,
        $status,
        $remarks, // Fixed: Use $remarks as the reason value
        $id
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Fetch updated record
    $select = $conn->prepare("SELECT * FROM bookings WHERE customer_id = ?");
    $select->bind_param('i', $id);
    $select->execute();
    $result = $select->get_result();
    $updatedBooking = $result->fetch_assoc();

    // Include remarks as reason in the response
    $updatedBooking['reason'] = $updatedBooking['remarks'] ?? '';

    echo json_encode(['success' => true, 'booking' => $updatedBooking]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;

} finally {
    // Remove unexpected output if any
    $unexpected = ob_get_clean();
    if (!empty($unexpected)) {
        file_put_contents('php_output_log.txt', $unexpected);
    }
}
?>