<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: ../LoginPages/log-in.php');
    exit();
}

// Check if user is either a teacher or a class-rep
if($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'class_rep') {
    header('Location: ../LoginPages/log-in.php');
    exit();
}

// Store user role for later use
$user_role = $_SESSION['role'];

// Check if material ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid material ID.";
    // Redirect based on role
    if($user_role == 'teacher') {
        header('Location: teacher-dashboard.php');
    } else {
        header('Location: class-rep-dashboard.php');
    }
    exit();
}

$material_id = $_GET['id'];

// Database connection
require_once('../LoginPages/database.php');

// Get the material information without teacher_id restriction
$sql = "SELECT * FROM materials WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $material_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    $_SESSION['error'] = "Material doesn't exist.";
    // Redirect based on role
    if($user_role == 'teacher') {
        header('Location: teacher-dashboard.php');
    } else {
        header('Location: class-rep-dashboard.php');
    }
    exit();
}

// Get the material information (especially the PDF path)
$material = $result->fetch_assoc();
$pdf_path = $material['pdf_path'];

// Delete the material from the database - no teacher_id restriction
$sql = "DELETE FROM materials WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $material_id);

if($stmt->execute()) {
    // If successful, also delete the PDF file if it exists
    if(!empty($pdf_path) && file_exists($pdf_path)) {
        unlink($pdf_path);
    }
    
    $_SESSION['success'] = "Material successfully deleted.";
} else {
    $_SESSION['error'] = "Database error: " . $conn->error;
}

// Close database connection
$stmt->close();
$conn->close();

// Redirect back to the appropriate dashboard based on role
if($user_role == 'teacher') {
    header('Location: teacher-dashboard.php');
} else {
    header('Location: class-rep-dashboard.php');
}
exit();
?>