<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

require_once 'includes/db.php'; 
require_once 'includes/config.php';

date_default_timezone_set('Africa/Kigali');

// Optional: Global expired token cleanup
$conn->query("DELETE FROM password_resets WHERE expiration_time < NOW()");

$status = false;
$toastMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['names'];

        $token = bin2hex(random_bytes(50));
        $resetLink = "http://localhost/mass_reader_scheduler/update-password.php?token=$token";
        $expiration = (new DateTime('now', new DateTimeZone('Africa/Kigali')))
                        ->add(new DateInterval('PT10M'))
                        ->format('Y-m-d H:i:s');
        $current_time = date('Y-m-d H:i:s');

        // Delete previous tokens for this email
        $cleanup = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $cleanup->bind_param("s", $email);
        $cleanup->execute();

        $insert = $conn->prepare("INSERT INTO password_resets (names, email, token, reset_link, expiration_time, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("ssssss",$username, $email, $token, $resetLink, $expiration, $current_time);

        if ($insert->execute()) {
            $subject = "🔐 Password Reset Request";
            $body = '
                <div style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;">
                    <div style="max-width: 600px; margin: auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                        <h2 style="color: #0d6efd;">Hello ' . htmlspecialchars($username) . ',</h2>
                        <p>You requested to reset your password on <strong>' . WEBSITE_NAME . '</strong>. Click the button below to proceed:</p>
                        <p style="text-align: center;">
                            <a href="' . $resetLink . '" style="background-color: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a>
                        </p>
                        <p>This link is valid for <strong>10 minutes</strong>.</p>
                        <p>If you did not request this, you can safely ignore this email.</p>
                        <p style="font-size: 0.9em; color: #777;">Need help? Contact our support team.<br>— ' . WEBSITE_NAME . ' Team</p>
                    </div>
                </div>';

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = MAILHOST;
                $mail->SMTPAuth = true;
                $mail->Username = USERNAME;
                $mail->Password = PASSWORD;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = PORT;

                $mail->setFrom(SEND_FROM, WEBSITE_NAME);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->send();

                $status = true;
                $toastMessage = 'Password reset link sent to your email.';
            } catch (Exception $e) {
                $toastMessage = "Email could not be sent. Error: " . $mail->ErrorInfo;
            }
        } else {
            $toastMessage = "Failed to store reset token.";
        }
    } else {
        $toastMessage = "No account found with that email.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .spinner-border-sm { display: none; }
        .btn-loading .spinner-border-sm { display: inline-block; }
        .btn-loading .btn-text { display: none; }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow rounded-4">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Forgot Password</h3>
                    <form method="POST" onsubmit="handleSubmit(this)">
                        <div class="mb-3">
                            <label class="form-label">Registered Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            <span class="btn-text">Send Reset Link</span>
                        </button>
                    </form>
                    <div class="mt-3 text-center small">
                        <a href="login.php">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11;">
    <div id="toastMessage" class="toast align-items-center text-white <?= $status ? 'bg-success' : 'bg-danger' ?> border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body"><?= $toastMessage ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function handleSubmit(form) {
        document.getElementById('submitBtn').classList.add('btn-loading');
    }

    <?php if (!empty($toastMessage)) : ?>
        new bootstrap.Toast(document.getElementById('toastMessage')).show();
    <?php endif; ?>
</script>
</body>
</html>
