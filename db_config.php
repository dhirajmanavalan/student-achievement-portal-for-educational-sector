<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "events_db";
$port = "3306";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Your SQL query and other logic here...

?>
