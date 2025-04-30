<?php
// Set timezone
date_default_timezone_set('Africa/Kigali');

// Database configuration
$host = "localhost";
$user = "root"; 
$pass = "";     
$dbname = "if0_38626920_stbasile_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
