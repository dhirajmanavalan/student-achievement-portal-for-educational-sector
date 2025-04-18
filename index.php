<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "events_db";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_to_student'])) {
    $event_id = intval($_POST['event_id']);

    $check_stmt = $conn->prepare("SELECT event_id FROM student_events WHERE event_id = ?");
    $check_stmt->bind_param("i", $event_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows == 0) {
        $insert_stmt = $conn->prepare("
        INSERT INTO student_events (
            event_id,
            max_count,
            applied_count,
            balance_count,
            event_code,
            title,
            organizer,
            registration_link,
            category,
            status,
            start_date,
            end_date,
            last_date_registration,
            duration,
            location,
            is_posted,
            state,
            country,
            within_bit,
            department,
            eligible_for_winners,
            winner_awards
        )
        SELECT 
            event_id,
            max_count,
            applied_count,
            balance_count,
            event_code,
            title,
            organizer,
            registration_link,
            category,
            status,
            NULLIF(start_date, ''),
            end_date,
            last_date_registration,
            duration,
            location,
            is_posted,
            state,
            country,
            within_bit,
            department,
            eligible_for_winners,
            winner_awards
        FROM events
        WHERE event_id = ?
    ");
    
        $insert_stmt->bind_param("i", $event_id);

        if ($insert_stmt->execute()) {
            $update_stmt = $conn->prepare("UPDATE events SET is_posted = 1 WHERE event_id = ?");
            $update_stmt->bind_param("i", $event_id);
            $update_stmt->execute();

            echo "<script>alert('Event posted to students successfully!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Error posting event.');</script>";
        }
        $insert_stmt->close();
    } else {
        echo "<script>alert('Event already posted to students!');</script>";
    }
    $check_stmt->close();
}

// ✅ Filters
$collegeList = $conn->query("SELECT DISTINCT organizer FROM events");
$locationList = $conn->query("SELECT DISTINCT location FROM events");

$filterCollege = isset($_GET['college']) ? $conn->real_escape_string($_GET['college']) : '';
$filterLocation = isset($_GET['location']) ? $conn->real_escape_string($_GET['location']) : '';
$filterDate = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';

$sqlFilters = "";
if (!empty($filterCollege)) {
    $sqlFilters .= " AND organizer = '$filterCollege'";
}
if (!empty($filterLocation)) {
    $sqlFilters .= " AND location = '$filterLocation'";
}
if (!empty($filterDate)) {
    $sqlFilters .= " AND DATE(start_date) = '$filterDate'";
}

$sql = "SELECT * FROM events WHERE 1=1 $sqlFilters ORDER BY start_date DESC";
$result_filtered = $conn->query($sql);

// ✅ Posted/Unposted Events
$result_unposted = $conn->query("SELECT * FROM events WHERE is_posted = 0");
$result_posted = $conn->query("SELECT * FROM events WHERE is_posted = 1");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
body {
    background-color: #f8f9fa;
    display: flex;
    overflow: hidden;
}

.sidebar {
    width: 250px;
    height: 100vh;
    background-color: #343a40;
    color: white;
    padding: 20px;
    position: fixed;
}

.sidebar a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 10px;
    border-radius: 5px;
}

.content {
    margin-left: 270px;
    padding: 20px;
    width: calc(100% - 270px);
    height: 100vh;
    overflow: auto;
}

.table-responsive {
    width: 100%;
    max-height: 80vh;
    overflow: auto;
    border: 1px solid #dee2e6;
}

table {
    width: max-content;
    min-width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    white-space: nowrap;
    background-color: white;
}

/* Freeze only the header row */
thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background-color: #343a40;
    color: white;
}
</style>


</head>

<body>

<div class="sidebar">
    <h3>📌 Dashboard</h3>
    <a href="#" class="active">🎟 Events</a>
    <a href="adminverify.php" class="nav-link">✅ Verification</a>
    <a href="logout.php" class="nav-link">🚪 Logout</a>
</div>

<div class="content">

    <h2>🎟 Events Dashboard</h2>
    <form action="export_events.php" method="post">
        <button type="submit" class="btn btn-success mb-3">⬇️ Export Events to CSV</button>
    </form>
    <form action="export_student_events.php" method="post">
        <button type="submit" class="btn btn-success mb-3">⬇️ Export Posted Events to CSV</button>
    </form>

    <form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
        <select name="college" class="form-select">
            <option value="">All Colleges</option>
            <?php while ($row = $collegeList->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['organizer']) ?>" <?= $filterCollege == $row['organizer'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['organizer']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="location" class="form-select">
            <option value="">All Locations</option>
            <?php while ($row = $locationList->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['location']) ?>" <?= $filterLocation == $row['location'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['location']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-3">
        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Apply Filter</button>
        <a href="index.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

 <!-- Tabs for switching between Not Posted and Posted -->
 <!-- Bootstrap Nav Tabs -->
<ul class="nav nav-tabs" id="eventTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="not-posted-tab" data-bs-toggle="tab" href="#not-posted" role="tab">🕒 Not Posted</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="posted-tab" data-bs-toggle="tab" href="#posted" role="tab">✅ Posted</a>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="eventTabsContent">
    <!-- Not Posted Events -->
    <div class="tab-pane fade show active" id="not-posted" role="tabpanel">
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                <tr>
    <th>S.No</th>
    <th>ID</th>
    <th>Max Count</th>
    <th>Applied Count</th>
    <th>Balance Count</th>
    <th>Event Code</th>
    <th>Title</th>
    <th>Organizer</th>
    <th>Registration Link</th>
    <th>Category</th>
    <th>Status</th>
    <th>Start Date</th>
    <th>End Date</th>
    <th>Last Date Registration</th>
    <th>Duration</th>
    <th>Location</th>
    <th>Is Posted</th>
    <th>State</th>
    <th>Country</th>
    <th>Within BIT</th>
    <th>Department</th>
    <th>Eligible for Winners</th>
    <th>Winner Awards</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
    <?php 
    $sno = 1; 
    while ($row = $result_unposted->fetch_assoc()): ?>
        <tr>
            <td><?= $sno++ ?></td>
            <td><?= $row['event_id'] ?></td>
            <td><?= $row['max_count'] ?></td>
            <td><?= $row['applied_count'] ?></td>
            <td><?= $row['balance_count'] ?></td>
            <td><?= $row['event_code'] ?></td>
            <td><?= $row['title'] ?></td>
            <td><?= $row['organizer'] ?></td>
            <td><a href="<?= $row['registration_link'] ?>" target="_blank">Register</a></td>
            <td><?= $row['category'] ?></td>
            <td><?= $row['status'] ?></td>
            <td><?= $row['start_date'] ?></td>
            <td><?= $row['end_date'] ?></td>
            <td><?= $row['last_date_registration'] ?></td>
            <td><?= $row['duration'] ?></td>
            <td><?= $row['location'] ?></td>
            <td><?= $row['is_posted'] ?></td>
            <td><?= $row['state'] ?></td>
            <td><?= $row['country'] ?></td>
            <td><?= $row['within_bit'] ?></td>
            <td><?= $row['department'] ?></td>
            <td><?= $row['eligible_for_winners'] ?></td>
            <td><?= $row['winner_awards'] ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="event_id" value="<?= $row['event_id']; ?>">
                    <button type="submit" name="post_to_student" class="btn btn-success">📢 Post to Students</button>
                </form>
            </td>
        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Posted Events -->
    <div class="tab-pane fade" id="posted" role="tabpanel">
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                <tr>
    <th>S.No</th>
    <th>ID</th>
    <th>Max Count</th>
    <th>Applied Count</th>
    <th>Balance Count</th>
    <th>Event Code</th>
    <th>Title</th>
    <th>Organizer</th>
    <th>Registration Link</th>
    <th>Category</th>
    <th>Status</th>
    <th>Start Date</th>
    <th>End Date</th>
    <th>Last Date Registration</th>
    <th>Duration</th>
    <th>Location</th>
    <th>Is Posted</th>
    <th>State</th>
    <th>Country</th>
    <th>Within BIT</th>
    <th>Department</th>
    <th>Eligible for Winners</th>
    <th>Winner Awards</th>
</tr>
</thead>
<tbody>
    <?php 
    $sno = 1; 
    while ($row = $result_posted->fetch_assoc()): ?>
        <tr>
            <td><?= $sno++ ?></td>
            <td><?= $row['event_id'] ?></td>
            <td><?= $row['max_count'] ?></td>
            <td><?= $row['applied_count'] ?></td>
            <td><?= $row['balance_count'] ?></td>
            <td><?= $row['event_code'] ?></td>
            <td><?= $row['title'] ?></td>
            <td><?= $row['organizer'] ?></td>
            <td><a href="<?= $row['registration_link'] ?>" target="_blank">Register</a></td>
            <td><?= $row['category'] ?></td>
            <td><?= $row['status'] ?></td>
            <td><?= $row['start_date'] ?></td>
            <td><?= $row['end_date'] ?></td>
            <td><?= $row['last_date_registration'] ?></td>
            <td><?= $row['duration'] ?></td>
            <td><?= $row['location'] ?></td>
            <td><?= $row['is_posted'] ?></td>
            <td><?= $row['state'] ?></td>
            <td><?= $row['country'] ?></td>
            <td><?= $row['within_bit'] ?></td>
            <td><?= $row['department'] ?></td>
            <td><?= $row['eligible_for_winners'] ?></td>
            <td><?= $row['winner_awards'] ?></td>
        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


</body>
</html>

<?php $conn->close(); ?>
