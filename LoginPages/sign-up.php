<?php
require_once 'database.php';

// Initialize variables
$error_msg = "";
$success_msg = "";

// Process sign-up form submission
if (isset($_POST['sign-up'])) {
    // Get form data and sanitize
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $role = $_POST['role']; // Get selected role
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($cpassword) || empty($role)) {
        $error_msg = "All fields are required";
    } 
    // Validate username (alphanumeric, 3-20 chars)
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error_msg = "Username must be 3-20 characters and contain only letters, numbers, and underscores";
    }
    // Validate email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address";
    }
    // Check password length
    elseif (strlen($password) < 8) {
        $error_msg = "Password must be at least 8 characters long";
    }
    // Check if passwords match
    elseif ($password !== $cpassword) {
        $error_msg = "Passwords do not match";
    }
    // Validate role
    elseif (!in_array($role, ['student', 'teacher', 'class_rep'])) {
        $error_msg = "Invalid role selected";
    }
    else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT username FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $error_msg = "Username already exists. Please choose another one.";
            } else {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->rowCount() > 0) {
                    $error_msg = "Email already exists. Please use another email or login.";
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user with role
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hashed_password, $role]);
                    
                    if ($stmt->rowCount() > 0) {
                        $success_msg = "Registration successful! You can now login.";
                        
                        // Auto login after registration (optional)
                        $user_id = $pdo->lastInsertId();
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $role;
                        
                        header("Location: log-in.php");
                        exit;
                    } else {
                        $error_msg = "Registration failed. Please try again.";
                    }
                }
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