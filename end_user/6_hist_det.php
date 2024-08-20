<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db_connection.php'; // Assuming functions.php has necessary DB connection and functions

$assessment_id = isset($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : null;

if ($assessment_id === null) {
    die("Assessment ID not specified.");
}

// Fetch assessment details
$sql = "SELECT assessment_date, status, assessment_type FROM assessments WHERE assessment_id = $assessment_id";
$result = $conn->query($sql);

$assessment = $result->fetch_assoc();

if (!$assessment) {
    die("Assessment not found.");
}

// Determine the table to fetch responses from based on assessment_type
if ($assessment['assessment_type'] == 'ma') {
    $response_table = 'ma_tr';
    $question_table = 'ma_mt';
} else {
    $response_table = 'hc_tr';
    $question_table = 'hc_mt';
}

// Fetch responses for the assessment, including question text
$sql = "SELECT r.question_id, r.response, q.question_text, q.part
        FROM $response_table r
        JOIN $question_table q ON r.question_id = q.question_id
        WHERE r.assessment_id = $assessment_id
        ORDER BY r.question_id";
$result = $conn->query($sql);

$responses = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $responses[] = $row;
    }
} 
// else {
//     echo "No responses found.";
// }
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Details</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
        <div class="sidebar">
            <ul class="sidebar-menu">
			<?php if ($is_admin): ?>
                    <li><a id="back-button" href="../admin_view/1_main.html" class="back-button">Admin Portal</a></li>
                <?php endif; ?>
                <li><a href="1_overview_flow.php">Overview</a></li>
                <li><a href="2_geninfo_view.php">Generic Information</a></li>
                <li><a href="6_history.php">History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <header class="header">
                <img src="assets/images/esgreen.png" alt="Logo 1" class="logo1">
                <h1>History</h1>
                <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
            </header>
            <div class="content">
                <h1>Assessment on <?php echo htmlspecialchars($assessment['assessment_date']); ?></h1>
                <p>Status: <?php echo htmlspecialchars($assessment['status']); ?></p>
                <p>Type: <?php echo htmlspecialchars($assessment['assessment_type']); ?></p>
                <table>
                    <tr>
                        <th>Part</th>
                        <th>Question</th>
                        <th>Response</th>
                    </tr>
                    <?php foreach ($responses as $response): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($response['part']); ?></td>
                            <td><?php echo htmlspecialchars($response['question_text']); ?></td>
                            <td><?php echo htmlspecialchars($response['response']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>