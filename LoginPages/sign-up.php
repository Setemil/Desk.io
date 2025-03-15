<?php
include 'database.php';

$error_msg = '';
$success_msg = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['cpassword'];
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error_msg = "All fields are required, including role.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error_msg = "Username or email already exists.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into database with role
            $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($insert_stmt, "ssss", $username, $email, $hashed_password, $role);

            if (mysqli_stmt_execute($insert_stmt)) {
                $success_msg = "Account created successfully. You can now log in.";
            } else {
                $error_msg = "Something went wrong. Please try again.";
            }

            mysqli_stmt_close($insert_stmt);
        }

        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>


<!-- Signup Form HTML -->
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desk.io | Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/login-style.css">
    <style>
        .role-select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            background-color: white;
        }
        
        .role-select:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }
        
        .role-description {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
            margin-bottom: 15px;
            display: none;
        }
        
        .role-description.active {
            display: block;
        }
    </style>
</head>
<body>
<div class="main-content">
        <div class="header">
            <h2>Sign-up</h2>
            <a href="../index.html">Go Back</a>
        </div>
        
        <?php if (!empty($error_msg)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_msg)): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>
        
        <form action="sign-up.php" method="post">
            <div class="l-part">
                <label for="username">Username</label>
                <input type="text" name="username" placeholder="Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                
                <label for="email">Email</label>
                <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Password" required>
                
                <label for="cpassword">Confirm Password</label>
                <input type="password" name="cpassword" placeholder="Confirm Password" required>
                
                <label for="role">Select Your Role</label>
                <select id="role" name="role" class="role-select" required>
                    <option value="" disabled selected>-- Select your role --</option>
                    <option value="student">Student</option>
                    <option value="class_rep">Class Representative</option>
                    <option value="teacher">Teacher</option>
                </select>
                
                <div id="student-desc" class="role-description">Access course materials and submit assignments</div>
                <div id="class_rep-desc" class="role-description">Manage communications and assist teachers</div>
                <div id="teacher-desc" class="role-description">Create courses and manage classes</div>
                
                <button type="submit" name="sign-up" class="btn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </div>
        </form>
        
        <div class="footer">
            <p>Already have an account? <a href="log-in.php">Login</a></p>
        </div>
    </div>
    
    <script src="../JS/login-script.js"></script>
</body>
</html>
