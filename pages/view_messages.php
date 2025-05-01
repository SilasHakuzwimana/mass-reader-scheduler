<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Messages</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .message-item:hover {
            background-color: #f8f9fa;
        }

        .message-status.unread {
            color: red;
            font-weight: bold;
        }

        .message-status.read {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">View Messages</h1>
        <form id="filter-form" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="filter" class="form-label">Filter by Viewer Role</label>
                <select name="filter" id="filter" class="form-select">
                    <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All</option>
                    <option value="coordinator" <?= $filter == 'coordinator' ? 'selected' : '' ?>>Coordinators</option>
                    <option value="admin" <?= $filter == 'admin' ? 'selected' : '' ?>>Admins</option>
                    <option value="reader" <?= $filter == 'reader' ? 'selected' : '' ?>>Readers</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Filter by Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All</option>
                    <option value="unread" <?= $status_filter == 'unread' ? 'selected' : '' ?>>Unread</option>
                    <option value="read" <?= $status_filter == 'read' ? 'selected' : '' ?>>Read</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="sender" class="form-label">Filter by Sender</label>
                <select name="sender" id="sender" class="form-select">
                    <option value="all" <?= $sender_filter == 'all' ? 'selected' : '' ?>>All</option>
                    <?php
                    $user_query = "SELECT id, names FROM users";
                    $user_result = $conn->query($user_query);
                    while ($user = $user_result->fetch_assoc()) {
                        echo "<option value='{$user['id']}'" . ($sender_filter == $user['id'] ? ' selected' : '') . ">{$user['names']}</option>";
                    }
                    ?>
                </select>
            </div>
        </form>

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
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Filter reload
    $('#filter-form select').on('change', function() {
        const filter = $('#filter').val();
        const status = $('#status').val();
        const sender = $('#sender').val();
        $.get('messages_ajax.php', {
            filter: filter,
            status: status,
            sender: sender
        }, function(data) {
            $('#message-list').html($(data).find('#message-list').html());
        });
    });

    // Auto-refresh messages every 10 seconds
    setInterval(function() {
        const filter = $('#filter').val();
        const status = $('#status').val();
        const sender = $('#sender').val();
        $.get('messages_ajax.php', {
            filter: filter,
            status: status,
            sender: sender,
            t: new Date().getTime()
        }, function(data) {
            $('#message-list').html($(data).find('#message-list').html());
        });
    }, 1000); // 1 seconds

    // Mark as read
    $(document).on('click', '.mark-read-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        $.post('mark_as_read.php', {
            message_id: id
        }, function(response) {
            if (response === 'success') {
                btn.closest('.message-item')
                    .find('.message-status')
                    .removeClass('unread')
                    .addClass('read')
                    .text('Read');
                btn.remove();
            } else {
                alert('Error marking as read');
            }
        });
    });
</script>
</body>

</html>

<?php include '../templates/footer.php'; ?>