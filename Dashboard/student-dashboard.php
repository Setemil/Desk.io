<?php
session_start();

// Check if user is logged in and is a student
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Database connection
require_once('../LoginPages/database.php');

// Get the student ID from session
$student_id = $_SESSION['user_id'];

// Fetch all materials for students to view
$sql = "SELECT m.*, u.username as teacher_name 
        FROM materials m 
        JOIN users u ON m.teacher_id = u.user_id 
        ORDER BY m.upload_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desk.io | Student Dashboard</title>
    <link rel="stylesheet" href="../CSS/student-style.css">
    <style>
        .navbar {
    display: flex;
    justify-content: space-around;
    align-items: center;
    background-color: #3a86ff;
    color: white;
    padding: 0 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
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
        // Display success or error messages if any
        if(isset($_SESSION['success'])) {
            echo '<div class="alert success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        if(isset($_SESSION['error'])) {
            echo '<div class="alert error">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        
        <h1>Posted Materials</h1>
        
        <div class="filter-section">
            <form method="GET" action="" class="filter-form">
                <input type="text" name="search" placeholder="Search materials..." 
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
                <?php if(isset($_GET['search'])): ?>
                    <a href="student-dashboard.php" class="clear-filter">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="materials-container">
            <?php
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // If search parameter exists, filter results
                    if(isset($_GET['search']) && !empty($_GET['search'])) {
                        $search = strtolower($_GET['search']);
                        $title = strtolower($row['title']);
                        $description = strtolower($row['description']);
                        $teacher = strtolower($row['teacher_name']);
                        
                        if(strpos($title, $search) === false && 
                           strpos($description, $search) === false &&
                           strpos($teacher, $search) === false) {
                            continue; // Skip this iteration if not matching search
                        }
                    }
                    ?>
                    <div class="material-card">
                        <div class="material-header">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <span class="material-date">
                                Posted on <?php echo date('F j, Y', strtotime($row['upload_date'])); ?>
                                by <?php echo htmlspecialchars($row['teacher_name']); ?>
                            </span>
                        </div>
                        
                        <div class="material-content">
                            <p class="material-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                            
                            <?php if(!empty($row['message'])): ?>
                            <div class="material-message">
                                <h4>Teacher's Message:</h4>
                                <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($row['pdf_path'])): ?>
                            <div class="material-actions">
                                <a href="<?php echo htmlspecialchars($row['pdf_path']); ?>" target="_blank" class="btn-view">
                                    <span class="icon">👁️</span> View PDF
                                </a>
                                <a href="download.php?file=<?php echo htmlspecialchars($row['pdf_path']); ?>" class="btn-download">
                                    <span class="icon">⬇️</span> Download
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-materials">No materials available at this time.</p>';
            }
            ?>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Desk.io - All Rights Reserved</p>
    </footer>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>