<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';

$error = '';  // Initialize error message as an empty string
$email = '';

// Check if there's an error message from the previous attempt and clear it after the next page load.
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);  // Clear the error after showing it
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare and execute the query to fetch user from the database
    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if the user exists and the password matches
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['names'];
        $_SESSION['role'] = strtolower($user['role']); // lowercase for consistency

        // Redirect based on user role
        header('Location: dashboard_router.php');
        exit;
    } else {
        // Error handling if credentials are invalid
        $_SESSION['login_error'] = 'Invalid login credentials.';  // Store error in session for next load
        header('Location: login.php');  // Redirect back to login page with error
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for eye icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
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

                    <!-- Display error message if there's any -->
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

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Password Toggle Script -->
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
</script>
<script>
    function redirectToRegister(){
        window.location.href = 'register.php';
    }
    function redirectToForgotPassword(){
        window.location.href = 'forgot-password.php';
    }
</script>
</body>
</html>
