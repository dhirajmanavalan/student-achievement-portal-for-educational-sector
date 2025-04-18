<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || !isset($_SESSION['student_name'])) {
    // If session variables are not set, redirect to login page
    header("Location: user_login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "events_db"; 
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$student_name = $_SESSION['student_name'];  

// Fetch all event applications for the logged-in student
$sql_status = "SELECT student_id, event_name, event_type, certificate, event_date, status, submission_date 
               FROM applications WHERE student_name = ? ORDER BY id DESC";
$stmt_status = $conn->prepare($sql_status);
$stmt_status->bind_param("s", $student_name);
$stmt_status->execute();
$result = $stmt_status->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card { border-radius: 10px; box-shadow: 3px 3px 15px rgba(0, 0, 0, 0.2); transition: transform 0.3s; }
        .card:hover { transform: scale(1.03); }
        .card-header { background: #007bff; color: white; font-weight: bold; text-align: center; }

        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding: 20px;
            position: fixed;
            left: 0;
            top: 0;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #007bff;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            width: 100%;
        }
        .card {
            border-radius: 10px;
            box-shadow: 3px 3px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: scale(1.03);
        }
        .card-header {
            background: #007bff;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .event-link {
            text-decoration: none;
            font-weight: bold;
        }
        .hidden {
            display: none;
        }
    </style>

</head>
<div class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white" style="width: 250px; height: 100vh; position: fixed;">
    <a href="#" class="d-flex align-items-center mb-3 text-white text-decoration-none">
        <span class="fs-4">🎓 Event Portal</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="indexUser.php" class="nav-link text-white">
                <i class="bi bi-house-door"></i> Home
            </a>
        </li>
        <li>
            <a href="status.php" class="nav-link text-white">
                <i class="bi bi-file-earmark-check"></i> Application Status
            </a>
        </li>
    </ul>
    <hr>

    <!-- Profile Section at the Bottom -->
    <div class="text-center mt-auto">
        <?php if (isset($_SESSION['username']) && isset($_SESSION['student_name'])): ?>
            <div class="border-top pt-3">
                <p class="mb-1"><strong><?php echo htmlspecialchars($_SESSION['student_name']); ?></strong></p>
                <p class="mb-2 small">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <a href="logout.php" class="btn btn-danger btn-sm w-100">Logout</a>
            </div>
        <?php else: ?>
            <a href="user_login.html" class="btn btn-primary btn-sm w-100">Login</a>
        <?php endif; ?>
    </div>
</div>


<body>

<div class="container mt-5">
    <h2 class="mb-4">Application Status</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Student ID</th>
                    <th>Event Name</th>
                    <th>Event Type</th>
                    <th>Certificate</th>
                    <th>Event Date</th>
                    <th>Status</th>
                    <th>Submission Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['event_type']); ?></td>
                        <td>
                            <?php if (!empty($row['certificate'])): ?>
                                <a href="certificates/<?php echo htmlspecialchars($row['certificate']); ?>" target="_blank">View</a>
                            <?php else: ?>
                                No Certificate
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="alert alert-warning">No applications found.</p>
    <?php endif; ?>

    <a href="indexUser.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

</body>
</html>

<?php
$stmt_status->close();
$conn->close();
?>
