<?php
session_start();
require_once '../includes/db.php'; // Ensure correct database connection
include '../templates/header.php'; // Assuming this includes the header HTML

// Check if the user is authorized to view this page
if ($_SESSION['role'] !== 'coordinator') {
    header('Location: ../login.php');
    exit;
}

// Initialize variables
$assignments = [];
$heading = '';

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $type = isset($_POST['type']) ? $_POST['type'] : ''; // week or month
    $heading = trim($_POST['heading']);
    
    // Validate the selection
    if (empty($type)) {
        echo "<p class='alert alert-danger'>Please select a valid type (week/month).</p>";
    }

    $today = date('Y-m-d');

    // Set date range based on selection
    if ($type === 'week') {
        $start = date('Y-m-d', strtotime('monday this week'));
        $end = date('Y-m-d', strtotime('sunday this week'));
    } elseif ($type === 'month') {
        $start = date('Y-m-01');
        $end = date('Y-m-t');
    } else {
        // Handle invalid input
        echo "<p class='alert alert-danger'>Invalid type selected. Please try again.</p>";
        exit;
    }

    // SQL Query to fetch assignments and related details
    $stmt = $conn->prepare("
        SELECT 
            a.id as assignment_id, 
            e.event_date, 
            a.role as reader_role, 
            e.event_time, 
            a.notes, 
            e.title, 
            u.names as reader_name 
        FROM assignments a
        JOIN events e ON a.event_id = e.id
        JOIN users u ON a.reader_id = u.id
        WHERE e.event_date BETWEEN ? AND ?
        ORDER BY e.event_date ASC
    ");
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any assignments are found
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $assignments[] = $row;
        }
    } else {
        echo "<p class='alert alert-warning'>No assignments found for the selected period.</p>";
    }
}
?>

<div class="container mt-5">
    <h1 class="mb-4">Print Assignment Sheet</h1>

    <!-- Form to select type and heading -->
    <form method="POST" class="card p-4 shadow-sm rounded-4 mb-4">
        <div class="mb-3">
            <label for="type" class="form-label">Select Type</label>
            <select name="type" id="type" class="form-select" required>
                <option value="">-- Choose --</option>
                <option value="week" <?= (isset($_POST['type']) && $_POST['type'] == 'week') ? 'selected' : '' ?>>This Week</option>
                <option value="month" <?= (isset($_POST['type']) && $_POST['type'] == 'month') ? 'selected' : '' ?>>This Month</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="heading" class="form-label">Custom Heading</label>
            <input type="text" name="heading" id="heading" class="form-control" placeholder="Enter Custom Heading" value="<?= htmlspecialchars($heading) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Generate Sheet</button>
    </form>

    <!-- Display assignments if available -->
    <?php if (!empty($assignments)): ?>
    <div id="printArea" class="card shadow-sm p-4 rounded-4">
        <h2 class="text-center mb-4"><?= htmlspecialchars($heading) ?></h2>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Event Name</th>
                    <th>Reader Name</th>
                    <th>Role</th>
                    <th>Event Time</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('D, M j, Y', strtotime($assignment['event_date']))) ?></td>
                        <td><?= htmlspecialchars($assignment['title']) ?></td>
                        <td><?= htmlspecialchars($assignment['reader_name']) ?></td>
                        <td><?= htmlspecialchars($assignment['reader_role']) ?></td>
                        <td><?= htmlspecialchars($assignment['event_time']) ?></td>
                        <td><?= htmlspecialchars($assignment['notes']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-center mt-4">
            <button type="button" onclick="printDiv()" class="btn btn-success"><i class="fa-solid fa-print"></i> Print </button>
        </div>

        <div class="mt-5 text-center small text-muted">
            <hr>
            <p>Generated by St. Basile Community Mass Scheduler System</p>
            <p><?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- JavaScript for printing -->
<script>
function printDiv() {
    // Get the content from the print area div
    var divContents = document.getElementById("printArea").innerHTML;

    // Open a new window to handle the printing
    var printWindow = window.open('', '', 'height=800,width=1000');
    printWindow.document.write('<html><head><title>Assignment Sheet</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(divContents);
    printWindow.document.write('</body></html>');

    // Close the document to ensure the content is loaded in the print window
    printWindow.document.close();

    // Trigger the print dialog in the new window
    printWindow.print();
}

function downloadCSV() {
    var table = document.getElementById('assignmentsTable');
    var rows = table.rows;
    var csv = [];
    
    // Loop through the rows and columns to get the table data
    for (var i = 0; i < rows.length; i++) {
        var row = [];
        var cols = rows[i].cells;
        for (var j = 0; j < cols.length; j++) {
            row.push(cols[j].innerText);  // Collect cell text
        }
        csv.push(row.join(','));  // Join columns with commas
    }
    
    // Create a CSV string and trigger download
    var csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    var downloadLink = document.createElement('a');
    downloadLink.download = 'assignments.csv';  // Default file name
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.click();
}

</script>

<?php include '../templates/footer.php'; ?>
