<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

ob_start();
header('Content-Type: application/json; charset=utf-8');

// Enable error reporting for debugging
ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('error_log', 'php_errors.log');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Check if the configuration file exists
if (!file_exists('configure.php')) {
    echo json_encode(["success" => false, "message" => "Configuration file is missing."]);
    exit;
}
require 'configure.php';

// Check if required constants are defined
if (!defined('MAILHOST') || !defined('USERNAME') || !defined('PASSWORD') || !defined('SEND_FROM')) {
    echo json_encode(["success" => false, "message" => "SMTP configuration missing in configure.php."]);
    exit;
}

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

/**
 * Sends an email using PHPMailer
 *
 * @param string $email    Recipient's email address
 * @param string $subject  Email subject
 * @param string $message  Email message body (HTML format supported)
 * @return mixed           True if email sent successfully, else error message
 */
function sendEmail($email, $subject, $message)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = MAILHOST;
        $mail->SMTPAuth = true;
        $mail->Username = USERNAME;
        $mail->Password = PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Recipients
        $mail->setFrom(SEND_FROM, 'St. Basile Community readers scheduler System');
        $mail->addAddress($email);
        $mail->addReplyTo(SEND_FROM, 'St. Basile Community readers scheduler System');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);

        // Send the email
        return $mail->send() ? true : $mail->ErrorInfo;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (!isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['subject'], $_POST['message'])) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    // Sanitize user inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Validate email format
    if (!$email) {
        echo json_encode(["success" => false, "message" => "Invalid email format."]);
        exit;
    }

    // Check if email templates exist
    if (!file_exists('templates/admin_email_template.html') || !file_exists('templates/user_email_template.html')) {
        echo json_encode(["success" => false, "message" => "Email template files are missing."]);
        exit;
    }

    // Load email templates
    $adminMessage = file_get_contents('templates/admin_email_template.html');
    $userMessage = file_get_contents('templates/user_email_template.html');

    if (!$adminMessage || !$userMessage) {
        echo json_encode(["success" => false, "message" => "Failed to load email templates."]);
        exit;
    }

    // Prepare email for admin
    $adminSubject = "Membership Request from $name";
    $adminMessage = str_replace(
        ['{{name}}', '{{email}}', '{{phone}}', '{{subject}}', '{{message}}'],
        [$name, $email, $phone, $subject, nl2br($message)],
        $adminMessage
    );
    $adminResponse = sendEmail('hakuzwisilas@gmail.com', $adminSubject, $adminMessage);

    // Prepare email for the user
    $userSubject = "Membership Request Confirmation";
    $userMessage = str_replace(
        ['{{name}}', '{{subject}}', '{{message}}'],
        [$name, $subject, nl2br($message)],
        $userMessage
    );
    $userResponse = sendEmail($email, $userSubject, $userMessage);

    // Insert membership request data into the database
    if ($adminResponse === true && $userResponse === true) {
        // Set timezone to CAT (Central Africa Time, Rwanda)
        date_default_timezone_set('Africa/Kigali');

        // Get current timestamp
        $timestamp = date('Y-m-d H:i:s');

        // Prepare the SQL query to insert the data
        $stmt = $conn->prepare("INSERT INTO membership_requests (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
            exit;
        }

        $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $message, $timestamp);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Membership request submitted successfully! You will receive a confirmation email shortly."
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error saving to database: " . $stmt->error
            ]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error sending email. Admin: " . ($adminResponse !== true ? $adminResponse : "OK") .
                " | User: " . ($userResponse !== true ? $userResponse : "OK")
        ]);
    }

    exit;
}

header('Content-Type: application/json');
echo json_encode(["success" => false, "message" => "Invalid request method."]);
exit;
?>
