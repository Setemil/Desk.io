<?php

session_start();

include 'database.php';

if (!isset($_SESSION['email'])) {
    die("Error: No email found in session.");
}

$storedEmail = $_SESSION['email'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Use bcrypt hashing

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $password, $storedEmail);
$updateQueueResult = $stmt->execute();

if ($stmt->affected_rows > 0) {
    header("Location: log-in.php");
    exit();
} else {
    echo "No rows updated. Either the email does not exist or the password is the same as before.";
}

$stmt->close();
$conn->close();
?>
