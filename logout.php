<?php
session_start();
require_once 'includes/db.php';

// Capture IP address
$ip_address = $_SERVER['REMOTE_ADDR'];

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $email = $_SESSION['email'];

    // Log the logout action
    $action = "User logged out";
    $log_stmt = $conn->prepare("INSERT INTO logs (user_id, names, email, user_name, action, ip_address, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $log_stmt->bind_param("isssss", $user_id, $user_name, $email, $user_name, $action, $ip_address);
    $log_stmt->execute();

    // Destroy the session
    session_unset();
    session_destroy();

    // Redirect to login page
    header('Location: index.php');
    exit;
}
?>
