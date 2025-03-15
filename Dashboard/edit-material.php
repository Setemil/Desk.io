<?php
session_start();

// Check if user is logged in and is a teacher
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
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

// Check if this material belongs to the current teacher
$sql = "SELECT * FROM materials WHERE id = ? AND teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $material_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    $_SESSION['error'] = "You don't have permission to edit this material or it doesn't exist.";
    header('Location: teacher-dashboard.php');
    exit();
}

$material = $result->fetch_assoc();

// Process form submission
if(isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $message = trim($_POST['message']);
    $current_pdf = $material['pdf_path'];
    $pdf_path = $current_pdf; // Default to current path
    
    // Validate form inputs
    if(empty($title) || empty($description)) {
        $_SESSION['error'] = "Title and description are required.";
    } else {
        // Check if a new PDF is uploaded
        if(isset($_FILES['pdf']) && $_FILES['pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle file upload
            $target_dir = "uploads/";
            $file_name = time() . '_' . basename($_FILES["pdf"]["name"]);
            $target_file = $target_dir . $file_name;
            
            // Check if file is actually a PDF
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if($file_type != "pdf") {
                $_SESSION['error'] = "Sorry, only PDF files are allowed.";
            } else {
                // Try to upload the file
                if(move_uploaded_file($_FILES["pdf"]["tmp_name"], $target_file)) {
                    $pdf_path = $target_file;
                    
                    // Delete the old file if it exists and is different
                    if(!empty($current_pdf) && $current_pdf !== $pdf_path && file_exists($current_pdf)) {
                        unlink($current_pdf);
                    }
                } else {
                    $_SESSION['error'] = "Sorry, there was an error uploading your file.";
                }
            }
        }
        
        // If no errors, update the database
        if(!isset($_SESSION['error'])) {
            $sql = "UPDATE materials SET title = ?, description = ?, message = ?, pdf_path = ? WHERE id = ? AND teacher_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii", $title, $description, $message, $pdf_path, $material_id, $teacher_id);
            
            if($stmt->execute()) {
                $_SESSION['success'] = "Material successfully updated.";
                header('Location: teacher-dashboard.php');
                exit();
            } else {
                $_SESSION['error'] = "Database error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desk.io | Edit Material</title>
    <link rel="stylesheet" href="../CSS/teacher-style.css">
    <style>
        .btn-cancel {
            background-color: red;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            margin-top: 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s;
            align-self: flex-start;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background-color:rgb(193, 3, 3);
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo">Desk.io</div>
        <a href="logout.php" class="logout">Logout</a>
    </header>
    
    <main class="container">
        <?php
        // Display error messages if any
        if(isset($_SESSION['error'])) {
            echo '<div class="alert error">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        
        <h2>Edit Material</h2>
        <form action="edit-material.php?id=<?php echo $material_id; ?>" method="POST" enctype="multipart/form-data" class="upload-form">
            <label for="title">Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($material['title']); ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" rows="4" required><?php echo htmlspecialchars($material['description']); ?></textarea>

            <label for="message">Optional Message (Text only):</label>
            <textarea name="message" rows="3"><?php echo htmlspecialchars($material['message']); ?></textarea>

            <label for="pdf">Upload New PDF (optional):</label>
            <input type="file" name="pdf" accept="application/pdf">
            
            <?php if(!empty($material['pdf_path'])): ?>
            <div class="current-file">
                <p>Current PDF: <a href="<?php echo $material['pdf_path']; ?>" target="_blank"><?php echo basename($material['pdf_path']); ?></a></p>
                <small>Upload a new PDF to replace the current one.</small>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <a href="teacher-dashboard.php" class="btn-cancel">Cancel</a>
                <button type="submit" name="submit" class="btn-submit">Update Material</button>
            </div>
        </form>
    </main>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>