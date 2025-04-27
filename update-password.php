<?php
require_once 'includes/db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        die("Invalid token.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Update user password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$newPassword, $reset['email']]);

        // Delete the token
        $conn->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$reset['email']]);

        header("Location: login.php?updated=true");
        exit;
    }
} else {
    die("Token missing.");
}
?>

<!-- Reset Form -->
<form method="POST">
    <h2>Set New Password</h2>
    <input type="password" name="password" required placeholder="New password" class="form-control">
    <button type="submit" class="btn btn-success mt-2">Update Password</button>
</form>
