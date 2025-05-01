<?php
include 'templates/header.php';
?>

<!-- Landing Page Custom Style -->
<!-- FontAwesome Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/indexstyles.css">
<style>
   
</style>

<!-- Hero Section -->
<section class="hero">
    <h1>Welcome to the Mass Reader Scheduling App</h1>
    <p>Manage and organize daily mass readings with ease and efficiency.</p>
    <a href="/login" class="btn btn-primary-custom">Get Started</a>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4 feature">
                <img src="assets/images/schedule.png" alt="Calendar Icon"/>
                <h3>Easy Scheduling</h3>
                <p>Quickly assign readers for daily and weekly masses with just a few clicks.</p>
            </div>
            <div class="col-md-4 feature">
                <img src="assets/images/reader.png" alter="Reader Icon" >
                <h3>Manage Readers</h3>
                <p>Track reader availability, preferences, and assignments all in one place.</p>
            </div>
            <div class="col-md-4 feature">
                <img src="assets/images/shield.png" alt="Shield Icon"/>
                <h3>Secure Access</h3>
                <p>Role-based access for coordinators, readers, and admins with most secure authentication &amp; authorization.</p>
            </div>
        </div>
    </div>
</section>

<!-- Footer Section -->

<?php include 'templates/footer.php'; ?>
