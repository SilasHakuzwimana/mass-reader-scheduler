<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

// Authorization check
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Initialize variables
$assignments = [];
$heading = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $heading = trim($_POST['heading']);

    if (empty($type)) {
        echo "<p class='alert alert-danger'>Please select a valid type (week/month).</p>";
    }

    if ($type === 'week') {
        $start = date('Y-m-d', strtotime('monday this week'));
        $end = date('Y-m-d', strtotime('sunday this week'));
    } elseif ($type === 'month') {
        $start = date('Y-m-01');
        $end = date('Y-m-t');
    } else {
        echo "<p class='alert alert-danger'>Invalid type selected. Please try again.</p>";
        exit;
    }

    $stmt = $conn->prepare("
        SELECT 
            a.id as assignment_id, 
            e.event_date, 
            a.role as reader_role, 
            e.event_time, 
            e.title, 
            u.names as reader_name 
        FROM assignments a
        JOIN events e ON a.event_id = e.id
        JOIN users u ON a.reader_id = u.id
        WHERE e.event_date BETWEEN ? AND ?
        ORDER BY e.event_date ASC
    ");

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }

    if (empty($assignments)) {
        echo "<p class='alert alert-warning'>No assignments found for the selected period.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Assignment Sheet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- External CSS/JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 2cm;
            }

            .footer {
                margin-top: 50px;
                font-size: 0.9rem;
                text-align: center;
                color: #6c757d;
            }
        }

        .export-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .export-buttons .btn {
            min-width: 120px;
        }

        .card {
            margin-bottom: 20px;
        }

        body {
            padding-bottom: 60px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Assignment Sheet</h1>

        <!-- Form -->
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

        <!-- Results -->
        <?php if (!empty($assignments)): ?>
            <div id="printArea" class="card shadow-sm p-4 rounded-4">
                <h2 class="text-center mb-4"><?= htmlspecialchars($heading) ?></h2>

                <table class="table table-bordered" id="assignmentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Event Name</th>
                            <th>Reader Name</th>
                            <th>Role</th>
                            <th>Event Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td><?= htmlspecialchars(date('D, M j, Y', strtotime($assignment['event_date']))) ?></td>
                                <td><?= htmlspecialchars($assignment['title']) ?></td>
                                <td><?= htmlspecialchars($assignment['reader_name']) ?></td>
                                <td><?= htmlspecialchars($assignment['reader_role']) ?></td>
                                <td><?= htmlspecialchars($assignment['event_time']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Export Buttons -->
                <div class="export-buttons no-print">
                    <button onclick="printDiv()" class="btn btn-success">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button onclick="downloadCSV()" class="btn btn-primary">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>
                    <button onclick="downloadExcel()" class="btn btn-warning">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button onclick="downloadPDF()" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>

                <!-- Footer -->
                <div class="footer text-center small text-muted mt-5">
                    <hr>
                    <p>Generated by <strong>St. Basile Community Mass Readers Scheduler System</strong></p>
                    <p><?= date('Y-m-d H:i:s') ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function printDiv() {
            const content = document.getElementById('printArea').innerHTML;
            const printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write(`
            <html>
            <head>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .no-print { display: none !important; }
                        body { margin: 2cm; font-family: Arial, sans-serif; }
                        .footer {
                            position: fixed;
                            bottom: 0;
                            left: 0;
                            right: 0;
                            text-align: center;
                            font-size: 0.9rem;
                            color: #6c757d;
                        }
                    }
                    .footer {
                        margin-top: 50px;
                        text-align: center;
                        font-size: 0.9rem;
                        color: #6c757d;
                    }
                </style>
            </head>
            <body onload="window.print()">
                ${content}
            </body>
            </html>
        `);
            printWindow.document.close();
        }

        function downloadPDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();
            const heading = "<?= htmlspecialchars($heading) ?>";

            doc.setFontSize(16);
            doc.text(heading, 14, 15);

            const headers = [];
            const data = [];
            const table = document.getElementById('assignmentsTable');

            table.querySelectorAll('thead th').forEach(th => headers.push(th.innerText));
            table.querySelectorAll('tbody tr').forEach(tr => {
                const row = [];
                tr.querySelectorAll('td').forEach(td => row.push(td.innerText));
                data.push(row);
            });

            doc.autoTable({
                head: [headers],
                body: data,
                startY: 25,
                theme: 'grid',
                styles: {
                    fontSize: 9
                },
                headStyles: {
                    fillColor: [41, 128, 185]
                },
                didDrawPage: function(data) {
                    const pageHeight = doc.internal.pageSize.height;
                    const footerText = "Generated by St. Basile Community Mass Readers Scheduler System";
                    const timestamp = new Date().toLocaleString();

                    doc.setFontSize(10);
                    doc.setTextColor(100);

                    const footerY = pageHeight - 20;
                    doc.text(footerText, doc.internal.pageSize.width / 2, footerY, {
                        align: 'center'
                    });
                    doc.text(timestamp, doc.internal.pageSize.width / 2, footerY + 5, {
                        align: 'center'
                    });
                }
            });

            doc.save('assignments_' + new Date().toISOString().slice(0, 10) + '.pdf');
        }

        function downloadCSV() {
            const table = document.getElementById('assignmentsTable');
            const rows = table.querySelectorAll('tr');
            const csv = [];

            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = Array.from(cols).map(col => `"${col.innerText.replace(/"/g, '""')}"`);
                csv.push(rowData.join(','));
            });

            const blob = new Blob([csv.join('\n')], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'assignments_' + new Date().toISOString().slice(0, 10) + '.csv';
            link.click();
        }

        function downloadExcel() {
            const table = document.getElementById('assignmentsTable');
            const workbook = XLSX.utils.table_to_book(table, {
                sheet: "Assignments"
            });
            XLSX.writeFile(workbook, 'assignments_' + new Date().toISOString().slice(0, 10) + '.xlsx');
        }
    </script>

    <?php include '../templates/footer.php'; ?>
</body>

</html>