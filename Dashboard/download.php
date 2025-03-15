<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: ../LoginPages/log-in.php');
    exit();
}

// Check if file parameter exists
if(!isset($_GET['file']) || empty($_GET['file'])) {
    $_SESSION['error'] = "Invalid file request.";
    header('Location: ' . ($_SESSION['role'] === 'student' ? 'student-dashboard.php' : 'teacher-dashboard.php'));
    exit();
}

$file_path = $_GET['file'];

// Security check: Prevent directory traversal
$real_path = realpath($file_path);
$upload_dir = realpath('uploads'); // Assuming uploads directory

if($real_path === false || strpos($real_path, $upload_dir) !== 0) {
    $_SESSION['error'] = "Access denied. Invalid file path.";
    header('Location: ' . ($_SESSION['role'] === 'student' ? 'student-dashboard.php' : 'teacher-dashboard.php'));
    exit();
}

// Database connection to verify the file exists in our database
require_once('../LoginPages/database.php');

// Make sure $conn is a valid database connection
if(!$conn || $conn->connect_error) {
    $_SESSION['error'] = "Database connection error.";
    header('Location: ' . ($_SESSION['role'] === 'student' ? 'student-dashboard.php' : 'teacher-dashboard.php'));
    exit();
}

$sql = "SELECT * FROM materials WHERE pdf_path = ?";
$stmt = $conn->prepare($sql);

if(!$stmt) {
    $_SESSION['error'] = "Database prepare error: " . $conn->error;
    header('Location: ' . ($_SESSION['role'] === 'student' ? 'student-dashboard.php' : 'teacher-dashboard.php'));
    exit();
}

$stmt->bind_param("s", $file_path);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    $_SESSION['error'] = "File not found in database.";
    $stmt->close();
    $conn->close();
    header('Location: ' . ($_SESSION['role'] === 'student' ? 'student-dashboard.php' : 'teacher-dashboard.php'));
    exit();
}

// Close database connection properly
$stmt->close();
$conn->close();

// Get file information
$file_info = pathinfo($real_path);
$file_name = $file_info['basename'];

// Check if file exists and is readable
if(!file_exists($real_path) || !is_readable($real_path)) {
    $_SESSION['error'] = "File not found or not readable.";
    header('Location: ' . ($_SESSION['role'] === 'student' ? 'student-dashboard.php' : 'teacher-dashboard.php'));
    exit();
}

// Set appropriate headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . filesize($real_path));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Clear output buffer
ob_clean();
flush();

// Read the file and output it to the browser
readfile($real_path);
exit();
?>