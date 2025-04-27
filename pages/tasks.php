<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

// Check if the user is a reader
if ($_SESSION['role'] !== 'reader') {
    header('Location: ../login.php');
    exit;
}

// Query to fetch assignments along with mass details
$stmt = $conn->prepare("
    SELECT a.id, a.role, 
           e.event_date, e.title AS event_name, e.event_time 
    FROM assignments a
    JOIN events e ON a.event_id = e.id
    WHERE a.reader_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$tasks_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upcoming Tasks | Reader Dashboard</title>

    <!-- Include the custom toast styles -->
    <link rel="stylesheet" href="../assets/css/toaststyles.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4">Upcoming Tasks</h1>

    <!-- Back Button -->
        <a href="reader_dashboard.php" class="btn btn-secondary">

            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard

        </a>

    <?php if ($tasks_result->num_rows > 0): ?>
        <div class="list-group">
            <?php while ($task = $tasks_result->fetch_assoc()): ?>
                <div class="list-group-item">
                    <h5><?= htmlspecialchars($task['event_name']) ?> (<?= htmlspecialchars(date('F j, Y', strtotime($task['event_date']))) ?>)</h5>
                    <p><strong>Role:</strong> <?= htmlspecialchars($task['role']) ?></p>
                    <p><strong>Mass Time:</strong> <?= htmlspecialchars($task['event_time']) ?></p>
                    <p><strong>Due:</strong> <?= date('F j, Y', strtotime($task['event_date'])) ?></p>
                    <!-- View Task Button (Triggers Modal) -->
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#taskModal" onclick="loadTaskDetails(<?= $task['id'] ?>)">View Task</button>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">You have no upcoming tasks.</div>
    <?php endif; ?>
</div>

<!-- Toast Notifications -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="taskToast" class="toast toast-info" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Task Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Task successfully loaded!
        </div>
    </div>
</div>

<!-- Modal for Task Details -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 id="modalEventName"></h5>
                <p><strong>Role:</strong> <span id="modalRole"></span></p>
                <p><strong>Mass Date:</strong> <span id="modalEventDate"></span></p>
                <p><strong>Mass Time:</strong> <span id="modalEventTime"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Line -->
<div class="loading-line" id="loadingLine">
    <div class="progress-bar" id="progressBar"></div>
</div>

<?php include '../templates/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Automatically show toast after loading
    var toast = new bootstrap.Toast(document.getElementById('taskToast'));
    toast.show();

    // Simulate loading animation when task data is being fetched
    window.onload = function() {
        var loadingLine = document.getElementById("loadingLine");
        loadingLine.style.display = "block";
        setTimeout(function() {
            loadingLine.style.display = "none";
        }, 5000); // Hide loading line after 5 seconds
    };

    // Function to load task details into the modal
    function loadTaskDetails(taskId) {
        // Send an AJAX request to fetch the task details based on the taskId
        fetch('get_task_details.php?id=' + taskId)
            .then(response => response.json())
            .then(data => {
                // Populate the modal with the task details
                document.getElementById('modalEventName').textContent = data.event_name;
                document.getElementById('modalRole').textContent = data.role;
                document.getElementById('modalEventDate').textContent = new Date(data.event_date).toLocaleDateString();
                document.getElementById('modalEventTime').textContent = data.event_time;
            })
            .catch(error => console.error('Error loading task details:', error));
    }
</script>
</body>
</html>
