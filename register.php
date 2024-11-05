<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";  // Default username in XAMPP
$password = "";      // Default password is blank in XAMPP
$dbname = "agricart"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging line
    echo "Form submitted.<br>";

    // Get form data
    if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Output the received data for debugging
        echo "Username: $username <br>";
        echo "Email: $email <br>";
        echo "Password: $password <br>"; // Note: Do NOT display passwords in a real application!

        // Basic validation
        if (empty($username) || empty($email) || empty($password)) {
            die("All fields are required.");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("Invalid email format.");
        }

        // Check if username or email already exists
        $sqlCheck = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("ss", $username, $email);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();

        if ($result->num_rows > 0) {
            die("Username or email already exists.");
        }

        $stmtCheck->close();

        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL statement
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Check if statement preparation was successful
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sss", $username, $email, $hashed_password);

        // Execute the query
        if ($stmt->execute()) {
            echo "Account created successfully!";
            // Redirect to login page after registration
            header("Location: login.php");
            exit();
        } else {
            die("Error executing query: " . $stmt->error); // Detailed error message
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    } else {
        die("Form fields are not set."); // This indicates that POST data is not being sent
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            transition: transform 0.5s ease, opacity 0.5s ease; /* Added transition */
        }
        .fade-out {
            transform: translateY(-20px); /* Move up slightly */
            opacity: 0; /* Fade out */
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
        input[type="text"], input[type="email"], input[type="password"] {
            width: calc(100% - 10px);
            max-width: 350px;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
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
        .footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container" id="container">
        <h2>Create an Account</h2>
        <form action="register.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Register</button>
        </form>
        <div class="footer">
            <p>Already have an account? <a href="log.php" id="loginLink">Login</a></p>
        </div>
    </div>

    <script>

    </script>
</body>
</html>
