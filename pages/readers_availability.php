<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

if ($_SESSION['role'] !== 'coordinator') {
    header('Location: ../login.php');
    exit;
}

// Fetch all readers and their availability
$availability = $conn->query("
    SELECT u.names, a.available_date, a.notes
    FROM availability a
    JOIN users u ON u.id = a.user_id
    ORDER BY a.available_date ASC
");
?>

<div class="container mt-5">
    <h1 class="mb-4">Readers Availability</h1>
    <br />
        <a href="coordinator_dashboard.php" class="btn btn-secondary">

            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard

        </a>
    <br />
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Reader Name</th>
                <th>Available Date</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $availability->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['names']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['available_date'])) ?></td>
                    <td><?= htmlspecialchars($row['notes']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../templates/footer.php'; ?>
