<?php
session_start();
date_default_timezone_set('Africa/Kigali');

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// Optional: block access for non-admin users
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>
