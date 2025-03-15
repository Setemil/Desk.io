<?php
// Start session if not already started
session_start();

include('../LoginPages/database.php');

// Process form submission
if(isset($_POST['submit'])) {
    // Get form data
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $message = $conn->real_escape_string($_POST['message']);
    $teacher_id = $_SESSION['user_id']; // Assuming teacher ID is stored in session
    $upload_date = date('Y-m-d H:i:s');
    
    // Initialize PDF file path
    $pdf_path = NULL;
    
    // Process PDF upload if a file was submitted
    if(isset($_FILES['pdf']) && $_FILES['pdf']['error'] == 0) {
        $allowed_types = ['application/pdf'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        if(in_array($_FILES['pdf']['type'], $allowed_types) && $_FILES['pdf']['size'] <= $max_size) {
            // Create upload directory if it doesn't exist
            $upload_dir = "uploads/";
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = uniqid('pdf_') . '.pdf';
            $destination = $upload_dir . $new_filename;
            
            // Move uploaded file
            if(move_uploaded_file($_FILES['pdf']['tmp_name'], $destination)) {
                $pdf_path = $destination;
            } else {
                $_SESSION['error'] = "Failed to upload file.";
                header("Location: teacher-dashboard.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid file. Please upload a PDF file under 10MB.";
            header("Location: teacher-dashboard.php");
            exit;
        }
    }
    
    // Insert data into database
    $sql = "INSERT INTO materials (title, description, message, pdf_path, teacher_id, upload_date) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $title, $description, $message, $pdf_path, $teacher_id, $upload_date);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Material uploaded successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    
    // Redirect back to dashboard
    header("Location: teacher-dashboard.php");
    exit;
}
?>