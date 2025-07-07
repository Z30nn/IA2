<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('../includes/dbconnection.php');
include('../includes/session.php');

// Filtering
$eventType = isset($_GET['event_type']) ? $_GET['event_type'] : '';
$user = isset($_GET['user']) ? $_GET['user'] : '';

$where = [];
if ($eventType) {
    $where[] = "event_type = '" . mysqli_real_escape_string($con, $eventType) . "'";
}
if ($user) {
    $where[] = "user = '" . mysqli_real_escape_string($con, $user) . "'";
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$query = mysqli_query($con, "SELECT * FROM audit_logs $whereSql ORDER BY timestamp DESC LIMIT 200");
$auditRows = [];
while ($row = mysqli_fetch_assoc($query)) {
    $auditRows[] = $row;
}
// Get unique event types and users for filter dropdowns
$eventTypesRes = mysqli_query($con, "SELECT DISTINCT event_type FROM audit_logs");
$usersRes = mysqli_query($con, "SELECT DISTINCT user FROM audit_logs");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs</title>
    <link rel="stylesheet" href="../assets/css/style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'includes/leftMenu.php'; ?>
<div id="right-panel" class="right-panel">
    <?php include 'includes/header.php'; ?>
    <div class="breadcrumbs">
        <div class="breadcrumbs-inner">
            <div class="row m-0">
                <div class="col-sm-4">
                    <div class="page-header float-left">
                        <div class="page-title">
                            <h1>Audit Logs</h1>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="page-header float-right">
                        <div class="page-title">
                            <ol class="breadcrumb text-right">
                                <li><a href="index.php">Dashboard</a></li>
                                <li class="active">Audit Logs</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="animated fadeIn">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Audit Log Viewer</strong>
                </div>
                <div class="card-body">
                    <form method="get" class="form-inline mb-3">
                        <label class="mr-2">Event Type:</label>
                        <select name="event_type" class="form-control mr-2">
                            <option value="">All</option>
                            <?php while($et = mysqli_fetch_assoc($eventTypesRes)) { ?>
                                <option value="<?php echo htmlspecialchars($et['event_type']); ?>" <?php if($eventType == $et['event_type']) echo 'selected'; ?>><?php echo htmlspecialchars($et['event_type']); ?></option>
                            <?php } ?>
                        </select>
                        <label class="mr-2">User:</label>
                        <select name="user" class="form-control mr-2">
                            <option value="">All</option>
                            <?php while($u = mysqli_fetch_assoc($usersRes)) { ?>
                                <option value="<?php echo htmlspecialchars($u['user']); ?>" <?php if($user == $u['user']) echo 'selected'; ?>><?php echo htmlspecialchars($u['user']); ?></option>
                            <?php } ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Event Type</th>
                                    <th>Event Details</th>
                                    <th>IP Address</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($auditRows as $row) { ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['user']); ?></td>
                                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                                        <td><?php echo htmlspecialchars($row['event_type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['event_details']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                                        <td><?php echo $row['timestamp']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html> 