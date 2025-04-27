<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

// Only coordinators allowed
if ($_SESSION['role'] !== 'coordinator') {
    header('Location: ../login.php');
    exit;
}

date_default_timezone_set('Africa/Kigali');

$success = $error = "";

// Fetch event data for editing
if (isset($_GET['id'])) {
    $event_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
} else {
    header('Location: manage_events.php');
    exit;
}

// Handle update request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];

    // Validation
    if (empty($title) || empty($event_date) || empty($event_time)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("UPDATE events SET title = ?, event_date = ?, event_time = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $event_date, $event_time, $event_id);

        if ($stmt->execute()) {
            $success = "Event updated successfully.";
            header('Location:manage_events.php');
        } else {
            $error = "Failed to update the event.";
        }
    }
}
?>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Edit Event</h1>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="manage_events.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Manage Events
        </a>
    </div>

    <!-- Edit Event Form -->
    <form method="POST" class="bg-light p-4 rounded shadow-sm">
        <div class="mb-3">
            <label for="title" class="form-label">Event Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="event_date" class="form-label">Event Date</label>
            <input type="date" id="event_date" name="event_date" class="form-control" value="<?= htmlspecialchars($event['event_date']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="event_time" class="form-label">Event Time</label>
            <input type="time" id="event_time" name="event_time" class="form-control" value="<?= htmlspecialchars($event['event_time']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2">Update Event</button>
    </form>
</div>

<!-- Toast Notifications -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <!-- Success Toast -->
    <?php if (!empty($success)): ?>
        <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($success) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Error Toast -->
    <?php if (!empty($error)): ?>
        <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($error) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?>

<!-- Bootstrap Toast JS and initialization -->
<script>
    // Show the success or error toast if there's any message
    document.addEventListener("DOMContentLoaded", function() {
        const successToast = document.querySelector('.toast.bg-success');
        const errorToast = document.querySelector('.toast.bg-danger');

        if (successToast) {
            const toast = new bootstrap.Toast(successToast);
            toast.show();
        }

        if (errorToast) {
            const toast = new bootstrap.Toast(errorToast);
            toast.show();
        }
    });
</script>

<style>
    .container {
        max-width: 600px;
    }

    h1 {
        font-size: 2rem;
        font-weight: 600;
    }

    .form-label {
        font-weight: 500;
    }

    .btn-primary {
        font-size: 1.1rem;
        font-weight: 500;
    }

    .form-control {
        border-radius: 10px;
    }

    .btn-secondary {
        font-size: 1rem;
    }

    .alert {
        font-size: 1rem;
        font-weight: 400;
    }

    .shadow-sm {
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }

    .bg-light {
        background-color: #f9f9f9;
    }

    .toast-container {
        z-index: 1050; /* To ensure it's visible above other content */
    }

    .toast-body {
        font-size: 1rem;
        font-weight: 400;
    }
</style>
