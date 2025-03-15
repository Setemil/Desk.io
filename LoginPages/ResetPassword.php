<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
@import url("https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap");

        body {  
            font-family: "Outfit", serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ffffff;
        }

        .container {
            background: #f9f9f9;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
            position: relative;            
            height: 300px;
            width: 600px;
            padding: 25px;
            text-align: center;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form input {
            width: 90%;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 15px;
        }

        form input[type="submit"] {
            background-color: #4f46e5;
            color: white;
            cursor: pointer;
            border: none;
            font-weight: bold;
            width: 200px;
            border-bottom: 20px;
        }

        form input[type="submit"]:hover {
            background-color: #818cf8;
            transition: 0.3s ease-in-out;
        }

        .container div {
            text-align: center;
            align-items: center;
        }


        .error {
            color: red;
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 15px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        <form method="post" action="PasswordReset.php" id="registerForm">
            <input type="password" name="password" id="password" placeholder="Password" maxlength= 16 required>
            <p id="passwordLengthError" class="error" style="display: none; color: red;">Password must be more than 8 characters</p>
            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
            <p id="error" class="error" style="color: red">Passwords do not match</p>
            <input type="submit" class="btn" value="Reset Password" name="Reset">
        </form>
    </div>

    <script>
        const registerForm = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const passwordLengthError = document.getElementById('passwordLengthError');
        const confirmPassword = document.getElementById('confirmPassword');
        const error = document.getElementById('error');

        registerForm.addEventListener('submit', function(event) {
            if (password.value !== confirmPassword.value) {
                event.preventDefault();
                error.style.display = 'block';
            } else {
                error.style.display = 'none';
            }

            if (password.value.length < 8){
                event.preventDefault();
                passwordLengthError.style.display = 'block';
            }else {
                passwordLengthError.style.display = 'none';
            }
        });
    </script>
</body>
</html>
