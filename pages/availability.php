<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
include '../templates/header.php';

// TODO: Show reader availability form or status
?>
<h2>Set Your Availability</h2>
<form method="post" action="">
    <div class="mb-3">
        <label for="available_date" class="form-label">Available Date</label>
        <input type="date" class="form-control" id="available_date" name="available_date">
    </div>
    <button type="submit" class="btn btn-success">Submit Availability</button>
</form>
<?php include '../templates/footer.php'; ?>
