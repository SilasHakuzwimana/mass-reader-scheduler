<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

// Protect: Only coordinators allowed
if ($_SESSION['role'] !== 'coordinator') {
    header('Location: ../login.php');
    exit;
}

date_default_timezone_set('Africa/Kigali');

$error = "";
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);

    if (empty($title) || empty($event_date) || empty($event_time)) {
        $error = "All fields are required.";
    } else {
        $current_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO events (title, event_date, event_time, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $event_date, $event_time, $current_time);

        if ($stmt->execute()) {
            $success = "Event created successfully!";
        } else {
            $error = "Error creating event. Please try again.";
        }
    }
}
?>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Create New Event</h1>

    <!-- Toast notifications for success and error messages -->
    <div id="successToast" class="toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($success) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>

    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0 position-fixed top-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($error) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>

    <!-- Show the Toast notifications if there are any -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (!empty($success)): ?>
                var successToast = new bootstrap.Toast(document.getElementById('successToast'));
                successToast.show();
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                var errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
                errorToast.show();
            <?php endif; ?>
        });
    </script>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <a href="manage_events.php" class="btn btn-secondary mb-3">
                <i class="fa-solid fa-arrow-left"></i> Back to Events
            </a>

            <form method="POST" novalidate>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Event Details</h5>

                        <!-- Event Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" name="title" id="title" class="form-control" required placeholder="e.g., Sunday 7:00 AM Mass" aria-describedby="eventTitleHelp">
                            <small id="eventTitleHelp" class="form-text text-muted">Give a clear title for the event.</small>
                        </div>

                        <!-- Event Date -->
                        <div class="mb-3">
                            <label for="event_date" class="form-label">Event Date</label>
                            <input type="date" name="event_date" id="event_date" class="form-control" required aria-describedby="eventDateHelp">
                            <small id="eventDateHelp" class="form-text text-muted">Pick a date for the event.</small>
                        </div>

                        <!-- Event Time -->
                        <div class="mb-3">
                            <label for="event_time" class="form-label">Event Time</label>
                            <input type="time" name="event_time" id="event_time" class="form-control" required aria-describedby="eventTimeHelp">
                            <small id="eventTimeHelp" class="form-text text-muted">Pick a time for the event.</small>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-calendar-plus"></i> Create Event
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
