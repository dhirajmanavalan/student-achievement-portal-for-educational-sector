<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "events_db";
$port = 3336;

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];
    $rejection_reason = $_POST['rejection_reason'];

    // Update the application status
    if ($status == 'Rejected' && empty($rejection_reason)) {
        echo "<script>alert('Please provide a reason for rejection.');</script>";
    } else {
        $sql = "UPDATE applications SET status = ?, rejection_reason = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $rejection_reason, $application_id);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Status updated successfully!');</script>";
    }
}
?>
