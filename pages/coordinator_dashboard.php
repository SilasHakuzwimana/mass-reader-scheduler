<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

if ($_SESSION['role'] !== 'coordinator') {
    header('Location: ../login.php');
    exit;
}

// Fetch the logged-in user's name
$stmt = $conn->prepare("SELECT names FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch the total number of readers
$total_readers_stmt = $conn->query("SELECT COUNT(*) as total_readers FROM users WHERE role = 'reader'");
$total_readers = $total_readers_stmt->fetch_assoc();


// Fetch unread messages count
$unread_messages_stmt = $conn->prepare("SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND status = 'unread'");
$unread_messages_stmt->bind_param("i", $_SESSION['user_id']);
$unread_messages_stmt->execute();
$unread_messages_result = $unread_messages_stmt->get_result();
$unread_messages = $unread_messages_result->fetch_assoc();

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
    <title>Coordinator Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Toast Styles -->
    <link rel="stylesheet" href="../assets/css/coordinatortoaststyles.css">

    <style>
        /* Spinner hidden by default */
        .spinner-border {
            display: none;
        }
    </style>
</head>

<body>
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

    <div class="container mt-5">
        <h1 class="mb-4">Coordinator Dashboard</h1>

        <!-- Total Readers Section -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header text-center fw-bold">Total Readers</div>
                    <div class="card-body text-center">
                        <h3 class="card-title"><?= $total_readers['total_readers'] ?></h3>
                        <p class="card-text">Number of readers currently registered.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Mass Assignment Section -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header text-center fw-bold">Assign Readings</div>
                    <div class="card-body text-center">
                        <button onclick="redirectWithSpinner('assign.php', this)" class="btn btn-primary">
                            <i class="fa-solid fa-book-open me-2"></i>Assign Readings
                            <span class="spinner-border spinner-border-sm ms-2"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Availability Section -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header text-center fw-bold">Reader Availability</div>
                    <div class="card-body text-center">
                        <button onclick="redirectWithSpinner('readers_availability.php', this)" class="btn btn-primary">
                            <i class="fa-solid fa-user-clock me-2"></i>Manage Availability
                            <span class="spinner-border spinner-border-sm ms-2"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Manage Events Section -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow rounded-4 border-0">
                    <div class="card-header text-center fw-bold">Manage Events</div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <button onclick="redirectWithSpinner('manage_events.php', this)" class="btn btn-primary">
                            <i class="fa-solid fa-clipboard-list me-2"></i> Manage Events
                            <span class="spinner-border spinner-border-sm ms-2"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header text-center fw-bold">Event Calendar</div>
                    <div class="card-body text-center">
                        <button onclick="redirectWithSpinner('calendar.php', this)" class="btn btn-primary">
                            <i class="fa-solid fa-calendar-days me-2"></i>View Calendar
                            <span class="spinner-border spinner-border-sm ms-2"></span>
                        </button>
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

            <!-- Print Assignment Sheet Section -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header text-center fw-bold">Print Assignment Sheet</div>
                    <div class="card-body text-center">
                        <button onclick="redirectWithSpinner('print_assignment.php', this)" class="btn btn-primary">
                            <i class="fa-solid fa-print me-2"></i>Print Assignments
                            <span class="spinner-border spinner-border-sm ms-2"></span>
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
        function redirectWithSpinner(url, button) {
            const spinner = button.querySelector('.spinner-border');
            spinner.style.display = 'inline-block'; // Show spinner

            button.disabled = true; // Optional: disable button after click

            setTimeout(() => {
                window.location.href = url;
            }, 1000); // Small delay to show spinner
        }
    </script>
</body>

</html>