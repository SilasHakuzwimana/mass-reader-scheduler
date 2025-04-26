<?php
require_once 'includes/auth.php';
include 'templates/header.php';
?>

<h2>Welcome to the Mass Reader Scheduling App</h2>
<p>This application allows you to manage and assign readers for daily mass lectures.</p>

<div class="btn-group">
    <a href="pages/assign.php" class="btn btn-primary">Assign Reader</a>
    <a href="pages/calendar.php" class="btn btn-primary">View Calendar</a>
    <a href="pages/availability.php" class="btn btn-primary">Manage Availability</a>
</div>

<?php include 'templates/footer.php'; ?>
