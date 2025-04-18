<?php
// upload.php - Handle File Upload and Save Data
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentName = $_POST['studentName'];
    $studentID = $_POST['studentID'];
    $eventName = $_POST['eventName'];
    $eventType = $_POST['eventType'];
    $eventDate = $_POST['eventDate'];
    $abstract = $_POST['abstract'];
    $status = "Pending";

    $targetDir = "uploads/";
    $fileName = basename($_FILES["certificate"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    $allowedTypes = array('pdf', 'jpg', 'png');
    if (in_array($fileType, $allowedTypes) && $_FILES["certificate"]["size"] <= 5242880) {
        if (move_uploaded_file($_FILES["certificate"]["tmp_name"], $targetFilePath)) {
            $stmt = $conn->prepare("INSERT INTO submissions (studentName, studentID, eventName, eventType, eventDate, abstract, certificate, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $studentName, $studentID, $eventName, $eventType, $eventDate, $abstract, $fileName, $status);
            if ($stmt->execute()) {
                echo "Success: Submission uploaded successfully.";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error: Failed to upload file.";
        }
    } else {
        echo "Error: Invalid file type or size exceeds 5MB.";
    }
}
?>