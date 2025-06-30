<?php
session_start();
require_once 'db.php'; // Database connection file

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize($_POST['fullname'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    error_log("Signup POST data: password='$password', confirm_password='$confirm_password'");

    if (!$fullname || !$email || !$username || !$password || !$confirm_password) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: index.html');
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Password and Confirm Password do not match.';
        header('Location: index.html');
        exit;
    }

    $conn = getDbConnection();

    // Check if username already exists in customers table
    $stmt = $conn->prepare("SELECT id FROM customers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        $_SESSION['error'] = 'Username already exists.';
        header('Location: index.html');
        exit;
    }
    $stmt->close();

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new customer record
    $stmt = $conn->prepare("INSERT INTO customers (fullname, email, username, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullname, $email, $username, $passwordHash);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        $_SESSION['success'] = 'Signup successful. Please login.';
        header('Location: index.html');
        exit;
    } else {
        error_log("Signup failed: " . $stmt->error);
        $stmt->close();
        $conn->close();
        $_SESSION['error'] = 'Signup failed. Please try again.';
        header('Location: index.html');
        exit;
    }
} else {
    header('Location: index.html');
    exit;
}
?>
