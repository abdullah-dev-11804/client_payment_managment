<?php
chdir(__DIR__);
// helpers/NotificationService.php
require_once __DIR__ . '/../vendor/autoload.php'; 
//require_once 'path/to/Twilio/autoload.php';    // Adjust path
// Fixed (using __DIR__)
require_once __DIR__ . '/../config/config.php';       // Include config.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Twilio\Rest\Client;

class NotificationService {
    private $mail;
    private $twilio;

    public function __construct() {
        // Configure PHPMailer for email using values from config.php
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = SMTP_HOST; // From config.php
        $this->mail->SMTPAuth = true;
        $this->mail->Username = SMTP_USER; // From config.php
        $this->mail->Password = SMTP_PASS; // From config.php
        $this->mail->SMTPSecure = 'tls'; // Use 'ssl' if required
        $this->mail->Port = SMTP_PORT; // From config.php
        $this->mail->setFrom(SMTP_USER, 'client_payment_system'); // From config.php

        // // Configure Twilio for SMS using values from config.php
        // $sid = TWILIO_SID; // From config.php
        // $token = TWILIO_TOKEN; // From config.php
        // $this->twilio = new Client($sid, $token);
    }

    /**
     * Send an email notification
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body
     */
    public function sendEmail($to, $subject, $body) {
        try {
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->send();
            return true; // Email sent successfully
        } catch (Exception $e) {
            error_log("Email could not be sent. Error: {$this->mail->ErrorInfo}");
            return false; // Email failed to send
        }
    }

    /**
     * Send an SMS notification
     * @param string $to Recipient phone number
     * @param string $message SMS message
     */
    // public function sendSMS($to, $message) {
    //     try {
    //         $this->twilio->messages->create($to, [
    //             'from' => TWILIO_PHONE_NUMBER, // From config.php
    //             'body' => $message
    //         ]);
    //         return true; // SMS sent successfully
    //     } catch (Exception $e) {
    //         error_log("SMS could not be sent. Error: {$e->getMessage()}");
    //         return false; // SMS failed to send
    //     }
    // }
}
?>