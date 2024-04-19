<?php
session_start();

// Check if user is not logged in, redirect to index.php
if (!isset($_SESSION['email'])) {
    header('Location: index.php');
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

// Fetch user's information from the database based on session email
$email = $_SESSION['email'];
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
} else {
    die("User not found."); // Handle error if user not found
}

// Handle form submissions for updating user information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['updateName'])) {
        // Update user's name
        $name = $_POST['name'];
        $updateSql = "UPDATE users SET name = '$name' WHERE email = '$email'";
        if ($conn->query($updateSql) === TRUE) {
            $_SESSION['name'] = $name; // Update session name
            header('Location: edit_profile.php'); // Redirect to refresh page
            exit;
        } else {
            die("Error updating name: " . $conn->error);
        }
    }

    if (isset($_POST['updateEmail'])) {
        // Update user's email
        $newEmail = $_POST['email'];
        $updateSql = "UPDATE users SET email = '$newEmail' WHERE email = '$email'";
        if ($conn->query($updateSql) === TRUE) {
            $_SESSION['email'] = $newEmail; // Update session email
            header('Location: edit_profile.php'); // Redirect to refresh page
            exit;
        } else {
            die("Error updating email: " . $conn->error);
        }
    }

    if (isset($_POST['updatePassword'])) {
        // Update user's password
        $newPassword = $_POST['password'];
        $updateSql = "UPDATE users SET password = '$newPassword' WHERE email = '$email'";
        if ($conn->query($updateSql) === TRUE) {
            header('Location: edit_profile.php'); // Redirect to refresh page
            exit;
        } else {
            die("Error updating password: " . $conn->error);
        }
    }

    if (isset($_POST['exit'])) {
        // Redirect back to dashboard without updating
        header('Location: dashboard.php');
        exit;
    }

    if (isset($_POST['logout'])){
      header('Location: logout.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Custom CSS for form styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        .form-container {
            max-width: 400px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

        .exit-button {
            background-color: #f44336; /* Red color for exit button */
        }

        .exit-button:hover {
            background-color: #d32f2f; /* Darker red on hover */
        }

        .logout-button {
            background-color: #f44336; /* Red color for exit button */
        }

        .logout-button:hover {
            background-color: #d32f2f; /* Darker red on hover */
        }

        .current-credentials {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Profile</h2>

        <!-- Display current user information -->
        <div class="current-credentials">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($userData['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
        </div>

        <!-- Update Name Form -->
        <form action="edit_profile.php" method="POST">
            <label for="name">New Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter new name" required>
            <button type="submit" name="updateName">Update Name</button>
        </form>

        <!-- Update Email Form -->
        <form action="edit_profile.php" method="POST">
            <label for="email">New Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter new email" required>
            <button type="submit" name="updateEmail">Update Email</button>
        </form>

        <!-- Update Password Form -->
        <form action="edit_profile.php" method="POST">
            <label for="password">New Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter new password" required>
            <button type="submit" name="updatePassword">Update Password</button>
        </form>

        <!-- Exit Form -->
        <form action="edit_profile.php" method="POST">
            <button type="submit" name="exit" class="exit-button">Exit</button>
        </form>

        <form action="edit_profile.php" method="POST">
            <button type="submit" name="logout" class="logout-button">Logout</button>
        </form>
    </div>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
