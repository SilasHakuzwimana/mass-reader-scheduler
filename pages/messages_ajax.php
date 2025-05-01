<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

$filter = $_GET['filter'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$sender_filter = $_GET['sender'] ?? 'all';

// Dynamic SQL WHERE and bindings
$whereClauses = [
    "(viewers = 'all' OR viewers = ? OR receiver_id = ? OR sender_id = ?)",
    "(visibility = 'public' OR (visibility = 'private' AND (receiver_id = ? OR sender_id = ?)))",
    "m.receiver_id != m.sender_id"
];

$param_types = "siiii";
$params = [$user_role, $user_id, $user_id, $user_id, $user_id];

if ($status_filter != 'all') {
    $whereClauses[] = "status = ?";
    $param_types .= "s";
    $params[] = $status_filter;
}

if ($sender_filter != 'all') {
    $whereClauses[] = "sender_id = ?";
    $param_types .= "i";
    $params[] = $sender_filter;
}

$whereSQL = implode(" AND ", $whereClauses);

$query = "SELECT m.*, 
                 u1.names AS sender_name, u1.email AS sender_email, 
                 u2.names AS receiver_name 
          FROM messages m
          LEFT JOIN users u1 ON m.sender_id = u1.id
          LEFT JOIN users u2 ON m.receiver_id = u2.id
          WHERE $whereSQL
          ORDER BY m.created_at DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<div id="message-list">
    <?php if ($result->num_rows > 0): ?>
        <div class="list-group">
            <?php while ($message = $result->fetch_assoc()): ?>
                <div class="list-group-item message-item <?= $message['status'] == 'unread' ? 'bg-light' : '' ?>" id="message-<?= $message['id'] ?>">
                    <h5>
                        From: <?= htmlspecialchars($message['sender_name']) ?>
                        (<a href="mailto:<?= htmlspecialchars($message['sender_email']) ?>"><?= htmlspecialchars($message['sender_email']) ?></a>)
                    </h5>
                    <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                    <small class="text-muted d-block mb-2">
                        Sent on: <?= $message['created_at'] ?> |
                        To: <?= $message['receiver_name'] ? htmlspecialchars($message['receiver_name']) : 'All / Role-based' ?> |
                        Viewers: <?= htmlspecialchars($message['viewers']) ?> |
                        Visibility: <span class="badge bg-<?= $message['visibility'] == 'private' ? 'warning' : 'success' ?>">
                            <?= ucfirst($message['visibility']) ?>
                        </span> |
                        <span class="message-status <?= $message['status'] ?>"><?= ucfirst($message['status']) ?></span>
                    </small>
                    <?php if ($message['status'] == 'unread'): ?>
                        <button class="btn btn-sm btn-primary mark-read-btn" data-id="<?= $message['id'] ?>">Mark as Read</button>
                    <?php endif; ?>
                    <?php if ($message['receiver_id'] == $user_id): ?>
                        <a href="send_message.php?reply_to=<?= $message['id'] ?>&sender_id=<?= $message['sender_id'] ?>" class="btn btn-sm btn-outline-secondary">Reply</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No messages found.</p>
    <?php endif; ?>
</div>
