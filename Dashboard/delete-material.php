<?php
session_start();
// Check if user is logged in and is a teacher
if(!isset($_SESSION['user_id'])) {
    header('Location: ../LoginPages/log-in.php');
    exit();
}

// Check if material ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid material ID.";
    header('Location: teacher-dashboard.php');
    exit();
}

$material_id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

// Database connection
require_once('../LoginPages/database.php');

// First, check if this material belongs to the current teacher
$sql = "SELECT * FROM materials WHERE id = ? AND teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $material_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    $_SESSION['error'] = "You don't have permission to delete this material or it doesn't exist.";
    header('Location: teacher-dashboard.php');
    exit();
}

// Get the material information (especially the PDF path)
$material = $result->fetch_assoc();
$pdf_path = $material['pdf_path'];

// Delete the material from the database
$sql = "DELETE FROM materials WHERE id = ? AND teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $material_id, $teacher_id);

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

// Redirect back to the teacher dashboard
header('Location: teacher-dashboard.php');
exit();
?>