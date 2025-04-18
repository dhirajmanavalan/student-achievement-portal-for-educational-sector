<?php
include 'db_config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="Posted_events.csv"');

// Output CSV headers
$output = fopen("php://output", "w");

// Get all columns from `events` table
$sql = "SELECT * FROM student_events";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output column names dynamically
    $columns = array_keys($result->fetch_assoc());
    fputcsv($output, $columns);

    // Reset result pointer and re-fetch data
    $result->data_seek(0);

    // Output each row
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

fclose($output);
$conn->close();
exit();
?>
