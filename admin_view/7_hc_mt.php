<?php
session_start();
include('db_connection.php');

require 'assets/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $question_text = $_POST['question_text'];
    $response_type = strtoupper($_POST['response_type']);  // Ensure response type is uppercase
    $part = $_POST['part'];
    $subpart = $_POST['subpart'];
    $industry = $_POST['industry'];

    // Validate and sanitize data
    $question_text = $conn->real_escape_string($question_text);
    $response_type = $conn->real_escape_string($response_type);
    $part = $conn->real_escape_string($part);
    $subpart = $conn->real_escape_string($subpart);
    $industry = $conn->real_escape_string($industry);

    if (in_array($response_type, ['YES', 'NO'])) {
        $sql = "INSERT INTO hc_mt (question_text, response_type, part, subpart, industry) VALUES ('$question_text', '$response_type', '$part', '$subpart', '$industry')";
        if ($conn->query($sql) === TRUE) {
            header("Location: 7_hc_mt.php");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error: Response type must be 'YES' or 'NO'.";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM hc_mt WHERE question_id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: 7_hc_mt.php");
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

        $expectedHeaders = ['A' => 'question_text', 'B' => 'response_type', 'C' => 'part', 'D' => 'subpart', 'E' => 'industry'];

        $headers = $data[1];  // Get headers from the first row

        if ($headers == $expectedHeaders) {
            array_shift($data);  // Remove headers

            foreach ($data as $row) {
                $question_text = $row['A'];
                $response_type = strtoupper($row['B']);  // Ensure response type is uppercase
                $part = $row['C'];
                $subpart = $row['D'];
                $industry = $row['E'];

                if ($question_text && $response_type && $part && $subpart && $industry) {
                    $question_text = $conn->real_escape_string($question_text);
                    $response_type = $conn->real_escape_string($response_type);
                    $part = $conn->real_escape_string($part);
                    $subpart = $conn->real_escape_string($subpart);
                    $industry = $conn->real_escape_string($industry);

                    if (in_array($response_type, ['YES', 'NO'])) {
                        $sql = "INSERT INTO hc_mt (question_text, response_type, part, subpart, industry) VALUES ('$question_text', '$response_type', '$part', '$subpart', '$industry')";
                        if ($conn->query($sql) !== TRUE) {
                            echo "Error saving record for row: " . $conn->error;
                        }
                    } else {
                        echo "Error: Response type must be 'YES' or 'NO' for row.";
                    }
                } else {
                    echo "Error: Missing required fields for row.";
                }
            }
            echo "Upload completed successfully.";
        } else {
            echo "Error: Invalid headers in the Excel file.<br>";
            echo "Expected headers: " . implode(', ', $expectedHeaders) . "<br>";
            echo "Found headers: " . implode(', ', $headers) . "<br>";
        }
    } else {
        echo "Error uploading file: " . $_FILES['file']['error'] . "<br>";
    }
}

$parts_result = $conn->query("SELECT DISTINCT part FROM hc_mt");
$subparts_result = $conn->query("SELECT DISTINCT subpart FROM hc_mt");
$industries_result = $conn->query("SELECT DISTINCT industry FROM hc_mt");

$part_filter = isset($_GET['part']) ? $_GET['part'] : '';
$subpart_filter = isset($_GET['subpart']) ? $_GET['subpart'] : '';
$industry_filter = isset($_GET['industry']) ? $_GET['industry'] : '';

// Retrieve all industries and split them by comma
$industries_set = [];
while ($row = $industries_result->fetch_assoc()) {
    $industries = explode(',', $row['industry']);
    foreach ($industries as $industry) {
        $industries_set[trim($industry)] = true;
    }
}

$sql = "SELECT * FROM hc_mt WHERE 1=1";
if ($part_filter) {
    $sql .= " AND part='$part_filter'";
}
if ($subpart_filter) {
    $sql .= " AND subpart='$subpart_filter'";
}
if ($industry_filter) {
    $sql .= " AND industry LIKE '%$industry_filter%'";
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
                <h1>ESG Health Check Questions</h1>
                <div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </div>
            <div class="content">
                <div class="filter-container">
                    <form method="GET" action="7_hc_mt.php">
                        <label for="part-filter">Filter by Part: </label>
                        <select name="part" id="part-filter" onchange="this.form.submit()">
                            <option value="">All</option>
                            <?php while ($part_row = $parts_result->fetch_assoc()): ?>
                                <option value="<?php echo $part_row['part']; ?>" <?php if ($part_row['part'] == $part_filter) echo 'selected'; ?>>
                                    <?php echo $part_row['part']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <label for="subpart-filter">Filter by Sub-part: </label>
                        <select name="subpart" id="subpart-filter" onchange="this.form.submit()">
                            <option value="">All</option>
                            <?php while ($subpart_row = $subparts_result->fetch_assoc()): ?>
                                <option value="<?php echo $subpart_row['subpart']; ?>" <?php if ($subpart_row['subpart'] == $subpart_filter) echo 'selected'; ?>>
                                    <?php echo $subpart_row['subpart']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <label for="industry-filter">Filter by Industry: </label>
                        <select name="industry" id="industry-filter" onchange="this.form.submit()">
                            <option value="">All</option>
                            <?php foreach (array_keys($industries_set) as $industry): ?>
                                <option value="<?php echo $industry; ?>" <?php if ($industry == $industry_filter) echo 'selected'; ?>>
                                    <?php echo $industry; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <div class="form-container">
                    <h3>Add New Question</h3>
                    <form method="POST" action="7_hc_mt.php">
                        <input type="hidden" name="create" value="1">
                        <input type="text" name="question_text" placeholder="Question Text" required>
                        <input type="text" name="response_type" placeholder="Response Type" required>
                        <input type="text" name="part" placeholder="Part" required>
                        <input type="text" name="subpart" placeholder="Sub-part" required>
                        <input type="text" name="industry" placeholder="Industry" required>
                        <button type="submit">Add</button>
                    </form>
                    <h3> Upload Excel File </h3>
                    <p>If you want to add new questions for healthcheck assessment, you can choose to upload Excel File</p>
                    <p>You may use the Excel template to upload the file</p>
                    <form action="7_hc_mt.php" method="post" enctype="multipart/form-data">
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
                            <th class="question-text">Question Text</th>
                            <th>Response Type</th>
                            <th>Part</th>
                            <th>Sub-part</th>
                            <th class="industry">Industry</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['question_id']; ?></td>
                            <td class="question-text"><?php echo $row['question_text']; ?></td>
                            <td><?php echo $row['response_type']; ?></td>
                            <td><?php echo $row['part']; ?></td>
                            <td><?php echo $row['subpart']; ?></td>
                            <td class="industry"><?php echo nl2br(str_replace(',', "\n", $row['industry'])); ?></td>
                            <td class="actions">
                                <a href="7_hc_mt_edit.php?id=<?php echo $row['question_id']; ?>" class="btn-edit">Edit</a>
                                <a href="7_hc_mt.php?delete=<?php echo $row['question_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="7_hc_mt.php" class="back-button">Back</a>
            </div>
        </div>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
