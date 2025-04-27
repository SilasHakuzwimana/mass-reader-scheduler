<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

// Access control
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle Add User
if (isset($_POST['add_user'])) {
    $names = trim($_POST['names']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = password_hash('default123', PASSWORD_BCRYPT); // Default password

    $stmt = $conn->prepare("INSERT INTO users (names, email, role, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $names, $email, $role, $password);
    $stmt->execute();

    header('Location: manage_users.php');
    exit;
}

// Handle Edit User
if (isset($_POST['edit_user'])) {
    $id = $_POST['user_id'];
    $names = trim($_POST['names']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET names = ?, email = ?, role = ? WHERE id = ?");
    $stmt->bind_param('sssi', $names, $email, $role, $id);
    $stmt->execute();

    header('Location: manage_users.php');
    exit;
}

// Handle Delete User
if (isset($_POST['delete_user'])) {
    $id = $_POST['user_id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    header('Location: manage_users.php');
    exit;
}

// Fetch all users
$result = $conn->query("SELECT id, names, email, role FROM users ORDER BY names ASC");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <h2>Manage Users</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fa fa-user-plus"></i> Add User
        </button>
    </div>

    <div class="card shadow rounded-4 p-3">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Names</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $index => $user): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($user['names']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= ucfirst($user['role']) ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editUserModal" 
                                data-id="<?= $user['id'] ?>"
                                data-names="<?= htmlspecialchars($user['names']) ?>"
                                data-email="<?= htmlspecialchars($user['email']) ?>"
                                data-role="<?= $user['role'] ?>">
                                <i class="fa fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteUserModal"
                                data-id="<?= $user['id'] ?>"
                                data-names="<?= htmlspecialchars($user['names']) ?>">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No users found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="names" class="form-label">Names</label>
                    <input type="text" class="form-control" name="names" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="">-- Select Role --</option>
                        <option value="admin">Admin</option>
                        <option value="coordinator">Coordinator</option>
                        <option value="reader">Reader</option>
                    </select>
                </div>
                <small class="text-muted">Default password: <b>default123</b> (user can change after login)</small>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_user" class="btn btn-success">Add User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <input type="hidden" name="user_id" id="editUserId">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="editNames" class="form-label">Names</label>
                    <input type="text" class="form-control" name="names" id="editNames" required>
                </div>
                <div class="mb-3">
                    <label for="editEmail" class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" id="editEmail" required>
                </div>
                <div class="mb-3">
                    <label for="editRole" class="form-label">Role</label>
                    <select name="role" id="editRole" class="form-select" required>
                        <option value="admin">Admin</option>
                        <option value="coordinator">Coordinator</option>
                        <option value="reader">Reader</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <input type="hidden" name="user_id" id="deleteUserId">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user: <strong id="deleteUserName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="submit" name="delete_user" class="btn btn-danger">Yes, Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Fill Edit Modal
var editUserModal = document.getElementById('editUserModal');
editUserModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var userId = button.getAttribute('data-id');
    var names = button.getAttribute('data-names');
    var email = button.getAttribute('data-email');
    var role = button.getAttribute('data-role');

    document.getElementById('editUserId').value = userId;
    document.getElementById('editNames').value = names;
    document.getElementById('editEmail').value = email;
    document.getElementById('editRole').value = role;
});

// Fill Delete Modal
var deleteUserModal = document.getElementById('deleteUserModal');
deleteUserModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var userId = button.getAttribute('data-id');
    var userName = button.getAttribute('data-names');

    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').innerText = userName;
});
</script>

<?php include '../templates/footer.php'; ?>
