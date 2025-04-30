<?php
session_start();
date_default_timezone_set('Africa/Kigali'); // Use Africa/Kigali timezone

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';

$error = '';
$email = '';

// Check and display previous error
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

// Capture IP Address
$ip_address = $_SERVER['REMOTE_ADDR'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Store session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['names'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = strtolower($user['role']);

        // Log successful login
        $log_stmt = $conn->prepare("INSERT INTO logs (user_id, names, email, user_name, action, ip_address, created_at)
                                    VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $action = "User logged in";
        $log_stmt->bind_param("isssss", $user['id'], $user['names'], $user['email'], $user['names'], $action, $ip_address);
        $log_stmt->execute();

        header('Location: dashboard_router.php');
        exit;
    } else {
        // Log failed login
        $action = "Failed login attempt for email: $email";
        $log_stmt = $conn->prepare("INSERT INTO logs (user_id, names, email, user_name, action, ip_address, created_at)
                                    VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $null = null;
        $log_stmt->bind_param("isssss", $null, $email, $email, $email, $action, $ip_address);
        $log_stmt->execute();

        $_SESSION['login_error'] = 'Invalid login credentials.';
        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | St. Basile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .position-relative {
            position: relative;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow rounded-4">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Login to St. Basile</h3>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" name="email" id="email" class="form-control" required autofocus
                                   value="<?= htmlspecialchars($email) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label d-flex justify-content-between">
                                <span>Password</span>
                                <a href="javascript:void(0)" class="small" onclick="redirectToForgotPassword();">Forgot password?</a>
                            </label>
                            <div class="position-relative">
                                <input type="password" name="password" id="password" class="form-control" required>
                                <span class="password-toggle" onclick="togglePassword()">
                                    <i id="toggleIcon" class="fa-solid fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>

                    <div class="mt-3 text-center small">
                        Don't have an account? <a href="javascript:void(0);" onclick="redirectToRegister();">Create one</a>
                    </div>
                </div>

                <div class="card-footer text-center small text-muted">
                    <p>S<sup>t</sup> Basile Community &copy; <?= date('Y') ?> All rights reserved!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        const type = passwordField.getAttribute('type');

        if (type === 'password') {
            passwordField.setAttribute('type', 'text');
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        } else {
            passwordField.setAttribute('type', 'password');
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    }

    function redirectToRegister() {
        window.location.href = 'register.php';
    }

    function redirectToForgotPassword() {
        window.location.href = 'forgot-password.php';
    }
</script>
</body>
</html>
