<?php
// log-in.php - User login processing with role-based redirection

// Include database connection
require_once 'database.php';

// Initialize variables
$error_msg = "";

// Process login form submission
if (isset($_POST['log-in'])) {
    // Get form data and sanitize
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error_msg = "Both username/email and password are required";
    } else {
        try {
            // Check if input is username or email and retrieve role
            $stmt = $pdo->prepare("SELECT user_id, username, email, password, role FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_regenerate_id(true); // Prevent session fixation attacks
                
                // Store user data in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Update last login time
                $update_stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
                $update_stmt->execute([$user['user_id']]);
                
                // Insert session info for additional security (optional)
                $session_id = session_id();
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $expires_at = date('Y-m-d H:i:s', time() + 86400); // 24 hours from now
                
                $session_stmt = $pdo->prepare("INSERT INTO sessions (session_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)");
                $session_stmt->execute([$session_id, $user['user_id'], $ip_address, $user_agent]);
                
                // Redirect based on role
                switch ($user['role']) {
                    case 'teacher':
                        header("Location: ../Dashboard/teacher-dashboard.php");
                        break;
                    case 'class_rep':
                        header("Location: ../Dashboard/class-rep-dashboard.php");
                        break;
                    case 'student':
                        header("Location: ../Dashboard/student-dashboard.php");
                        break;
                    default:
                        header("Location: dashboard.php"); // Fallback
                }
                exit;
            } else {
                $error_msg = "Invalid username/email or password";
            }
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desk.io | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/login-style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h2>Login</h2>
            <a href="../index.html">Go Back</a>
        </div>
        
        <?php if (!empty($error_msg)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>
        
        <form action="log-in.php" method="post">
            <div class="l-part">
                <label for="username">Username or Email</label>
                <input type="text" name="username" placeholder="Username or Email" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Password" required>
                
                <button type="submit" name="log-in" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Log In
                </button>
            </div>
        </form>
        
        <div class="footer">
            <p>Don't have an account? <a href="sign-up.php">Sign Up</a></p>
        </div>
    </div>
</body>
</html>