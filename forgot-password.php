<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';
require_once 'includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
        $stmt->bind_param('ss', $email, $token);
        $stmt->execute();

        $resetLink = "https://stbasile.ct.ws/update-password.php?token=$token";
        $subject = "Password Reset Request";
        $body = "Click the link below to reset your password:<br><a href=\"$resetLink\">Reset Password</a>";

        sendMail($email, $subject, $body);
        $message = "Password reset email sent.";
    } else {
        $error = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | St. Basile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow rounded-4">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Forgot Password</h3>
                    <?php if (isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
                    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Registered Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>

                    <div class="mt-3 text-center small">
                        <a href="javascript:void(0);" onclick="redirectToLogin();">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function redirectToLogin(){
        window.location.href ="login.php";
    }
</script>
</body>
</html>
