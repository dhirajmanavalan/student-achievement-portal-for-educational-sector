<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "events_db";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the selected event type from the filter, if any
$event_type_filter = isset($_POST['event_type']) ? $_POST['event_type'] : '';

// Fetch event types for the filter
$event_types_sql = "SELECT DISTINCT event_type FROM applications";
$event_types_result = $conn->query($event_types_sql);

// Fetch only pending applications filtered by event type
$sql = "SELECT id, student_name, event_name, event_type, certificate, status FROM applications WHERE status = 'Pending'";

if ($event_type_filter) {
    $sql .= " AND event_type = ?";
}

$stmt = $conn->prepare($sql);
if ($event_type_filter) {
    $stmt->bind_param("s", $event_type_filter);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<div class="sidebar">
    <h3>📌 Dashboard</h3>
    <a href="index.php" onclick="showTab('events')">🎟 Events</a>
    <a href="#" class="active" class="nav-link">✅ Verification</a> 
    <a href="logout.php" class="nav-link">🚪 Logout</a> <!-- Logout Link -->
</div>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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




<body>
    <div class="container mt-5">
        <h2>Applications for Verification</h2>
        
        <!-- Event Type Filter Form -->
        <form method="POST" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="event_type" class="form-label">Filter by Event Type:</label>
                    <select name="event_type" id="event_type" class="form-select">
                        <option value="">All</option>
                        <?php while($event_type = $event_types_result->fetch_assoc()): ?>
                            <option value="<?php echo $event_type['event_type']; ?>" <?php if ($event_type_filter == $event_type['event_type']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($event_type['event_type']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary mt-4">Apply Filter</button>
                </div>
            </div>
        </form>

        <!-- Applications Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Event Name</th>
                    <th>Event Type</th>
                    <th>Certificate</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["student_name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["event_name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["event_type"]); ?></td>
                        <td><a href="uploads/<?php echo htmlspecialchars($row["certificate"]); ?>" target="_blank">View Certificate</a></td>
                        <td><?php echo htmlspecialchars($row["status"]); ?></td>
                        <td>
                            <form method="POST" action="update_succ.php">
                                <input type="hidden" name="application_id" value="<?php echo $row['id']; ?>">
                                <select name="status" class="form-select">
                                    <option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Approved" <?php if ($row['status'] == 'Approved') echo 'selected'; ?>>Approved</option>
                                    <option value="Rejected" <?php if ($row['status'] == 'Rejected') echo 'selected'; ?>>Rejected</option>
                                </select>
                                <textarea name="rejection_reason" class="form-control mt-2" placeholder="Reason for rejection (if any)"></textarea>
                                <button type="submit" class="btn btn-primary mt-2">Update Status</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
