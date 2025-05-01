<?php
require_once 'config.php';
// Set timezone
date_default_timezone_set('Africa/Kigali');

// Database configuration
$host = HOST;
$user = USER; 
$pass = PASS;     
$dbname = DB_NAME;

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
