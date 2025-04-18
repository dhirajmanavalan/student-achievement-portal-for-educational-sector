<?php
session_start();
include 'db_config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if student_name column exists in users table
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Check password
        if (password_verify($password, $row['password'])) {
            // Store session variables
            $_SESSION['user_id'] = $row['id'];  // Using 'id' as user ID
            $_SESSION['username'] = $row['username'];  // Store username

            // Check if student_name column exists before using it
            if (isset($row['student_name'])) {
                $_SESSION['student_name'] = $row['student_name'];  // Store student_name if it exists
            } else {
                $_SESSION['student_name'] = $row['username']; // Fallback to username if no student_name column
            }

            // Redirect to user dashboard
            header("Location: indexUser.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.location.href='user_login.html';</script>";
        }
    } else {
        echo "<script>alert('User not found.'); window.location.href='user_login.html';</script>";
    }

    $stmt->close();
}
$conn->close();
?>
