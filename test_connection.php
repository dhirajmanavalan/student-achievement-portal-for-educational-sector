<?php
// Include the database config file
include('db_config.php');

// Create a connection using the settings from db_config.php
$conn = new mysqli($db_config['host'], $db_config['user'], $db_config['password'], $db_config['database']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully";
?>
