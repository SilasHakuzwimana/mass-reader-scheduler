<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

if ($_SESSION['role'] !== 'reader') {
    header('Location: ../login.php');
    exit;
}

// Fetch the logged-in user's name
$stmt = $conn->prepare("SELECT names FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Greeting based on time
$hour = date("H");
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour >= 12 && $hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reader Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Toast Styles -->
    <link rel="stylesheet" href="../assets/css/readertoaststyles.css">

    <style>
        /* Spinner hidden by default */
        .spinner-border {
            display: none;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Reader Dashboard</h1>

    <!-- Greeting Toast -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="greetingToast" class="toast show custom-toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto"><?= htmlspecialchars($greeting) ?>, <?= htmlspecialchars($user['names']) ?>!</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                We are happy to have you back! Enjoy your day.
            </div>
        </div>
    </div>

    <?php include '../templates/user_info_card.php'; ?>

    <div class="row">
        <!-- Upcoming Tasks Section -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header"><i class="fas fa-tasks me-2"></i>Upcoming Tasks</div>
                <div class="card-body text-center">
                    <button onclick="redirectWithSpinner('tasks.php')" class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i>View Tasks
                        <div class="spinner-border spinner-border-sm ms-2" role="status"></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Availability Section -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header"><i class="fas fa-calendar-check me-2"></i>Submit Availability</div>
                <div class="card-body text-center">
                    <button onclick="redirectWithSpinner('availability.php')" class="btn btn-success">
                        <i class="fas fa-paper-plane me-2"></i>Submit Availability
                        <div class="spinner-border spinner-border-sm ms-2" role="status"></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Profile Management Section -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header"><i class="fas fa-user-cog me-2"></i>Profile Management</div>
                <div class="card-body text-center">
                    <button onclick="redirectWithSpinner('profile.php')" class="btn btn-warning">
                        <i class="fas fa-user-edit me-2"></i>Update Profile
                        <div class="spinner-border spinner-border-sm ms-2" role="status"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    // Show greeting toast for 3 seconds
    var greetingToast = new bootstrap.Toast(document.getElementById('greetingToast'));
    greetingToast.show();
    setTimeout(() => greetingToast.hide(), 3000);

    // Redirect with loading spinner
    function redirectWithSpinner(url) {
        const button = event.currentTarget;
        const spinner = button.querySelector('.spinner-border');
        spinner.style.display = 'inline-block'; // Show spinner

        setTimeout(() => {
            window.location.href = url;
        }, 1000); // Wait 1 second to show spinner before redirect
    }
</script>
</body>
</html>
