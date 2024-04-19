<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['email'])) {
    header('Location: dashboard.php');
    exit;
}

// Database connection settings
$servername = "localhost";
$username = "root"; // Default username for XAMPP MySQL
$password = ""; // Leave password blank if not set during XAMPP setup
$dbname = "food_ordering"; // Database name (change to 'food_ordering')

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for form inputs
$email = '';
$password = '';
$name = ''; // New variable for user's name
$action = '';
$registrationSuccess = false;

// Process form submission (login or register)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'login') {
        // Login form submitted
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Validate login credentials
        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Login successful, retrieve user's data
            $user = $result->fetch_assoc();
            $_SESSION['email'] = $email; 
            $_SESSION['name'] = $user['name'];
            header('Location: dashboard.php');
            exit;
        } else {
            // Login failed, display error message
            $loginError = "Invalid email or password.";
        }
    } elseif ($action === 'register') {
        // Registration form submitted
        $email = $_POST['email'];
        $password = $_POST['password'];
        $name = $_POST['name']; // Retrieve user's name from form

        // Check if email already exists
        $checkSql = "SELECT * FROM users WHERE email = '$email'";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            // Email already exists, display error message
            $registerError = "Email already registered. Please use a different email.";
        } else {
            // Insert new user into database with name, email, and password
            $insertSql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
            if ($conn->query($insertSql) === TRUE) {
                // Registration successful
                $registrationSuccess = true;
            } else {
                // Registration failed, display error message
                $registerError = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login or Register</title>
    <style>
        /* Styling for the login/register container */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #50C878;
        }

        .login-register-container {
            text-align: center;
            padding: 40px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Styling for form elements */
        form {
            margin-top: 20px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="login-register-container">
        <h2>Welcome!</h2>

        <div id="login-register-buttons">
            <button onclick="showLoginForm()">Login</button>
            <button onclick="showRegisterForm()">Register</button>
        </div>

        <?php if ($registrationSuccess) : ?>
            <div class="success-message">Registration successful! You can now login.</div>
        <?php endif; ?>

        <div id="login-form" style="display: none;">
            <h3>Login</h3>
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>

        <div id="register-form" style="display: none;">
            <h3>Register</h3>
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="register">
                <input type="text" name="name" placeholder="Name" required> <!-- New field for user's name -->
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Register</button>
            </form>
        </div>

        <?php if (isset($loginError)) : ?>
            <div class="error-message"><?php echo $loginError; ?></div>
        <?php endif; ?>

        <?php if (isset($registerError)) : ?>
            <div class="error-message"><?php echo $registerError; ?></div>
        <?php endif; ?>
    </div>

    <script>
        function showLoginForm() {
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('register-form').style.display = 'none';
        }

        function showRegisterForm() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('register-form').style.display = 'block';
        }
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
