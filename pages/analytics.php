<?php
session_start();
require_once '../includes/db.php';
include '../templates/header.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch analytics data
$userStats = $conn->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
$eventCount = $conn->query("SELECT COUNT(*) as total FROM events WHERE MONTH(event_date) = MONTH(CURRENT_DATE()) AND YEAR(event_date) = YEAR(CURRENT_DATE())")->fetch_assoc();
$assignmentCount = $conn->query("SELECT COUNT(*) as total FROM assignments WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetch_assoc();
$recentLogs = $conn->query("SELECT * FROM logs ORDER BY created_at DESC LIMIT 20");

// Fetch login/logout data for the current month
$loginLogoutStats = $conn->query("
    SELECT 
        DATE(created_at) as date, 
        COUNT(CASE WHEN action = 'User logged in' THEN 1 END) AS logins, 
        COUNT(CASE WHEN action = 'User logged out' THEN 1 END) AS logouts
    FROM logs
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
    GROUP BY DATE(created_at)
");

// Fetch daily system usage based on events
$systemUsageStats = $conn->query("
    SELECT 
        DATE(event_date) as date, 
        COUNT(*) as events
    FROM events
    WHERE MONTH(event_date) = MONTH(CURRENT_DATE()) 
    AND YEAR(event_date) = YEAR(CURRENT_DATE())
    GROUP BY DATE(event_date)
");

// Prepare data for charts
$userRoles = [];
$userCounts = [];
while ($row = $userStats->fetch_assoc()) {
    $userRoles[] = ucfirst($row['role']);
    $userCounts[] = $row['total'];
}

// Prepare login/logout chart data
$loginDates = [];
$loginCounts = [];
$logoutCounts = [];
while ($row = $loginLogoutStats->fetch_assoc()) {
    $loginDates[] = $row['date'];
    $loginCounts[] = $row['logins'];
    $logoutCounts[] = $row['logouts'];
}

// Prepare system usage chart data
$usageDates = [];
$usageCounts = [];
while ($row = $systemUsageStats->fetch_assoc()) {
    $usageDates[] = $row['date'];
    $usageCounts[] = $row['events'];
}
?>

<div class="container mt-4">
    <h2 class="mb-4">System Reports & Analytics</h2>

    <!-- USER ANALYTICS -->
    <div class="card mb-4">
        <div class="card-header">User Analytics</div>
        <div class="card-body">
            <div class="row">
                <?php while ($row = $userStats->fetch_assoc()): ?>
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <strong><?= ucfirst($row['role']) ?>s:</strong> <?= $row['total'] ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Chart for User Analytics -->
            <canvas id="userRolesChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- SYSTEM ANALYTICS -->
    <div class="card mb-4">
        <div class="card-header">System Analytics</div>
        <div class="card-body">
            <p><strong>Events this Month:</strong> <?= $eventCount['total'] ?></p>
            <p><strong>Assignments this Month:</strong> <?= $assignmentCount['total'] ?></p>
        </div>
    </div>

    <!-- LOGIN/LOGOUT ACTIVITY -->
    <div class="card mb-4">
        <div class="card-header">Login/Logout Activity</div>
        <div class="card-body">
            <!-- Chart for Login/Logout -->
            <canvas id="loginLogoutChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- SYSTEM USAGE -->
    <div class="card mb-4">
        <div class="card-header">System Usage</div>
        <div class="card-body">
            <!-- Chart for System Usage -->
            <canvas id="systemUsageChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- ACTIVITY LOGS -->
    <div class="card mb-4">
        <div class="card-header">Recent Activity Logs</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Action</th>
                        <th>IP</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($log = $recentLogs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($log['names']) ?></td>
                            <td><?= htmlspecialchars($log['email']) ?></td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                            <td><?= htmlspecialchars($log['ip_address']) ?></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ASSIGNMENT REPORTS -->
    <div class="card mb-4">
        <div class="card-header">Assignment Reports</div>
        <div class="card-body">
            <a href="assignment-sheet.php" class="btn btn-primary">
                <i class="fa fa-file-alt"></i> Generate Assignment Sheet
            </a>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// User Roles Chart
const userRolesCtx = document.getElementById('userRolesChart').getContext('2d');
const userRolesChart = new Chart(userRolesCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($userRoles); ?>,
        datasets: [{
            label: 'Users by Role',
            data: <?php echo json_encode($userCounts); ?>,
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],  // Colors for each role
            borderColor: ['#4e73df', '#1cc88a', '#36b9cc'],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Login/Logout Activity Chart
const loginLogoutCtx = document.getElementById('loginLogoutChart').getContext('2d');
const loginLogoutChart = new Chart(loginLogoutCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($loginDates); ?>,
        datasets: [
            {
                label: 'Logins',
                data: <?php echo json_encode($loginCounts); ?>,
                borderColor: '#1cc88a',
                fill: false,
                tension: 0.1
            },
            {
                label: 'Logouts',
                data: <?php echo json_encode($logoutCounts); ?>,
                borderColor: '#f6c23e',
                fill: false,
                tension: 0.1
            }
        ]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// System Usage Chart
const systemUsageCtx = document.getElementById('systemUsageChart').getContext('2d');
const systemUsageChart = new Chart(systemUsageCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($usageDates); ?>,
        datasets: [{
            label: 'Events',
            data: <?php echo json_encode($usageCounts); ?>,
            borderColor: '#4e73df',
            fill: false,
            tension: 0.1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include '../templates/footer.php'; ?>
