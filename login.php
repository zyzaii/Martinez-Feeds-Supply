<?php
session_start(); // Start the session

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agricart";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize an error message variable
$error_message = "";

// Check if the registration form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Get the confirm password

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: login.php"); // Redirect back to the login page
        exit();
    }

    // Check if email or username is already taken
    $checkQuery = "SELECT * FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email or username already exists
        $_SESSION['error_message'] = "Username or email is already taken.";
        header("Location: login.php"); // Redirect back to the login page
        exit();
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $insertQuery = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            // Redirect to the login page after successful registration
            header("Location: login.php");
            exit();
        } else {
            // If there was an error inserting into the database
            $_SESSION['error_message'] = "Error registering account. Please try again.";
            header("Location: login.php");
            exit();
        }
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

// Check if the login form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Check if login is an email or username
    $checkQuery = "SELECT * FROM users WHERE (email = ? OR username = ?)";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Successful login, store username and email in session
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Redirect to index.html
            header("Location: index.html");
            exit();
        } else {
            // Incorrect password
            $_SESSION['error_message'] = "Invalid password.";
            header("Location: login.php");
            exit();
        }
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            filter: brightness(1);
            opacity: 1;
        }

        .fade-container {
            position: relative;
            height: 100%;
        }

        .container,
        .create-account-container
         {
            position: absolute;
            top: 50%;
            left: 20%;
            transform: translate(-50%, -50%);
            width: 100%;
            max-width: 400px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            border: 2px solid #4CAF50;
            z-index: 1;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .overviewcontainer {
            flex: 1;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    position: absolute; /* Changed from relative to absolute */
    top: 50%; /* Positioning from the top */
    left: 0; /* Aligning to the left */
    transform: translateY(-50%); /* Centering vertically */
    z-index: 1;
    opacity: 1;
        }

        .container,
        .create-account-container {
            display: none;
        }

        h2, h1 {
            text-align: center;
            color: #4CAF50;
            font-size: xx-large;
        }

        p {
            margin: 10px 0;
            text-align: justify;
             margin-bottom: 25px;
            line-height: 1.6;
            color: black;   
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .create-account a, .browse-account a {
            color: #4CAF50;
            text-decoration: none;
        }

        .create-account a:hover, .browse-account a:hover {
            text-decoration: underline;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            color: #333;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: calc(100% - 10px);
            max-width: 350px;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .show-password {
            margin: 5px 0;
        }
        #getStartedBtn{
            padding: 10px 20px; /* Adjusted padding for a smaller button */
    font-size: 18px;
    color: white;
    background-color: #4CAF50;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    width: auto; /* Set to auto to fit the content */
    
    left: 20%;
        }
        
    </style>
</head>
<body>
<video autoplay muted loop id="backgroundVideo">
            <source src="vid2.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    <div class="fade-container">
       

        <div class="overviewcontainer" id="overviewContainer">
            <h1 style="font-size: xxx-large;">Welcome to AgriCart</h1>
            <p> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Your one-stop shop for all your animal care needs. We offer a wide range of high-quality animal<br>
             feeds, medicines, and supplies to keep your pets and livestock healthy and happy. With our<br>
              easy-to-navigate online store, you can conveniently browse and purchase everything<br>
               from nutritious feeds to essential medications. Trust us to provide the best products<br>
                for your beloved animals, delivered right to your doorstep. Shop with us today for<br>
                 a happier, healthier tomorrow!</p> <br>
            <button id="getStartedBtn">Let's get started</button>
        </div>

        <div class="container" id="loginContainer">
            <h2>Login</h2>
            <form action="" method="POST" id="loginForm">
                <label for="login">Email or Username:</label>
                <input type="text" name="login" required autocomplete="username">

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required autocomplete="current-password">

                <label class="show-password">
                    <input type="checkbox" id="showPassword"> Show Password
                </label>

                <button type="submit">Login</button>
            </form>
            <div class="create-account">
                <p>Don't have an account? <a href="#" id="showCreateAccount">Create one</a></p>
            </div>
            <div class="browse-account">
                <p><a href="index.html?user=no_account">Browse without account</a></p>
            </div>
        </div>

        <div class="create-account-container" id="createAccountContainer">
    <h2>Create an Account</h2>
    <form action="login.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        
        <label for="password">Password:</label>
        <input type="password" name="password" id="createPassword" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirmPassword" required>

        <label class="show-password">
            <input type="checkbox" id="showCreatePassword"> Show Password
        </label>
        
        <button type="submit">Register</button>
    </form>
    <div class="create-account">
        <p>Already have an account? <a href="#" id="showLogin">Login</a></p>
    </div>
</div>

        
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const overviewContainer = document.getElementById("overviewContainer");
            const loginContainer = document.getElementById("loginContainer");
            const createAccountContainer = document.getElementById("createAccountContainer");
            const getStartedBtn = document.getElementById("getStartedBtn");
            const showCreateAccount = document.getElementById("showCreateAccount");
            const showLogin = document.getElementById("showLogin");

            const showPassword = document.getElementById("showPassword");
            const passwordInput = document.getElementById("password");

            const showCreatePassword = document.getElementById("showCreatePassword");
            const createPasswordInput = document.getElementById("createPassword");

            // Show/hide password in login
            showPassword.addEventListener("change", function() {
                passwordInput.type = showPassword.checked ? "text" : "password";
            });

            // Show/hide password in create account
            showCreatePassword.addEventListener("change", function() {
                createPasswordInput.type = showCreatePassword.checked ? "text" : "password";
            });

            // Function to transition from overview to login
            function showLoginContainer() {
                overviewContainer.style.opacity = 0;
                setTimeout(() => {
                    overviewContainer.style.display = "none";
                    loginContainer.style.display = "block";
                    setTimeout(() => {
                        loginContainer.style.opacity = 1;
                    }, 20);
                }, 500);
            }

            // Event listener for "Let's get started" button
            getStartedBtn.addEventListener("click", function() {
                showLoginContainer();
            });

            // Function to fade out login and show create account
            function toggleContainers() {
                loginContainer.style.opacity = 0;
                setTimeout(() => {
                    loginContainer.style.display = "none";
                    createAccountContainer.style.display = "block";
                    setTimeout(() => {
                        createAccountContainer.style.opacity = 1;
                    }, 20);
                }, 500);
            }

            // Function to show login container
            function toggleLogin() {
                createAccountContainer.style.opacity = 0;
                setTimeout(() => {
                    createAccountContainer.style.display = "none";
                    loginContainer.style.display = "block";
                    setTimeout(() => {
                        loginContainer.style.opacity = 1;
                    }, 50);
                }, 500);
            }

            showCreateAccount.addEventListener("click", function(e) {
                e.preventDefault();
                toggleContainers();
            });

            showLogin.addEventListener("click", function(e) {
                e.preventDefault();
                toggleLogin();
            });
        });
    </script>

</body>
</html>
