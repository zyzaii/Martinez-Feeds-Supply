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

// Check for session error message
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear after using
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Determine if the input is an email or username
    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM users WHERE email = ?";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
    }

    // Prepare and execute SQL query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize variables for user existence and password match
    $userExists = false;
    $passwordCorrect = false;

    // Check if user exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userExists = true; // User found
        // Verify password
        if (password_verify($password, $row['password'])) {
            $passwordCorrect = true; // Password is correct
            // Output JavaScript to redirect and close the window
            echo "<script>
                window.opener.location.href = 'index.html'; // Redirect the parent window
                window.close(); // Close the login window
            </script>";
            exit();
        }
    }

    // Set appropriate error messages
    if (!$userExists || !$passwordCorrect) {
        $_SESSION['error_message'] = "Username or password does not match. Please try again."; // Store in session
        header("Location: log.php"); // Redirect back to the login page
        exit();
    }

    // Close statement and connection
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 400px;
            margin: 100px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #333;
        }
        input[type="text"], input[type="password"] {
            width: calc(100% - 10px);
            max-width: 350px;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input.error-input {
            border-color: red;
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
        .create-account, .browse-account {
            text-align: center;
            margin-top: 20px;
        }
        .create-account a, .browse-account a {
            color: #4CAF50;
            text-decoration: none;
        }
        .error-message {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Login</h2>
        <form action="" method="POST" id="loginForm">
            <label for="login">Email or Username:</label>
            <input type="text" name="login" required autocomplete="username">

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" class="<?= !empty($error_message) ? 'error-input' : '' ?>" required autocomplete="current-password">

            <div class="show-password">
                <input type="checkbox" id="togglePassword" aria-label="Show Password"> Show Password
            </div>

            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?= $error_message; ?></p>
            <?php endif; ?>

            <button type="submit">Login</button>
        </form>

        <div class="create-account">
            <p>Don't have an account? <a href="reg.php">Create one</a></p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('change', function () {
            const type = this.checked ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });
    </script>

</body>
</html>
