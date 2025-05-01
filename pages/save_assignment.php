<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/config.php'; // Contains MAILHOST, USERNAME, etc.

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Africa/Kigali');

if ($_SESSION['role'] !== 'coordinator') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = intval($_POST['event_id']);
    $reader_id = intval($_POST['reader_id']);
    $role = htmlspecialchars(trim($_POST['role']));

    // Check if already assigned
    $stmt_check = $conn->prepare("SELECT id FROM assignments WHERE event_id = ? AND reader_id = ?");
    $stmt_check->bind_param("ii", $event_id, $reader_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header('Location: assign.php?error=reader_assigned');
        exit;
    }

    // Save assignment
    $created_at = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO assignments (event_id, reader_id, role, created_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $event_id, $reader_id, $role, $created_at);

    if ($stmt->execute()) {
        // Fetch reader info
        $reader_stmt = $conn->prepare("SELECT names, email FROM users WHERE id = ?");
        $reader_stmt->bind_param("i", $reader_id);
        $reader_stmt->execute();
        $reader = $reader_stmt->get_result()->fetch_assoc();

        // Fetch event info
        $event_stmt = $conn->prepare("SELECT title, event_date, event_time FROM events WHERE id = ?");
        $event_stmt->bind_param("i", $event_id);
        $event_stmt->execute();
        $event = $event_stmt->get_result()->fetch_assoc();

        // Format data
        $reader_name  = $reader['names'];
        $reader_email = $reader['email'];
        $event_title  = $event['title'];
        $event_date   = date('l, F j, Y', strtotime($event['event_date']));
        $event_time   = date('g:i A', strtotime($event['event_time']));

        // Prepare email
        $subject = "📖 Your Mass Assignment on " . WEBSITE_NAME;
        $assignment_date = date('l, F j, Y', strtotime($created_at));
        $assignment_time = date('g:i A', strtotime($created_at));

        $body = '
        <div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
            <div style="max-width: 600px; margin: auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                <h2 style="color: #0d6efd;">Hello ' . htmlspecialchars($reader_name) . ',</h2>
                <p>You have been assigned the following role for the upcoming Mass:</p>
                <ul style="list-style: none; padding: 0;">
                <li><strong>📍 Mass/Event:</strong> ' . htmlspecialchars($event_title) . '</li>
                <li><strong>📅 Mass Date:</strong> ' . $event_date . '</li>
                <li><strong>⏰ Mass Time:</strong> ' . $event_time . '</li>
                <li><strong>🎙️ Assigned Role:</strong> ' . htmlspecialchars($role) . '</li>
                </ul>
                <hr style="margin: 20px 0;">
                <p style="font-size: 0.95em; color: #333;"><em>This assignment notification was sent on <strong>' . $assignment_date . '</strong> at <strong>' . $assignment_time . '</strong> (Africa/Kigali time).</em></p>
                <p>Please prepare accordingly. If you have any questions, feel free to contact your coordinator.</p>
                <hr style="margin: 20px 0;">
                <p style="font-size: 0.9em; color: #777;">Thank you for your ministry and dedication.<br>' . WEBSITE_NAME . ' Team</p>
            </div>
        </div>';

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = MAILHOST;
            $mail->SMTPAuth = true;
            $mail->Username = USERNAME;
            $mail->Password = PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = PORT;

            $mail->setFrom(SEND_FROM, WEBSITE_NAME . ' Notifications');
            $mail->addAddress($reader_email, $reader_name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            // Optional: notify coordinator of failed email
        }

        header('Location: assign.php?success=1');
    } else {
        header('Location: assign.php?error=failed');
    }

    exit;
}
