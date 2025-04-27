<?php
require_once '../includes/db.php';

if (isset($_GET['id'])) {
    $taskId = $_GET['id'];

    // Query to get task details
    $stmt = $conn->prepare("
        SELECT a.role, e.event_date, e.title AS event_name, e.event_time
        FROM assignments a
        JOIN events e ON a.event_id = e.id
        WHERE a.id = ?
    ");
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();
        echo json_encode($task);
    } else {
        echo json_encode(['error' => 'Task not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid task ID']);
}
