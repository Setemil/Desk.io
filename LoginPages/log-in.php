<?php
session_start(); 

require_once 'database.php';
$error_msg = "";

if (isset($_POST['log-in'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $error_msg = "Both username/email and password are required";
    } else {
        // Check if input is username or email and retrieve user
        $stmt = mysqli_prepare($conn, "SELECT user_id, username, email, password, role FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                // Password is correct
                session_regenerate_id(true); // Prevent session fixation attacks

                // Store user data in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Update last login time
                $update_stmt = mysqli_prepare($conn, "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
                mysqli_stmt_bind_param($update_stmt, "i", $user['user_id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);

                // Insert session info for additional security (optional)
                $session_id = session_id();
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];

                $session_stmt = mysqli_prepare($conn, "INSERT INTO sessions (session_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($session_stmt, "siss", $session_id, $user['user_id'], $ip_address, $user_agent);
                mysqli_stmt_execute($session_stmt);
                mysqli_stmt_close($session_stmt);

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
                        header("Location: dashboard.php");
                }
                exit;
            } else {
                $error_msg = "Invalid username/email or password";
            }
        } else {
            $error_msg = "Invalid username/email or password";
        }

        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
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
            <br>
            <a href="OTPRequest.php">Forgot Passsword?</a>
        </form>
        
        <div class="footer">
            <p>Don't have an account? <a href="sign-up.php">Sign Up</a></p>
        </div>
    </div>
</body>
</html>