<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mass Reader App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <!-- Add brand name to navbar -->
            <a class="navbar-brand" href="#">Mass Scheduler App</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0);" onclick="redirectToAdmin();">
                                    <i class="fas fa-user-shield me-1"></i> Admin Dashboard
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'coordinator'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0);" onclick="redirectToCoordinator();">
                                    <i class="fas fa-user-cog me-1"></i> Coordinator Dashboard
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0);" onclick="redirectToReader();">
                                    <i class="fas fa-user me-1"></i> Reader Dashboard
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0);" onclick="redirectToLogout();">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0);" onclick="redirectToLogin();">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0);" onclick="redirectToRegister();">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>    

<div class="container">
<script>
    function redirectToReader(){
        window.location.href="/mass_reader_scheduler/pages/reader_dashboard.php";
    }
    
    function redirectToAdmin(){
        window.location.href="/mass_reader_scheduler/pages/admin_dashboard.php";
    }
    
    function redirectToCoordinator(){
        window.location.href="/mass_reader_scheduler/pages/coordinator_dashboard.php";
    }

    function redirectToLogin(){
        window.location.href="/mass_reader_scheduler/login.php";
    }

    function redirectToRegister(){
        window.location.href="/mass_reader_scheduler/register.php";
    }

    function redirectToLogout(){
        window.location.href="/mass_reader_scheduler/logout.php";
    }
</script>