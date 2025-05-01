<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


require_once '../config.php';

/*
The function uses the PHPMailer object to send an email to the address we specify

@param [string] $email, [where our email goes]
@param [string] $subject, [The email's subject]
@param [string] $message, [The message]
@return [string] [Error message, or success]
*/
function sendEmail($email, $subject, $message)
{
    //creating a new PHPMailer object.
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = MAILHOST;
        $mail->SMTPAuth = true;
        $mail->Username = USERNAME;
        $mail->Password = PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        //Recipients
        $mail->setFrom(SEND_FROM, 'St. Basile Community readers scheduler System');
        $mail->addAddress($email);
        $mail->addReplyTo(SEND_FROM, 'St. Basile Community readers scheduler System');

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        if ($mail->send()) {
            return "Email sent successfully";
        } else {
            return "Error sending email: " . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        return "Error sending email: " . $mail->ErrorInfo;
    }
}
