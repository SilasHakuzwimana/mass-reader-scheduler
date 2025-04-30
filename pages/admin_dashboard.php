<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

// Access control: Ensure the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>

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