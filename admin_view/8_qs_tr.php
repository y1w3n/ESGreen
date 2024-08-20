<?php
session_start();
include 'db_connection.php';

if (isset($_POST['delete_submission'])) {
    $deleteAssessmentId = (int)$_POST['delete_submission'];
    $deleteQuery = "DELETE FROM assessments WHERE assessment_id = $deleteAssessmentId";
    $conn->query($deleteQuery);
    $deleteQuery = "DELETE FROM ma_tr WHERE assessment_id = $deleteAssessmentId";
    $conn->query($deleteQuery);
    $deleteQuery = "DELETE FROM hc_tr WHERE assessment_id = $deleteAssessmentId";
    $conn->query($deleteQuery);
}

$sql = "SELECT assessment_id, user_id, assessment_date, status, assessment_type FROM assessments ORDER BY assessment_id";
$result = $conn->query($sql);

$assessments = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $assessments[] = $row;
    }
} else {
    echo "No assessments found.";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Users Submissions</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
			<ul class="sidebar-menu">
                <li><a href="1_main.html">Overview</a></li>
                <li><a href="2_kb.php">Tasks</a></li>
				<li><a href="../end_user/1_overview_flow.php">Client Portal</a></li>
                <li><a href="3_user.php">Users Management</a></li>
                <li><a href="4_geninfo_mt.php">Generic Information Master Data</a></li>
x                <li><a href="6_ma_mt.php">Materiality Assessment Questions</a></li>
                <li><a href="7_hc_mt.php">Health Check Assessment Questions</a></li>
                <li><a href="8_qs_tr.php">Users Submissions Status</a></li>
                <li><a href="9_rp_vis.html">Reports</a></li>
                <li><a href="10_dev.html">Development Portal</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <header class="header">
				<img src="assets/images/esgreen.png" alt="Logo 1" class="logo1">
                <h1>Review Users Submissions</h1>
				<div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </header>
            <div class="content">
                <?php if (!empty($assessments)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Assessment ID</th>
                                <th>User ID</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assessments as $assessment): ?>
                                <tr>
                                    <td><?php echo $assessment['assessment_id']; ?></td>
                                    <td><?php echo $assessment['user_id']; ?></td>
                                    <td><?php echo $assessment['assessment_date']; ?></td>
                                    <td><?php echo $assessment['status']; ?></td>
                                    <td><?php echo $assessment['assessment_type']; ?></td>
                                    <td><a href="8_qs_det.php?assessment_id=<?php echo $assessment['assessment_id']; ?>" class="btn btn-delete">View Details</a></td>
                                    <td>
                                        <form method="POST" action="" style="display:inline;">
                                            <button type="submit" name="delete_submission" value="<?php echo $assessment['assessment_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this submission?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No assessments found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
