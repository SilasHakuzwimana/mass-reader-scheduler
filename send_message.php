<?php
// Include your database configuration file
include 'config.php';

// Initialize response array
$response = array(
    "success" => false,
    "error" => "An error occurred. Please try again."
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Set timezone to CAT (Central Africa Time, Rwanda)
    date_default_timezone_set('Africa/Kigali');

    // Get current timestamp
    $timestamp = date('Y-m-d H:i:s');

    // Insert message into database
    $sql = "INSERT INTO messages (name, phone, subject, message, sent_at) 
            VALUES ('$name', '$phone', '$subject', '$message', '$timestamp')";

    if ($conn->query($sql) === TRUE) {
        $response['success'] = true;
        $response['message'] = "Message sent successfully";
    } else {
        $response['error'] = "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    $response['error'] = "Invalid request method";
}

$conn->close(); // Close database connection

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
