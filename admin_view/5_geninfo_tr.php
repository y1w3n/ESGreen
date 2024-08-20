<?php
session_start();
include('db_connection.php');

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM geninfo_tr WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "Record deleted successfully";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

$sql = "SELECT * FROM geninfo_tr";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
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
        </div>
        <div class="main-content">
            <div class="header">
				<img src="assets/images/esgreen.png" alt="Logo 1" class="logo1">
				<h1>Users Background</h1>
				<div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </div>
            <div class="content">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company Name</th>
                            <th>Industry</th>
                            <th>Function</th>
                            <th>Company Size</th>
                            <th>Annual Turnover</th>
                            <th>Job Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['company_name']; ?></td>
                            <td><?php echo $row['industry']; ?></td>
                            <td><?php echo $row['function']; ?></td>
                            <td><?php echo $row['company_size']; ?></td>
                            <td><?php echo $row['annual_turnover']; ?></td>
                            <td><?php echo $row['job_title']; ?></td>
                            <td class="actions">
                                <a href="5_geninfo_tr_edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                <a href="5_geninfo_tr.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="5_geninfo_tr.php" class="back-button">Back</a>
            </div>
        </div>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
