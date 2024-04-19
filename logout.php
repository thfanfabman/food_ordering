<?php
// Start session to access session variables
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect user to the login/register page (index.php)
header('Location: index.php');
exit;
?>
