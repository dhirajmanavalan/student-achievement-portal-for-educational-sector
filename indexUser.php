<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "events_db";
$port = 3306;

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all event data from student_events
$sql = "SELECT * FROM student_events";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Events Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 90%;
        padding: 40px 60px; /* Increased padding for bigger look */
        margin-left: 180px;
    }

    .table-responsive-scroll {
        max-height: 600px; /* Set visible height */
        overflow-x: auto;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    table {
        white-space: nowrap;
        min-width: 1000px;
        font-size: 1rem; /* Bigger font */
    }

    th, td {
        vertical-align: middle !important;
        padding: 14px 20px;
    }

    th {
        background-color: #343a40;
        color: #fff;
        text-align: center;
        font-size: 1rem;
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        width: 220px;
        background-color: #343a40;
        color: #fff;
        padding: 30px 20px;
        font-size: 1rem;
    }

    .sidebar h2 {
        font-size: 1.4rem;
        margin-bottom: 25px;
    }

    .sidebar a {
        color: #fff;
        text-decoration: none;
        display: block;
        margin: 12px 0;
        font-size: 1rem;
    }

    .sidebar a:hover {
        background-color: #495057;
        border-radius: 6px;
        padding: 6px;
    }

    .content {
        margin-left: 240px;
        padding: 40px;
    }

    .btn-sm {
        font-size: 0.85rem;
    }

    @media (max-width: 768px) {
        .sidebar {
            position: relative;
            width: 100%;
        }

        .content {
            margin-left: 0;
        }

        table {
            min-width: 1000px;
        }
    }
</style>


</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>🎓 Event Portal</h2>
    <hr>
    <a href="#">🏠 Home</a>
    <a href="status.php">📄 Application Status</a>
    <hr>
    <!-- Profile Section -->
    <div class="profile">
        <?php if (isset($_SESSION['username']) && isset($_SESSION['student_name'])): ?>
            <p><strong><?php echo htmlspecialchars($_SESSION['student_name']); ?></strong></p>
            <p>👤 <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        <?php else: ?>
            <a href="user_login.html" class="btn btn-primary btn-sm">Login</a>
        <?php endif; ?>
    </div>
</div>

<!-- Main Content -->
<div class="container mt-5">
    <h2 class="mb-4">🎓 All Student Events</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr>
            <th>Event ID</th>
            <th>Event Code</th>
            <th>Title</th>
            <th>Organizer</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Last Date to Register</th>
            <th>Duration (hrs)</th>
            <th>Location</th>
            <th>State</th>
            <th>Country</th>
            <th>Within BIT</th>
            <th>Department</th>
            <th>Max Count</th>
            <th>Applied Count</th>
            <th>Balance Count</th>
            <th>Category</th>
            <th>Status</th>
            <th>Eligible for Winners</th>
            <th>Winner Awards</th>
            <th>Registration Link</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['event_id']) ?></td>
                    <td><?= htmlspecialchars($row['event_code']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['organizer']) ?></td>
                    <td><?= htmlspecialchars($row['start_date']) ?></td>
                    <td><?= htmlspecialchars($row['end_date']) ?></td>
                    <td><?= htmlspecialchars($row['last_date_registration']) ?></td>
                    <td><?= htmlspecialchars($row['duration']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= htmlspecialchars($row['state']) ?></td>
                    <td><?= htmlspecialchars($row['country']) ?></td>
                    <td><?= htmlspecialchars($row['within_bit']) ?></td>
                    <td><?= htmlspecialchars($row['department']) ?></td>
                    <td><?= htmlspecialchars($row['max_count']) ?></td>
                    <td><?= htmlspecialchars($row['applied_count']) ?></td>
                    <td><?= htmlspecialchars($row['balance_count']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['eligible_for_winners']) ?></td>
                    <td><?= htmlspecialchars($row['winner_awards']) ?></td>
                    <td>
                        <a href="<?= htmlspecialchars($row['registration_link']) ?>" target="_blank" class="btn btn-primary btn-sm">🔗 Register</a>
                    </td>
                    <td>
                        <a href="apply.php?event_id=<?= urlencode($row['event_id']) ?>" class="btn btn-success btn-sm">📝 Apply</a>
                    </td>
                </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="27" class="text-center">No events found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

<?php
$conn->close();
?>