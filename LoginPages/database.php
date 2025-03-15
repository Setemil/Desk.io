<?php
// Database configuration
$db_host = "localhost"; 
$db_name = "deskio_db";
$db_user = "root";
$db_pass = "";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
