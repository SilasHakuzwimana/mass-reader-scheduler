<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

// Auth check
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $system_name = trim($_POST['system_name']);
    $coordinator_email = trim($_POST['coordinator_email']);
    $timezone = trim($_POST['timezone']);

    // Update settings
    $stmt = $conn->prepare("UPDATE settings SET system_name = ?, coordinator_email = ?, timezone = ? WHERE id = 1");
    $stmt->bind_param("sss", $system_name, $coordinator_email, $timezone);
    $stmt->execute();

    // Log the action
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $email = $_SESSION['email'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $action = "Updated system settings (System Name: $system_name, Coordinator Email: $coordinator_email, Timezone: $timezone)";
    
    // Insert log record
    $log_stmt = $conn->prepare("INSERT INTO logs (user_id, names, email, user_name, action, ip_address, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $log_stmt->bind_param("isssss", $user_id, $user_name, $email, $user_name, $action, $ip_address);
    $log_stmt->execute();

    $message = "Settings updated successfully.";
}

// Load current settings
$settings = $conn->query("SELECT * FROM settings WHERE id = 1")->fetch_assoc();
?>

<div class="container mt-5">
    <h2>System Settings</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm rounded-4">
        <div class="mb-3">
            <label for="system_name" class="form-label">System Name</label>
            <input type="text" name="system_name" class="form-control" value="<?= htmlspecialchars($settings['system_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="coordinator_email" class="form-label">Coordinator Email</label>
            <input type="email" name="coordinator_email" class="form-control" value="<?= htmlspecialchars($settings['coordinator_email']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="timezone" class="form-label">Timezone</label>
            <input type="text" name="timezone" class="form-control" value="<?= htmlspecialchars($settings['timezone']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Update Settings</button>
    </form>
</div>

<?php include '../templates/footer.php'; ?>
