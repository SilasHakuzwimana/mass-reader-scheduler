<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

if ($_SESSION['role'] !== 'coordinator') {
    header('Location: ../login.php');
    exit;
}

// Fetch all readers
$readers = $conn->query("SELECT id, names FROM users WHERE role = 'reader'");

// Fetch upcoming events (Masses)
$events = $conn->query("SELECT id, title, event_date FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");

$success = isset($_GET['success']) ? 'Reader assigned successfully!' : '';
$error_code = isset($_GET['error']) ? $_GET['error'] : '';

// Define error messages
$error_messages = [
    'reader_assigned' => 'This reader is already assigned to the selected mass.',
    'failed' => 'Failed to assign reader. Please try again.',
];

$error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : $error_messages['failed'];
?>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Assign Readers to Mass</h1>

    <!-- Success Toast -->
    <?php if ($success): ?>
        <div id="successToast" class="toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"><?= htmlspecialchars($success) ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Error Toast -->
    <?php if ($error_message): ?>
        <div id="errorToast" class="toast align-items-center text-white bg-danger border-0 position-fixed top-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"><?= htmlspecialchars($error_message) ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if ($success): ?>
                var successToast = new bootstrap.Toast(document.getElementById('successToast'));
                successToast.show();
            <?php endif; ?>

            <?php if ($error_message): ?>
                var errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
                errorToast.show();

                // Hide the error toast after 3 seconds (3000 milliseconds)
                setTimeout(function () {
                    var toastElement = document.getElementById('errorToast');
                    if (toastElement) {
                        var toastInstance = bootstrap.Toast.getInstance(toastElement);
                        toastInstance.hide();
                    }
                }, 3000); // 3 seconds
            <?php endif; ?>
        });
    </script>

    <a href="coordinator_dashboard.php" class="btn btn-secondary mb-4">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <!-- Form to assign reader -->
    <form action="save_assignment.php" method="POST">
        <div class="card">
            <div class="card-header">Assign Reader</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="event_id" class="form-label">Select Mass/Event</label>
                    <select name="event_id" id="event_id" class="form-select" required>
                        <?php while ($event = $events->fetch_assoc()): ?>
                            <option value="<?= $event['id'] ?>">
                                <?= htmlspecialchars($event['title']) ?> (<?= date('M d, Y', strtotime($event['event_date'])) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Select Role</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="Kubwiriza">Kubwiriza (Leader)</option>
                        <option value="Isomo rya 1">Isomo rya 1 (First Reading)</option>
                        <option value="Isomo rya 2">Isomo rya 2 (Second Reading)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="reader_id" class="form-label">Select Reader</label>
                    <select name="reader_id" id="reader_id" class="form-select" required>
                        <?php while ($reader = $readers->fetch_assoc()): ?>
                            <option value="<?= $reader['id'] ?>"><?= htmlspecialchars($reader['names']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-check"></i> Assign Reader
                </button>
            </div>
        </div>
    </form>
</div>

<?php include '../templates/footer.php'; ?>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
