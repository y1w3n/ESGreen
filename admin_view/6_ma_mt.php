<?php
session_start();
include('db_connection.php');

require 'assets/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $question_text = $_POST['question_text'];
    $response_type = $_POST['response_type'];
    $part = $_POST['part'];
    $industry = $_POST['industry'];
    $sql = "INSERT INTO ma_mt (question_text, response_type, part, industry) VALUES ('$question_text', '$response_type', '$part', '$industry')";
    if ($conn->query($sql) === TRUE) {
        header("Location: 6_ma_mt.php");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM ma_mt WHERE question_id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: 6_ma_mt.php");
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
        $data = $sheet->toArray(null, true, true, true);

        $expectedHeaders = ['A' => 'question_text', 'B' => 'response_type', 'C' => 'part', 'D' => 'industry'];

        $headers = array_slice($data, 0, 1)[0];

        if ($headers === $expectedHeaders) {
            array_shift($data);

            $columns = array_keys($expectedHeaders);
            $columnData = [];
            foreach ($columns as $column) {
                $columnData[$column] = array_column($data, $column);
            }

            $numRows = count($columnData[$columns[0]]);
            for ($i = 0; $i < $numRows; $i++) {
                $question_text = $columnData['A'][$i];
                $response_type = $columnData['B'][$i];
                $part = $columnData['C'][$i];
                $industry = $columnData['D'][$i];

                $question_text = $conn->real_escape_string($question_text);
                $response_type = $conn->real_escape_string($response_type);
                $part = $conn->real_escape_string($part);
                $industry = $conn->real_escape_string($industry);

                $sql = "INSERT INTO ma_mt (question_text, response_type, part, industry) VALUES ('$question_text', '$response_type', '$part', '$industry')";
                if ($conn->query($sql) === TRUE) {
                    echo "<p>Upload completed successfully.</p>";
                } else {
                    echo "Error saving record: " . $conn->error;
                }
            }
		}
    }
}

$parts_result = $conn->query("SELECT DISTINCT part FROM ma_mt");

$part_filter = isset($_GET['part']) ? $_GET['part'] : '';

$sql = "SELECT * FROM ma_mt";
if ($part_filter) {
    $sql .= " WHERE part='$part_filter'";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - View Master Table</title>
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
				<h1>Materiality Assessment Questions</h1>
				<div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </div>
            <div class="content">
                <div class="filter-container">
                    <form method="GET" action="6_ma_mt.php">
                        <label for="part-filter">Filter by Part: </label>
                        <select name="part" id="part-filter" onchange="this.form.submit()">
                            <option value="">All</option>
                            <?php while ($part_row = $parts_result->fetch_assoc()): ?>
                                <option value="<?php echo $part_row['part']; ?>" <?php if ($part_row['part'] == $part_filter) echo 'selected'; ?>>
                                    <?php echo $part_row['part']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>
                <div class="form-container">
                    <h3>Add New Question</h3>
                    <form method="POST" action="6_ma_mt.php">
                        <input type="hidden" name="create" value="1">
                        <input type="text" name="question_text" placeholder="Question Text" required>
                        <input type="text" name="response_type" placeholder="Response Type" required>
                        <input type="text" name="part" placeholder="Part" required>
                        <input type="text" name="industry" placeholder="Industry" required>
                        <button type="submit">Add</button>
                    </form>
                    <h3> Upload Excel File </h3>
						<p>If you want to add new questions for materiality assessment, you can choose to upload Excel File</p>
						<p>You may use the Excel template to upload the file<p>
					<form action="6_ma_mt.php" method="post" enctype="multipart/form-data">
                        <label for="file">Upload Excel File:</label>
                        <input type="file" name="file" id="file" accept=".xlsx, .xls">
                        <button type="submit">Upload File</button>
                    </form>
                    <a href="assets/templates/qs_mt_add.xlsx" download> Excel Template</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Question ID</th>
                            <th>Question Text</th>
                            <th>Response Type</th>
                            <th>Part</th>
                            <th>Industry</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['question_id']; ?></td>
                            <td><?php echo $row['question_text']; ?></td>
                            <td><?php echo $row['response_type']; ?></td>
                            <td><?php echo $row['part']; ?></td>
                            <td><?php echo $row['industry']; ?></td>
                            <td class="actions">
                                <a href="6_ma_mt_edit.php?id=<?php echo $row['question_id']; ?>" class="btn-edit">Edit</a>
                                <a href="6_ma_mt.php?delete=<?php echo $row['question_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="6_ma_mt.php" class="back-button">Back</a>
            </div>
        </div>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
