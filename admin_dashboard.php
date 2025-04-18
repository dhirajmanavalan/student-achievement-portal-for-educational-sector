<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Plain text password comparison
        if ($password == $row['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $row['username'];
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.location.href='admin_login.html';</script>";
        }
    } else {
        echo "<script>alert('Admin not found.'); window.location.href='admin_login.html';</script>";
    }
    
    $stmt->close();
}
$conn->close();
?>
