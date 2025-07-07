<?php
include('includes/dbconnection.php');
// PHPMailer namespace imports
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';
require 'includes/PHPMailer/src/Exception.php';

// Set your admin email
$adminEmail = 'junno.elizer@gmail.com'; // CHANGE THIS to your real admin email

// Find all unnotified attack_detected events
$query = mysqli_query($con, "SELECT * FROM audit_logs WHERE event_type = 'attack_detected' AND notified = 0");

if (mysqli_num_rows($query) > 0) {
    $msg = "Suspicious activity detected:\n\n";
    $ids = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $msg .= "User: {$row['user']}, IP: {$row['ip_address']}, Details: {$row['event_details']}, Time: {$row['timestamp']}\n";
        $ids[] = $row['id'];
    }
    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ia2studentgrading@gmail.com'; // Your Gmail address
        $mail->Password   = 'zrrw jbcl hvxd ltdw'; // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('ia2studentgrading@gmail.com', 'Audit Log Monitor');
        $mail->addAddress($adminEmail);
        $mail->isHTML(false);
        $mail->Subject = 'Security Alert: Suspicious Activity Detected';
        $mail->Body    = $msg;
        $mail->send();
    } catch (Exception $e) {
        error_log('PHPMailer Error: ' . $mail->ErrorInfo);
    }
    // Mark these events as notified
    if (!empty($ids)) {
        $ids_str = implode(',', array_map('intval', $ids));
        mysqli_query($con, "UPDATE audit_logs SET notified = 1 WHERE id IN ($ids_str)");
    }
}
// Optional: else, do nothing 