<?php
session_start();
require_once 'db.php'; // Database connection file

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$email || !$new_password || !$confirm_password) {
        $_SESSION['error'] = 'Please fill in all required fields.';
            header('Location: ../customer/index.html');
            exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'New Password and Confirm Password do not match.';
            header('Location: ../customer/index.html');
            exit;
    }

    $conn = getDbConnection();

    // Check if email exists in customers table
    $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($userId);
        $stmt->fetch();
        $stmt->close();

        // Hash the new password
        $passwordHash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the customers table
        $updateStmt = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
        $updateStmt->bind_param("si", $passwordHash, $userId);

        if ($updateStmt->execute()) {
            $_SESSION['success'] = 'Password has been reset successfully.';
            $updateStmt->close();
            $conn->close();
        header('Location: ../customer/index.html');
        exit;
        } else {
            $_SESSION['error'] = 'Failed to reset password. Please try again.';
            $updateStmt->close();
            $conn->close();
        header('Location: ../customer/index.html');
        exit;
        }
    } else {
        $_SESSION['error'] = 'Email address not found.';
        $stmt->close();
        $conn->close();
        header('Location: forgot_password.html');
        exit;
    }
} else {
    header('Location: forgot_password.html');
    exit;
}
?>
