<?php 
session_start();
require_once '../includes/db.php';
date_default_timezone_set('Africa/Kigali');

if ($_SESSION['role'] !== 'coordinator') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = intval($_POST['event_id']);
    $reader_id = intval($_POST['reader_id']);
    $role = htmlspecialchars($_POST['role']);

    // Check if the reader is already assigned to the event with a role
    $stmt_check = $conn->prepare("SELECT id FROM assignments WHERE event_id = ? AND reader_id = ?");
    $stmt_check->bind_param("ii", $event_id, $reader_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // If a record exists, the reader is already assigned to the event
        header('Location: assign.php?error=reader_assigned');
        exit;
    }

    $current_time = date('Y-m-d H:i:s');
    
    // Prepare SQL statement to insert the assignment into the database
    $stmt = $conn->prepare("INSERT INTO assignments (event_id, reader_id, role, created_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $event_id, $reader_id, $role, $current_time);

    if ($stmt->execute()) {
        // Redirect with success message
        header('Location: assign.php?success=1');
    } else {
        // Redirect with error message
        header('Location: assign.php?error=1');
    }
    exit;
}
