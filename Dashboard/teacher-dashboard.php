<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desk.io | Teachers Dashboard</title>
    <link rel="stylesheet" href="../CSS/teacher-style.css">
    <style>
        .material-actions a{
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
        .btn-delete{
            background-color: red;
        }
        .btn-delete:hover {
            background-color:rgb(193, 3, 3);
        }
        .btn-edit
        {
            background-color: blue;
        }
        .btn-edit:hover {
            background-color:rgb(3, 3, 193);
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo">Desk.io</div>
        <a href="logout.php" class="logout">Logout</a>
    </header>
    
    <main class="container">
        <h2>Welcome, Teacher</h2>
        <?php
        // Display success or error messages if any
        session_start();
        if(isset($_SESSION['success'])) {
            echo '<div class="alert success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        if(isset($_SESSION['error'])) {
            echo '<div class="alert error">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        
        <h2>Upload Material or Send Message</h2>
        <form action="upload-process.php" method="POST" enctype="multipart/form-data" class="upload-form">
            <label for="title">Title:</label>
            <input type="text" name="title" required>

            <label for="description">Description:</label>
            <textarea name="description" rows="4" required></textarea>

            <label for="message">Optional Message (Text only):</label>
            <textarea name="message" rows="3"></textarea>

            <label for="pdf">Upload PDF (optional):</label>
            <input type="file" name="pdf" accept="application/pdf">

            <button type="submit" name="submit">Send</button>
        </form>
        
        <h2 class="section-title">Your Uploaded Materials</h2>
        <div class="materials-container">
            <?php
            include('../LoginPages/database.php');
            // Get current teacher's ID from session
            $teacher_id = $_SESSION['user_id']; // Assuming teacher ID is stored in session
            
            // Query to get materials uploaded by this teacher
            $sql = "SELECT * FROM materials WHERE teacher_id = ? ORDER BY upload_date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    ?>
                    <div class="material-card">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="material-date">Uploaded on <?php echo date('F j, Y', strtotime($row['upload_date'])); ?></p>
                        <p class="material-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                        
                        <?php if(!empty($row['message'])): ?>
                        <div class="material-message">
                            <strong>Message:</strong>
                            <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($row['pdf_path'])): ?>
                        <div class="material-pdf">
                            <a href="<?php echo htmlspecialchars($row['pdf_path']); ?>" target="_blank" class="pdf-link">
                                <span class="pdf-icon">📄</span> View PDF
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="material-actions">
                            <a href="edit-material.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                            <a href="delete-material.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this material?')">Delete</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-materials">You haven\'t uploaded any materials yet.</p>';
            }
            
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </main>
</body>
</html>