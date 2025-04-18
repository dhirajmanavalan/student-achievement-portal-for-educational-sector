<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start(); // Start the session to access session variables

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "events_db";
$port = 3306; // Change this to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch event details from database based on the event ID
// Fetch event details from the database using event_id
// Fetch event details from the database using id (not event_id)
if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']); // Ensure it's an integer for security

    $sql = "SELECT title FROM student_events WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $event_title = $row['title'];
    } else {
        $event_title = "Unknown Event";
    }
    $stmt->close();
} else {
    die("Event ID not provided.");
}



// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["student_name"];
    $student_id = $_POST["student_id"];
    $event_name = $_POST["event_name"];
    $event_type = $_POST["event_type"];
    $event_date = $_POST["event_date"];
    $abstract = $_POST["abstract"];
    $status = "Pending";

    // File upload handling
    $target_dir = "uploads/";
    $certificate = basename($_FILES["certificate"]["name"]);
    $target_file = $target_dir . $certificate;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (!in_array($file_type, ["jpg", "png", "pdf"])) {
        echo "<script>alert('Invalid file type! Only JPG, PNG, and PDF allowed.');</script>";
    } elseif ($_FILES["certificate"]["size"] > 5 * 1024 * 1024) {
        echo "<script>alert('File size exceeds 5MB!');</script>";
    } else {
        move_uploaded_file($_FILES["certificate"]["tmp_name"], $target_file);

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO applications (student_name, student_id, event_name, event_type, event_date, certificate, abstract, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $name, $student_id, $event_name, $event_type, $event_date, $certificate, $abstract, $status);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Application submitted successfully!');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>📝 Apply for <?php echo htmlspecialchars($event_title); ?></h2>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="event_name" value="<?php echo htmlspecialchars($event_title); ?>">

            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name:</label>
                <input type="text" class="form-control" id="student_name" name="student_name" 
                    value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID / Roll Number:</label>
                <input type="text" class="form-control" id="student_id" name="student_id" 
                    value="<?php echo isset($_SESSION['student_id']) ? htmlspecialchars($_SESSION['student_id']) : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="event_type" class="form-label">Event Type:</label>
                <input type="text" class="form-control" id="event_type" name="event_type" required>
            </div>

            <div class="mb-3">
                <label for="event_date" class="form-label">Date of Event:</label>
                <input type="date" class="form-control" id="event_date" name="event_date" required>
            </div>

            <div class="mb-3">
                <label for="abstract" class="form-label">Abstract:</label>
                <textarea class="form-control" id="abstract" name="abstract" required></textarea>
            </div>

            <div class="mb-3">
                <label for="certificate" class="form-label">Certificate Upload (JPG, PNG, PDF - Max 5MB):</label>
                <input type="file" class="form-control" id="certificate" name="certificate" required>
            </div>

            <button type="submit" class="btn btn-primary">Submit Application</button>
        </form>
    </div>
</body>
</html>
