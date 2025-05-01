<?php
session_start();
require_once '../includes/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo 'error';
    exit;
}

$message_id = $_POST['message_id'];

// Sanitize the input
$message_id = mysqli_real_escape_string($conn, $message_id);

// Update the message status to 'read'
$query = "UPDATE messages SET status = 'read' WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $message_id);
$stmt->execute();

// Check if the update was successful
if ($stmt->affected_rows > 0) {
    echo 'success';
} else {
    echo 'error';
}
?>
