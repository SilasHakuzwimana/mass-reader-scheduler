<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

// Set the default timezone to Africa/Kigali
date_default_timezone_set('Africa/Kigali');

if ($_SESSION['role'] !== 'reader') {
    header('Location: ../login.php');
    exit;
}

// Handle form submission for availability
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $availability_data = [];

    foreach ($_POST['days'] as $day => $value) {
        if (!empty($_POST['times'][$day])) {
            $availability_data[] = [
                'day' => $day,
                'time' => trim($_POST['times'][$day])
            ];
        }
    }

    foreach ($availability_data as $entry) {
        $available_date = date('Y-m-d', strtotime('next ' . $entry['day']));
        $notes = $entry['time']; // Save time as notes
        $current_time = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO availability (user_id, email, available_date, notes, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $_SESSION['user_id'], $_SESSION['email'], $available_date, $notes, $current_time);
        $stmt->execute();
    }

    $success_message = "Availability successfully submitted!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Availability | Reader Dashboard</title>

    <!-- Include the custom toast styles -->
    <link rel="stylesheet" href="../assets/css/toaststyles.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4">Submit Availability</h1>

    <!-- Back Button -->
    <a href="reader_dashboard.php" class="btn btn-secondary mb-3"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

    <!-- Display success message in Toast -->
    <?php if (isset($success_message)): ?>
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Success</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Availability Form -->
    <form method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
            <label class="form-label">Select Your Availability</label>
            <div class="row">
                <?php
                // Days of the week
                $days_of_week = [
                    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
                ];
                foreach ($days_of_week as $day):
                ?>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="days[<?= $day ?>]" id="<?= $day ?>">
                        <label class="form-check-label" for="<?= $day ?>"><?= $day ?></label>
                        <input type="text" name="times[<?= $day ?>]" class="form-control mt-2" placeholder="Enter time (e.g. 9:00 AM - 5:00 PM)" disabled>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Submit Availability</button>
    </form>
</div>

<!-- Footer -->
<?php include '../templates/footer.php'; ?>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Enable Bootstrap 5 Form Validation
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
    })();

    // Enable time input only when the checkbox is selected
    document.querySelectorAll('.form-check-input').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            var timeInput = checkbox.closest('.form-check').querySelector('input[type="text"]');
            timeInput.disabled = !checkbox.checked;
        });
    });
</script>

</body>
</html>
