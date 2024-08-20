<?php
session_start();
include('db_connection.php');

require 'assets/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $type = $_POST['type'];
    $value = $_POST['value'];
    $sql = "INSERT INTO geninfo_mt (type, value) VALUES ('$type', '$value')";
    if ($conn->query($sql) === TRUE) {
        header("Location: 4_geninfo_mt.php");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM geninfo_mt WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: 4_geninfo_mt.php");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file']['tmp_name'];

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        $expectedHeaders = ['type', 'value'];

        $headers = $data[0];

        if ($headers === $expectedHeaders) {
            array_shift($data);
            foreach ($data as $row) {
                $type = $row[0];
                $value = $row[1];
                $sql = "INSERT INTO geninfo_mt (type, value) VALUES ('$type', '$value')";
                if ($conn->query($sql) !== TRUE) {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
            header("Location: 4_geninfo_mt.php");
            exit;
        } else {
            echo "Error: Invalid headers in the Excel file.";
        }
    } else {
        echo "Error uploading file: " . $_FILES['file']['error'];
    }
}

$types_result = $conn->query("SELECT DISTINCT type FROM geninfo_mt");

$type_filter = isset($_GET['type']) ? $_GET['type'] : '';

$sql = "SELECT * FROM geninfo_mt";
if ($type_filter) {
    $sql .= " WHERE type='$type_filter'";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - View Master Data</title>
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
				<h1>Generic Information Master Data</h1>
				<div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </div>
            <div class="content">
                <div class="filter-container">
                    <form method="GET" action="4_geninfo_mt.php">
                        <label for="type-filter">Filter by Type: </label>
                        <select name="type" id="type-filter" onchange="this.form.submit()">
                            <option value="">All</option>
                            <?php while ($type_row = $types_result->fetch_assoc()): ?>
                                <option value="<?php echo $type_row['type']; ?>" <?php if ($type_row['type'] == $type_filter) echo 'selected'; ?>>
                                    <?php echo $type_row['type']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>
                <div class="form-container">
                    <h3>Add New Option</h3>
                    <form method="POST" action="4_geninfo_mt.php">
                        <input type="hidden" name="create" value="1">
                        <input type="text" name="type" placeholder="Type, eg: Industry" required>
                        <input type="text" name="value" placeholder="Value, eg: Manufacturing" required>
                        <button type="submit">Add</button>
                    </form>
                    <h3> Upload Excel File </h3>
						<p>If you want to add new options for generic options, you can choose to upload Excel File</p>
						<p>You may use the Excel template to upload the file<p>
					<form action="4_geninfo_mt.php" method="post" enctype="multipart/form-data">
                        <label for="file">Upload Excel File:</label>
                        <input type="file" name="file" id="file" accept=".xlsx, .xls">
                        <button type="submit">Upload File</button>
                    </form>
                    <a href="assets/templates/geninfo_mt_add.xlsx" download> Excel Template</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['type']; ?></td>
                            <td><?php echo $row['value']; ?></td>
                            <td class="actions">
                                <a href="4_geninfo_mt_edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                <a href="4_geninfo_mt.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="4_geninfo_mt.php" class="back-button">Back</a>
            </div>
        </div>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
