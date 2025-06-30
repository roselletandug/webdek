<?php
session_start();
require_once 'db.php'; // Database connection file

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $_SESSION['error'] = 'Please enter both username and password.';
        header('Location: index.html');
        exit;
    }

    $conn = getDbConnection();

    // Check in customers table
    $stmt = $conn->prepare("SELECT id, password FROM customers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($userId, $passwordHash);
        $stmt->fetch();
        if (password_verify($password, $passwordHash)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'customer';
            $stmt->close();
            $conn->close();
            header('Location: dashboard.php'); // Redirect to customer dashboard or home page
            exit;
        }
    }
    $stmt->close();

    // Check in admin table if not found in customers
    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($userId, $passwordHash);
        $stmt->fetch();
        if (password_verify($password, $passwordHash)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'admin';
            $stmt->close();
            $conn->close();
            header('Location: admin/index.html'); // Redirect to admin dashboard
            exit;
        }
    }
    $stmt->close();
    $conn->close();

    $_SESSION['error'] = 'Invalid username or password.';
    header('Location: index.html');
    exit;
} else {
    header('Location: index.html');
    exit;
}
?>
