<?php
include './includes/db_connection.php';
session_start(); 

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $username = sanitize_input($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = sanitize_input($_POST['email']);

   
    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $email);

   
    if ($stmt->execute()) {
        
        $_SESSION['registration_message'] = 'Registration successful! You will be redirected to the login page shortly.';
        
        header("Location: login.php");
        exit(); 
    } else {
        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($stmt->error) . '</div>';
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
    <title>Register</title>
    <style>
        @import "compass/css3";

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

html {
    background: #95a5a6;
    background-image: url(http://subtlepatterns2015.subtlepatterns.netdna-cdn.com/patterns/dark_embroidery.png);
    font-family: 'Helvetica Neue', Arial, sans-serif;
    height: 100%; 
}

body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; 
    margin: 0;
}

.register-wrap {
    position: relative;
    background: #ecf0f1;
    width: 100%;
    max-width: 350px;
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

input[type="text"],
input[type="password"],
input[type="email"],
input[type="number"],
button {
    width: 80%;
    margin-left: 10%;
    margin-bottom: 25px;
    height: 40px;
    border-radius: 5px;
    outline: 0;
    -moz-outline-style: none;
    transition: border-color 0.3s ease;
}

input[type="text"],
input[type="password"],
input[type="email"],
input[type="number"] {
    border: 1px solid #bbb;
    padding: 0 0 0 10px;
    font-size: 14px;
}

input[type="text"]:focus,
input[type="password"]:focus,
input[type="email"]:focus {
    border: 1px solid #3498db;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.7);
}

a {
    text-align: center;
    font-size: 10px;
    color: #3498db;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

button {
    background: #27ae60;
    border: none;
    color: white;
    font-size: 18px;
    font-weight: 200;
    cursor: pointer;
    transition: box-shadow .4s ease, background-color 0.3s ease;
}

button:hover {
    box-shadow: 1px 1px 5px #555;
    background-color: #2ecc71;
}

button:active {
    box-shadow: 1px 1px 7px #222;
}

.register-wrap:after {
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
            #f1c40f 80%, #f1c40f 100%);
    background: -moz-linear-gradient(left,
            #27ae60 0%, #27ae60 20%,
            #8e44ad 20%, #8e44ad 40%,
            #3498db 40%, #3498db 60%,
            #e74c3c 60%, #e74c3c 80%,
            #f1c40f 80%, #f1c40f 100%);
    height: 5px;
    border-radius: 5px 5px 0 0;
}



    </style>
</head>
<body>

    <div class="register-wrap">
        <h2>Register</h2>
        
        <div class="form">
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username" required />
                <input type="password" name="password" placeholder="Password" required />
                <input type="email" name="email" placeholder="Email" required />
                <button type="submit">Register</button>
                <a href="login.php"><p>Already have an account? Login here</p></a>
            </form>

        </div>
    </div>

</body>
</html>
