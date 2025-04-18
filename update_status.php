<?php
// update_status.php - Admin Status Update
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE submissions SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) {
        echo "Success: Status updated.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
