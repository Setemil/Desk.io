<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
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
            height: 230px;
            width: 600px;
            padding: 25px;
            text-align: center;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #343a40;
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
            width: 100px;
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

    </style>
</head>
<body>
    <div class="container">
        <h1>Input OTP</h1>
        <form method="post" action="OTPVerify.php">
            <input type="number" name="OTP" id="OTP" placeholder="OTP" required>
            <input type="submit" class="btn" value="Submit" name="inputOTP">
        </form>
    </div>
</body>
</html>
