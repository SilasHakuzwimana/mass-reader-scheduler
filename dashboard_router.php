<?php
session_start();

// If no user is logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect based on role
switch ($_SESSION['role']) {
    case 'admin':
        header('Location: pages/admin_dashboard.php');
        break;
    case 'coordinator':
        header('Location: pages/coordinator_dashboard.php');
        break;
    case 'reader':
        header('Location: pages/reader_dashboard.php');
        break;
    default:
        // Unknown role: log out
        session_destroy();
        header('Location: login.php');
        break;
}
exit;
?>
