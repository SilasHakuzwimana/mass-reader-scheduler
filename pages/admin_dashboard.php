<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

// Access control: Ensure the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch unread messages count
$unread_messages_stmt = $conn->prepare("SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND status = 'unread'");
$unread_messages_stmt->bind_param("i", $_SESSION['user_id']);
$unread_messages_stmt->execute();
$unread_messages_result = $unread_messages_stmt->get_result();
$unread_messages = $unread_messages_result->fetch_assoc();
?>

<?php include '../templates/user_info_card.php'; ?>

<div class="container mt-5">
    <h1 class="mb-4">Admin Dashboard</h1>
    <div class="row">
        <!-- User Management Section -->
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header">User Management</div>
                <div class="card-body">
                    <a href="manage_users.php" class="btn btn-primary manage-btn" onclick="showLoader(event, this)">
                        <i class="fa fa-users"></i> Manage Users
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Reports Section -->
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header">Reports</div>
                <div class="card-body">
                    <a href="reports.php" class="btn btn-primary manage-btn" onclick="showLoader(event, this)">
                        <i class="fa fa-chart-line"></i> View Reports
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- System Analytics Section -->
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header">System Analytics</div>
                <div class="card-body">
                    <a href="analytics.php" class="btn btn-primary manage-btn" onclick="showLoader(event, this)">
                        <i class="fa-solid fa-chart-pie"></i> System Analytics
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </a>
                </div>
            </div>
        </div>
        <!-- Messages Cards -->

        <!-- Send Message Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header text-center fw-bold">
                    <i class="bi bi-envelope-fill me-2"></i> Send Message
                </div>
                <div class="card-body text-center">
                    <h4 class="card-title">Send a Message</h4>
                    <p class="card-text">Communicate with coordinators, readers, or admins.</p>
                    <a href="send_message.php" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Compose Message</a>
                </div>
            </div>
        </div>

        <!-- View Messages Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header text-center fw-bold">
                    <i class="bi bi-envelope me-2"></i> Messages
                </div>
                <div class="card-body text-center">
                    <h4 class="card-title"><?= $unread_messages['unread_count'] ?> New Message<?= ($unread_messages['unread_count'] > 1 ? 's' : '') ?></h4>
                    <p class="card-text"><?= ($unread_messages['unread_count'] > 0) ? 'You have unread messages from your coordinator.' : 'No new messages.' ?></p>
                    <a href="view_messages.php" class="btn btn-primary"><i class="fa-solid fa-glasses"></i> View Messages</a>
                </div>
            </div>
        </div>

        <!-- System Configuration Section -->
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header">System Settings</div>
                <div class="card-body">
                    <a href="settings.php" class="btn btn-primary manage-btn" onclick="showLoader(event, this)">
                        <i class="fa fa-cogs"></i> System Settings
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>

<script>
    // Function to show the loader spinner when the link is clicked
    function showLoader(event, element) {
        // Prevent default action of link
        event.preventDefault();

        // Show spinner and disable the button
        var spinner = element.querySelector('.spinner-border');
        spinner.classList.remove('d-none');

        // Disable the button to prevent multiple clicks
        element.classList.add('disabled');

        // Wait for 1 second (simulating the loading process) and then redirect
        setTimeout(function() {
            window.location.href = element.href;
        }, 1000); // Adjust the delay as needed
    }
</script>

<?php include '../templates/footer.php'; ?>