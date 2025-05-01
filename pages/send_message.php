<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/mailer.php';
include '../templates/header.php';

// Fetch users and roles
$user_result = $conn->query("SELECT id, names FROM users");
$users = $user_result ? $user_result->fetch_all(MYSQLI_ASSOC) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id'];
    $message = trim($_POST['message']);
    $viewers = $_POST['viewers'] ?? '';
    $status_unread = 'unread';
    $visibility_public = "public";

    $toast_message = '';
    $toast_class = '';

    if ($message) {
        // Sending to selected users
        if ($viewers === 'selected_user' && !empty($_POST['selected_users'])) {
            $visibility = 'private';
            foreach ($_POST['selected_users'] as $receiver_id) {
                $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, visibility, viewers, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissss", $sender_id, $receiver_id, $message, $visibility, $viewers, $status_unread);
                $stmt->execute();
            }
        }
        // Sending to users with a specific role (admins, coordinators, etc.)
        elseif (in_array($viewers, ['admins', 'coordinators', 'readers', 'all'])) {
            $visibility = 'private';
            $role = $viewers === 'all' ? '%' : $viewers;
            $role_stmt = $conn->prepare("SELECT id FROM users WHERE role LIKE ?");
            $role_stmt->bind_param("s", $role);
            $role_stmt->execute();
            $result = $role_stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $receiver_id = $row['id'];
                $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, visibility, viewers, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissss", $sender_id, $receiver_id, $message, $visibility, $viewers, $status_unread);
                $stmt->execute();
            }
        }
        // Sending a public message
        else {
            $visibility = 'public';
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, visibility, viewers, status) VALUES (?, NULL, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $sender_id, $message, $visibility, $viewers, $status_unread);
            $stmt->execute();
        }

        // Notify sender of the message sent and remove the sender copy from the database
        $toast_message = "Message sent successfully!";
        $toast_class = "toast-success";
    } else {
        $toast_message = "Please enter a message.";
        $toast_class = "toast-error";
    }
}

$reply_to = $_GET['reply_to'] ?? null;

// If replying, fetch original sender to set as receiver
if ($reply_to && empty($_POST['selected_users'])) {
    $original_msg = $conn->prepare("SELECT sender_id FROM messages WHERE id = ?");
    $original_msg->bind_param("i", $reply_to);
    $original_msg->execute();
    $result = $original_msg->get_result();
    if ($result && $msg = $result->fetch_assoc()) {
        $_POST['selected_users'][] = $msg['sender_id'];
        $_POST['viewers'] = 'selected_user';

        // 🔔 Get email of original sender
        $original_sender_id = $msg['sender_id'];
        $email_stmt = $conn->prepare("SELECT email, names FROM users WHERE id = ?");
        $email_stmt->bind_param("i", $original_sender_id);
        $email_stmt->execute();
        $email_result = $email_stmt->get_result();
        $email_data = $email_result->fetch_assoc();
        $original_sender_email = $email_data['email'];
        $original_sender_name = $email_data['names'];
    }
}

// 🚀 Send email after message is submitted and available
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($original_sender_email)) {
    $message_content = nl2br(htmlspecialchars(trim($_POST['message'])));
    $sender_name = htmlspecialchars($_SESSION['user_name']);

    $subject = "📩 New Reply from $sender_name – Mass Reader App";

    $body = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
            }
            .email-wrapper {
                max-width: 600px;
                margin: auto;
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .email-header {
                font-size: 18px;
                margin-bottom: 20px;
            }
            .message-box {
                background: #f1f1f1;
                border-left: 5px solid #007bff;
                padding: 10px;
                margin: 20px 0;
                font-style: italic;
            }
            .btn {
                display: inline-block;
                padding: 10px 15px;
                background: #007bff;
                color: #fff;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 15px;
            }
            .footer {
                margin-top: 30px;
                font-size: 13px;
                color: #888;
            }
        </style>
    </head>
    <body>
        <div class='email-wrapper'>
            <div class='email-header'>📩 You’ve received a reply!</div>
            <p><strong>$sender_name</strong> replied to your message:</p>
            <div class='message-box'>$message_content</div>
            <p>Click the button below to view the conversation:</p>
            <a href='http://localhost/mass_reader_scheduler/login.php' class='btn'>View Message</a>
            <div class='footer'>
                Mass Reader App – Do not reply to this automated message.
            </div>
        </div>
    </body>
    </html>
    ";

    sendMail($original_sender_email, $subject, $body);
}

$prefill = '';
if ($reply_to) {
    $quoted_stmt = $conn->prepare("
        SELECT m.message, m.created_at, u.names 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.id = ?
    ");
    $quoted_stmt->bind_param("i", $reply_to);
    $quoted_stmt->execute();
    $quoted_result = $quoted_stmt->get_result();
    if ($quoted_result && $row = $quoted_result->fetch_assoc()) {
        $original = trim($row['message']);
        $sender_name = $row['names'];
        $sent_time = date("M d, Y h:i A", strtotime($row['created_at']));
        $quoted = preg_replace('/^/m', '> ', wordwrap($original, 70));
        $prefill = "On $sent_time, $sender_name wrote:\n$quoted\n\nYour reply here:\n";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Send Message</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
            margin-top: 50px;
        }

        .toast {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1055;
        }

        .toast-success {
            background-color: #28a745;
            color: white;
        }

        .toast-error {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Send a Message</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="4" required><?= htmlspecialchars($prefill) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Viewers</label>
                <select class="form-select" name="viewers" id="viewers" onchange="toggleUserList()" required>
                    <option value="">-- Select --</option>
                    <option value="selected_user">Selected Users</option>
                    <option value="coordinators">Coordinators</option>
                    <option value="admins">Admins</option>
                    <option value="readers">Readers</option>
                    <option value="all">All Users</option>
                </select>
            </div>

            <div class="mb-3" id="userSelection" style="display:none;">
                <label class="form-label">Choose Users</label>
                <select name="selected_users[]" class="form-select" multiple>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['names']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Send</button>
        </form>
    </div>

    <?php if (!empty($toast_message)): ?>
        <div class="toast <?= $toast_class ?>" role="alert">
            <div class="toast-body">
                <?= $toast_message ?>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleUserList() {
            const viewers = document.getElementById('viewers').value;
            document.getElementById('userSelection').style.display = viewers === 'selected_user' ? 'block' : 'none';
        }

        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.querySelector('.toast');
            if (toastEl) {
                new bootstrap.Toast(toastEl, {
                    delay: 1000
                }).show();
            }
        });
    </script>

</body>

</html>

<?php include '../templates/footer.php'; ?>