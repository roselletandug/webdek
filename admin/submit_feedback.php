<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($lastName === '' || $firstName === '' || $rating < 1 || $rating > 5) {
        // Invalid input, redirect back or show error
        header('Location: ../customer/index.html?error=invalid_input');
        exit;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO feedback (lastName, firstName, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $lastName, $firstName, $rating, $comment);

    if ($stmt->execute()) {
        // Success, redirect or show success message
        header('Location: ../customer/index.html?success=feedback_submitted');
    } else {
        // Error
        header('Location: ../customer/index.html?error=submit_failed');
    }

    $stmt->close();
    $conn->close();
} else {
    // Invalid request method
    header('Location: ../customer/index.html');
    exit;
}
?>
