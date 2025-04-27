<?php
if (!isset($_SESSION)) {
    session_start();
}
date_default_timezone_set('Africa/Kigali');

// Get user info from session
$user_id = $_SESSION['user_id'] ?? 'N/A';
$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_email = $_SESSION['email'] ?? 'Unknown Email';
$user_role = $_SESSION['role'] ?? 'Unknown Role';

// Decide card color based on role
$card_class = match (strtolower($user_role)) {
    'admin' => 'border-danger',
    'reader' => 'border-primary',
    'coordinator' => 'border-success',
    default => 'border-secondary',
};
$badge_class = match (strtolower($user_role)) {
    'admin' => 'bg-danger',
    'reader' => 'bg-primary',
    'coordinator' => 'bg-success',
    default => 'bg-secondary',
};
?>
<!-- FontAwesome for icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<div class="card shadow-sm mb-4 <?= $card_class ?>">
    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="mb-3 mb-md-0">
            <p class="fw-bold">
                <i class="fas fa-user-circle fa-spin me-2 text-info"></i>User Details:
            </p>
            <h5 class="card-title mb-1">
                <i class="fas fa-user fa-spin me-2 text-primary"></i><?= htmlspecialchars($user_name) ?>
            </h5>
            <p class="card-text mb-0">
                <i class="fas fa-id-badge fa-spin me-2 text-warning"></i><strong>ID:</strong> <?= htmlspecialchars($user_id) ?>
            </p>
            <p class="card-text mb-0">
                <i class="fas fa-envelope fa-spin me-2 text-success"></i><strong>Email:</strong> <?= htmlspecialchars($user_email) ?>
            </p>
            <p class="card-text mb-0">
                <i class="fas fa-user-tag fa-spin me-2 text-danger"></i><strong>Role:</strong> 
                <span class="badge <?= $badge_class ?>"><?= ucfirst(htmlspecialchars($user_role)) ?></span>
            </p>
        </div>
        <div class="text-end">
            <span id="currentDatetime" class="badge bg-dark fs-6">
                <i class="fas fa-clock fa-spin me-1 text-warning"></i>
            </span>
        </div>
    </div>
</div>

<!-- Live Clock Script -->
<script>
function updateClock() {
    const now = new Date();
    const options = { 
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', 
        hour: '2-digit', minute: '2-digit', second: '2-digit', 
        hour12: true 
    };
    document.getElementById('currentDatetime').innerHTML = '<i class="fas fa-clock fa-spin me-1 text-warning"></i> ' + now.toLocaleString('en-US', options);
}

// Update every second
setInterval(updateClock, 1000);
updateClock(); // initial call
</script>
