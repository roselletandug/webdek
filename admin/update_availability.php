<?php
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$theme_name = $_POST['theme_name'] ?? '';
$is_available = $_POST['is_available'] ?? '';

if (!$theme_name || ($is_available !== '0' && $is_available !== '1')) {
    echo json_encode(['success' => false, 'message' => 'Invalid input parameters.']);
    exit;
}

$conn = getDbConnection();

// Check if theme exists
$stmt = $conn->prepare("SELECT id FROM theme_availability WHERE theme_name = ?");
$stmt->bind_param("s", $theme_name);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update existing record
    $stmt->close();
    $stmt = $conn->prepare("UPDATE theme_availability SET is_available = ? WHERE theme_name = ?");
    $stmt->bind_param("is", $is_available, $theme_name);
    $success = $stmt->execute();
    $stmt->close();
} else {
    // Insert new record
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO theme_availability (theme_name, is_available) VALUES (?, ?)");
    $stmt->bind_param("si", $theme_name, $is_available);
    $success = $stmt->execute();
    $stmt->close();
}

$conn->close();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Availability updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
?>
