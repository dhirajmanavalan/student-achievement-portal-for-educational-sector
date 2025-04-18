<?php
include 'db_config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


    $check_sql = "SELECT COUNT(*) FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $username_count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($username_count > 0) {

        echo "Username already taken. Please choose a different one.";
    } else {

        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Registration successful! <a href='Student.html'>Login Here</a>";
        } else {
            echo "Error: " . mysqli_error($conn);  
        }

        mysqli_stmt_close($stmt);
    }
}
?>
