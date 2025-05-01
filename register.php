<?php
require_once '../templates/header.php';
session_start();

// Set the default timezone to Africa/Kigali
date_default_timezone_set('Africa/Kigali');

require_once 'includes/db.php';
require_once 'includes/config.php';
include 'templates/header.php';

$name = $email = ""; // For retaining values
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_plain = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Server-side validation
    if (empty($name) || empty($email) || empty($password_plain) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password_plain !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password_plain) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (empty($recaptcha_response)) {
        $error = "Please complete the reCAPTCHA.";
    } else {
        // Verify reCAPTCHA with Google
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . SECRET_KEY . "&response=" . $recaptcha_response);
        $captcha_success = json_decode($verify);

        if (!$captcha_success->success) {
            $error = "reCAPTCHA verification failed. Please try again.";
        } else {
            // Sanitize and validate the email
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                // Hash password using BCRYPT
                $password = password_hash($password_plain, PASSWORD_BCRYPT);

                // Check if email already exists
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $existing = $stmt->get_result();

                if ($existing->num_rows > 0) {
                    $error = "Email already exists.";
                } else {
                    // Default user role
                    $role = 'reader';
                    $current_time = date('Y-m-d H:i:s');

                    // Insert new user with the current time for created_at
                    $stmt = $conn->prepare("INSERT INTO users (names, email, password, role, created_at) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $name, $email, $password, $role, $current_time);
                    if ($stmt->execute()) {
                        // Set session variables after successful registration
                        $_SESSION['user_id'] = $stmt->insert_id;
                        $_SESSION['user_name'] = $name;
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = $role;

                        // Regenerate session ID to prevent session fixation
                        session_regenerate_id(true);

                        // Redirect to the reader dashboard
                        header("Location: pages/reader_dashboard");
                        exit;
                    } else {
                        $error = "Error registering user. Please try again later.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | St. Basile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/register.css">
    <style>
        .spinner-border-sm {
            width: 1.5rem;
            height: 1.5rem;
            border-width: 0.2em;
        }
        .input-group-text:hover {
            cursor: pointer;
        }
    </style>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow rounded-4">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Create an Account</h3>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" novalidate id="registerForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user" style="color: #74C0FC;"></i></span>
                                <input type="text" name="name" id="name" class="form-control" required
                                       value="<?= htmlspecialchars($name) ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope" style="color: #74C0FC;"></i></span>
                                <input type="email" name="email" id="email" class="form-control" required
                                       value="<?= htmlspecialchars($email) ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock" style="color: #74C0FC;"></i></span>
                                <input type="password" name="password" id="password" class="form-control" required minlength="6">
                                <span class="input-group-text" id="togglePassword"><i class="fa-regular fa-eye-slash"></i></span>
                            </div>
                            <div class="progress mt-2">
                                <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <small id="strengthMessage" class="form-text text-muted"></small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock" style="color: #74C0FC;"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                                <span class="input-group-text" id="toggleConfirmPassword"><i class="fa-regular fa-eye-slash"></i></span>
                            </div>
                            <small id="matchMessage" class="form-text text-danger"></small>
                        </div>

                        <div class="mb-3 text-center">
                            <div class="g-recaptcha" data-sitekey="<?= SITE_KEY ?>"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="registerButton">
                            <i class="fa-solid fa-user-plus"></i> Register
                            <div id="spinner" class="spinner-border text-light d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </form>

                    <div class="mt-3 text-center small">
                        Already have an account? <a href="/login">Login</a>
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

<script>
document.getElementById('registerForm').addEventListener('submit', function() {
    document.getElementById('registerButton').setAttribute('disabled', true);
    document.getElementById('spinner').classList.remove('d-none');
});

document.getElementById('password').addEventListener('input', function () {
    const password = this.value;
    const strengthMessage = document.getElementById('strengthMessage');
    const strengthBar = document.getElementById('strengthBar');
    let strength = 0;

    if (password.length >= 6) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    const messages = [
        "Too weak 😟",
        "Weak 😕",
        "Moderate 🙂",
        "Strong 💪",
        "Very strong 🔐"
    ];

    const widths = ["0%", "20%", "40%", "70%", "100%"];
    const colors = ["bg-danger", "bg-warning", "bg-info", "bg-primary", "bg-success"];

    strengthMessage.textContent = messages[Math.min(strength, messages.length - 1)];
    strengthBar.style.width = widths[strength];
    strengthBar.className = `progress-bar ${colors[strength]}`;
});

document.getElementById('confirm_password').addEventListener('input', function () {
    const matchMessage = document.getElementById('matchMessage');
    if (this.value !== document.getElementById('password').value) {
        matchMessage.textContent = "Passwords do not match.";
    } else {
        matchMessage.textContent = "";
    }
});

// Toggle visibility
function toggleVisibility(inputId, toggleId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(toggleId).querySelector('i');

    toggle.addEventListener('click', function () {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        toggle.classList.toggle('fa-eye');
        toggle.classList.toggle('fa-eye-slash');
    });
}

toggleVisibility('password', 'togglePassword');
toggleVisibility('confirm_password', 'toggleConfirmPassword');
</script>

</body>
</html>

<?php include 'templates/footer.php'; ?>
