<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

// Auth: Only admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Report generation logic (optional filters)
$reports = [];
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

$stmt = $conn->prepare("
    SELECT a.id, e.event_date, u.names AS reader, a.role, e.title, e.event_time
    FROM assignments a
    JOIN events e ON a.event_id = e.id
    JOIN users u ON a.reader_id = u.id
    WHERE e.event_date BETWEEN ? AND ?
    ORDER BY e.event_date ASC
");

$stmt->bind_param("ss", $start, $end);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}
?>

<div class="container mt-5">
    <h2>Assignment Reports</h2>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="start">From:</label>
            <input type="date" name="start" class="form-control" value="<?= htmlspecialchars($start) ?>" required>
        </div>
        <div class="col-md-4">
            <label for="end">To:</label>
            <input type="date" name="end" class="form-control" value="<?= htmlspecialchars($end) ?>" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Event</th>
                    <th>Reader</th>
                    <th>Role</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($reports) > 0): ?>
                    <?php foreach ($reports as $i => $r): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($r['event_date']) ?></td>
                            <td><?= htmlspecialchars($r['title']) ?></td>
                            <td><?= htmlspecialchars($r['reader']) ?></td>
                            <td><?= htmlspecialchars($r['role']) ?></td>
                            <td><?= htmlspecialchars($r['event_time']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
