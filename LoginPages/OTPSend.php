<?php

session_start();
include 'database.php'; 

$email = $_POST['email'];
$otp = rand(100000, 999999);

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        $mail->isSMTP();
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER; 
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'setemiloye1@gmail.com';
        $mail->Password   = 'nfqe xfcs iclp puss'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port       = 465;

        // Fix: Manually set sender email
        $mail->setFrom('setemiloye1@gmail.com', 'Desk.io');
        $mail->addAddress($email); 

        $mail->isHTML(true);
        $mail->Subject = 'Desk.io OTP';
        $mail->Body    = "Your OTP is: <strong>$otp</strong>. If you did not request this, please ignore this email.";

        if ($mail->send()) {
            header("Location: OTPInput.php");
            exit();
        } else {
            $error = "Email not sent.";
        }
    } else {
        $error = "Email does not exist.";
    }
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}

if (isset($error)) {
    // This is a simple way to pass the error. A better way would be to use sessions.
    header("Location: OTPRequest.php?error=" . urlencode($error)); 
    exit();
}
?>
