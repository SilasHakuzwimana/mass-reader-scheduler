<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/mailer.php';
include '../templates/header.php';

// Fetch available lectures (not yet assigned)
$lectures = $pdo->query('SELECT * FROM lectures WHERE reader_id IS NULL ORDER BY date, time')->fetchAll();

// Fetch all readers
$readers = $pdo->query('SELECT * FROM readers')->fetchAll();

// Handle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lecture_id = $_POST['lecture_id'];
    $reader_id = $_POST['reader_id'];

    // Assign the reader
    $stmt = $pdo->prepare('UPDATE lectures SET reader_id = ? WHERE id = ?');
    $stmt->execute([$reader_id, $lecture_id]);

    // Get reader info
    $stmt = $pdo->prepare('SELECT name, email FROM readers WHERE id = ?');
    $stmt->execute([$reader_id]);
    $reader = $stmt->fetch();

    // Get lecture info
    $stmt = $pdo->prepare('SELECT date, time FROM lectures WHERE id = ?');
    $stmt->execute([$lecture_id]);
    $lecture = $stmt->fetch();

    // Send email
    $subject = "You have been assigned to a Mass Reading";
    $body = "Hello {$reader['name']},<br><br>You have been assigned to read on <strong>{$lecture['date']}</strong> at <strong>{$lecture['time']}</strong>.<br><br>Thank you!";
    sendMail($reader['email'], $subject, $body);

    echo "<div class='alert alert-success'>Reader assigned and notified via email!</div>";
}
?>

<h2>Assign Reader to Lecture</h2>
<form method="POST" class="form-inline">
    <div class="form-group mr-2">
        <label for="lecture_id" class="mr-2">Select Lecture</label>
        <select name="lecture_id" id="lecture_id" class="form-control" required>
            <?php foreach ($lectures as $lecture): ?>
                <option value="<?= $lecture['id'] ?>">
                    <?= $lecture['date'] ?> at <?= $lecture['time'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group mr-2">
        <label for="reader_id" class="mr-2">Select Reader</label>
        <select name="reader_id" id="reader_id" class="form-control" required>
            <?php foreach ($readers as $reader): ?>
                <option value="<?= $reader['id'] ?>"><?= $reader['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Assign</button>
</form>

<?php include '../templates/footer.php'; ?>
