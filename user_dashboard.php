<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: user_login.html");
    exit();
}

echo "<h2>Welcome, " . $_SESSION['username'] . "!</h2>";
echo "<a href='logout.php'>Logout</a>";
?>
