<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
include '../templates/header.php';

// Fetch upcoming lectures
$stmt = $conn->query('SELECT * FROM lectures ORDER BY date ASC LIMIT 5');
$lectures = $stmt->fetchAll();
?>

<h2>Dashboard</h2>
<table class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Reader</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lectures as $lecture): ?>
        <tr>
            <td><?= htmlspecialchars($lecture['date']) ?></td>
            <td><?= htmlspecialchars($lecture['time']) ?></td>
            <td><?= htmlspecialchars($lecture['reader_name']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../templates/footer.php'; ?>
