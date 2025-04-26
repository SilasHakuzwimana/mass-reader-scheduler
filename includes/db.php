<?php
// Set timezone
date_default_timezone_set('Africa/Kigali');

// Database configuration
$host = "localhost";
$user = "root"; 
$pass = "";     
$dbname = "mass_scheduler";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
