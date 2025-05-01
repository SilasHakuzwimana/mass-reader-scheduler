<?php
require_once 'includes/db.php';
require_once 'includes/config.php';
date_default_timezone_set('Africa/Kigali');

// Google reCAPTCHA Secret Key
$secretKey = SECRET_KEY;

$token = $_GET['token'] ?? null;
$reset = null;
$error = '';

if ($token) {
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $reset = $result->fetch_assoc();

        $expiration = new DateTime($reset['expiration_time'], new DateTimeZone('Africa/Kigali'));
        $now = new DateTime('now', new DateTimeZone('Africa/Kigali'));

        if ($now > $expiration) {
            $delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $delete->bind_param("s", $token);
            $delete->execute();

            $error = "⏰ This reset link has expired. Please request a new one.";
            $reset = null;
        }
    } else {
        $error = "⚠️ Invalid or expired password reset token.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
        // Google reCAPTCHA verification
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptchaData = [
            'secret' => $secretKey,
            'response' => $recaptchaResponse
        ];
        $recaptchaVerify = file_get_contents($recaptchaUrl . '?' . http_build_query($recaptchaData));
        $recaptchaResult = json_decode($recaptchaVerify);

        if (!$recaptchaResult->success) {
            $error = "⚠️ reCAPTCHA verification failed. Please try again.";
        } else {
            // Process password reset
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (strlen($password) < 6) {
                $error = "Password must be at least 6 characters.";
            } elseif ($password !== $confirm) {
                $error = "Passwords do not match.";
            } else {
                $newPassword = password_hash($password, PASSWORD_BCRYPT);

                $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $update->bind_param("ss", $newPassword, $reset['email']);
                $update->execute();

                $delete = $conn->prepare("UPDATE password_resets SET token = NULL, reset_link = NULL WHERE email = ?");
                $delete->bind_param("s", $reset['email']);
                $delete->execute();

                header("Location: login.php?updated=true");
                exit;
            }
        }
    }
} else {
    $error = "🔒 Missing reset token.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .spinner-border-sm {
            display: none;
        }

        .btn-loading .spinner-border-sm {
            display: inline-block;
        }

        .btn-loading .btn-text {
            display: none;
        }

        .strength-meter {
            height: 5px;
            background-color: #e0e0e0;
            margin-top: 4px;
        }

        .strength-meter div {
            height: 100%;
            transition: width 0.3s;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow rounded-4">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Reset Your Password</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php elseif ($reset): ?>
                            <form method="POST" onsubmit="return validateForm(this)">
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control" required minlength="6" placeholder="Enter new password" oninput="checkStrength(this.value)">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', 'eye1')">
                                            <i id="eye1" class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="strength-meter mt-1">
                                        <div id="strength-bar" class="bg-danger" style="width: 0%;"></div>
                                    </div>
                                    <small id="strength-text" class="text-muted"></small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6" placeholder="Confirm password">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password', 'eye2')">
                                            <i id="eye2" class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="g-recaptcha" data-sitekey="<?= SITE_KEY ?>"></div>
                                </div>


                                <button type="submit" id="submitBtn" class="btn btn-success w-100">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    <span class="btn-text">Update Password</span>
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="mt-3 text-center small">
                            <a href="login.php">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(id, eyeId) {
            const input = document.getElementById(id);
            const eyeIcon = document.getElementById(eyeId);
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            eyeIcon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        function checkStrength(password) {
            const bar = document.getElementById('strength-bar');
            const text = document.getElementById('strength-text');
            let strength = 0;

            if (password.length >= 6) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[\W]/.test(password)) strength++;

            let width = ['0%', '25%', '50%', '75%', '100%'][strength];
            let color = ['#dc3545', '#fd7e14', '#ffc107', '#0d6efd', '#198754'][strength];
            let label = ['Too weak', 'Weak', 'Moderate', 'Strong', 'Very Strong'][strength];

            bar.style.width = width;
            bar.style.backgroundColor = color;
            text.innerText = label;
        }

        function validateForm(form) {
            const pass = form.password.value;
            const confirm = form.confirm_password.value;
            if (pass !== confirm) {
                alert('Passwords do not match!');
                return false;
            }
            document.getElementById('submitBtn').classList.add('btn-loading');
            return true;
        }
    </script>
</body>

</html>