<?php
session_start();

include 'database.php';

$storedOTP = $_SESSION['otp'];
$inputtedOTP = $_POST['OTP'];

if($inputtedOTP == $storedOTP){
    header("Location: ResetPassword.php");
    exit();
}
else{
    echo "Incorrect OTP";
}

?>