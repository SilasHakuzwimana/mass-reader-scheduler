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

// Handle delete request
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);

    if ($stmt->execute()) {
        $success = "Event deleted successfully.";
    } else {
        $error = "Failed to delete the event.";
    }
}

// Fetch all events
$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC, event_time ASC");
?>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- FontAwesome Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<!-- Custom Css-->
<style>
/* Spinner hidden by default */
.spinner-border {
    display: none;
}
</style>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Manage Events</h1>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="coordinator_dashboard.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <button onclick="redirectWithSpinner('create_event.php', this)" class="btn btn-success">
            <i class="fa-solid fa-plus me-2"></i> Create New Event
            <span class="spinner-border spinner-border-sm ms-2"></span>
        </button>
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

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Event Date</th>
                    <th>Event Time</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($events->num_rows > 0): ?>
                    <?php $counter = 1; ?>
                    <?php while($event = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($event['event_date'])) ?></td>
                            <td><?= date('H:i', strtotime($event['event_time'])) ?></td>
                            <td><?= date('M d, Y H:i', strtotime($event['created_at'])) ?></td>
                            <td>
                                <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-warning">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <a href="?delete=<?= $event['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this event?');">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No events found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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

<style>
    .container {
        max-width: 900px;
    }

    h1 {
        font-size: 2rem;
        font-weight: 600;
    }

    .btn-primary {
        font-size: 1.1rem;
        font-weight: 500;
    }

    .btn-success {
        font-size: 1.1rem;
        font-weight: 500;
    }

    .btn-sm {
        font-size: 0.9rem;
    }

    .table-primary {
        background-color: #f0f8ff;
    }

    .toast-container {
        z-index: 1050; /* Ensure toasts are above all other content */
    }

    .toast-body {
        font-size: 1rem;
    }
</style>
