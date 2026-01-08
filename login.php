<?php
include './includes/db_connection.php';
session_start();

$error = '';


if (isset($_SESSION['registration_message'])) {
    $message = $_SESSION['registration_message'];
    unset($_SESSION['registration_message']);
} else {
    $message = '';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare(query: $sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        
        if (password_verify($password, $row['password'])) {
            
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that username.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Registration</title>
    <style>
        @import "compass/css3";

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            width: 100%;
            background: #95a5a6;
            background-image: url(http://subtlepatterns2015.subtlepatterns.netdna-cdn.com/patterns/dark_embroidery.png);
            font-family: 'Helvetica Neue', Arial, Sans-Serif;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
        }

        .login-wrap {
            position: relative;
            background: #ecf0f1;
            width: 350px;
            border-radius: 5px;
            box-shadow: 3px 3px 10px #333;
            padding: 15px;
        }

        h2 {
            text-align: center;
            font-weight: 200;
            font-size: 2em;
            margin-top: 10px;
            color: #34495e;
        }

        .form {
            padding-top: 20px;
        }

        input[type="text"], input[type="password"], button {
            width: 80%;
            margin-left: 10%;
            margin-bottom: 25px;
            height: 40px;
            border-radius: 5px;
            outline: 0;
            -moz-outline-style: none;
        }

        input[type="text"], input[type="password"] {
            border: 1px solid #bbb;
            padding: 0 0 0 10px;
            font-size: 14px;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border: 1px solid #3498db;
        }

        input:invalid {
            border-color: #e74c3c;
        }

        input:valid {
            border-color: #27ae60;
        }

        a {
            text-align: center;
            font-size: 10px;
            color: #3498db;
        }

        button {
            background: #e74c3c;
            border: none;
            color: white;
            font-size: 18px;
            font-weight: 200;
            cursor: pointer;
            transition: box-shadow .4s ease;
        }

        button:hover {
            box-shadow: 1px 1px 5px #555;
        }

        button:active {
            box-shadow: 1px 1px 7px #222;
        }

        .login-wrap:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: -webkit-linear-gradient(left, 
                #27ae60 0%, #27ae60 20%, 
                #8e44ad 20%, #8e44ad 40%,
                #3498db 40%, #3498db 60%,
                #e74c3c 60%, #e74c3c 80%,
                #f1c40f 80%, #f1c40f 100%
            );
            background: -moz-linear-gradient(left, 
                #27ae60 0%, #27ae60 20%, 
                #8e44ad 20%, #8e44ad 40%,
                #3498db 40%, #3498db 60%,
                #e74c3c 60%, #e74c3c 80%,
                #f1c40f 80%, #f1c40f 100%
            );
            height: 5px;
            border-radius: 5px 5px 0 0;
        }

        
        .alert-success {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }

        .alert-success p {
            margin: 0;
        }

       
    </style>
</head>
<body>

    <div class="login-wrap">
        <h2>Login</h2>

        <?php if ($message): ?>
            <div class="alert-success" id="success-message">
                <p><?php echo $message; ?></p>
            </div>
            <script>
                setTimeout(function() {
                    document.getElementById('success-message').style.display = 'none';
                }, 4000); 
            </script>
        <?php endif; ?>
        
        <div class="form">
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit">Sign in</button>
                <a href="register.php"> <p>Don't have an account? Register</p></a>
            </form>
        </div>
    </div>

</body>
</html>
