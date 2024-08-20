<?php
session_start();
include('db_connection.php');

$assessment_id = isset($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : null;

if ($assessment_id === null) {
    die("Assessment ID not specified.");
}

$sql = "SELECT assessment_id, user_id, assessment_date, status, assessment_type FROM assessments WHERE assessment_id = $assessment_id";
$result = $conn->query($sql);

$assessment = $result->fetch_assoc();

if (!$assessment) {
    die("Assessment not found.");
}

if ($assessment['assessment_type'] == 'ma') {
    $response_table = 'ma_tr';
    $question_table = 'ma_mt';
} else {
    $response_table = 'hc_tr';
    $question_table = 'hc_mt';
}

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
} else {
    echo "No responses found.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update responses
    foreach ($_POST['responses'] as $question_id => $response) {
        $sql = "UPDATE $response_table SET response = ? WHERE assessment_id = ? AND question_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sii', $response, $assessment_id, $question_id);
        $stmt->execute();
    }
    header("Location: 8_qs_det.php?assessment_id=$assessment_id");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Details</title>
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
				<h1>Assessment Details</h1>
				<div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </header>
            <div class="content">
                <?php if ($assessment): ?>
                    <h2>Assessment ID: <?php echo $assessment['assessment_id']; ?> </h2>
                    <p>User ID: <?php echo $assessment['user_id']; ?></p>
                    <p>Date: <?php echo $assessment['assessment_date']; ?></p>
                    <p>Status: <?php echo $assessment['status']; ?></p>
                    <p>Type: <?php echo $assessment['assessment_type']; ?></p>

                    <h3>Responses:</h3>
					<button class="edit-button" onclick="window.location.href='../end_user/5_dash.html?assessment_id=<?php echo $assessment_id; ?>';">Generate Dashboard</button>
					<button class="edit-button" onclick="enableEditing()">Edit</button>
					<div class="button-group">
                        <button class="save-button" form="edit-form">Save</button>
                    </div>
                    <?php if (!empty($responses)): ?>
                        <form id="edit-form" method="post">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Part</th>
                                        <th>Question</th>
                                        <th>Response</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($responses as $response): ?>
                                        <tr>
                                            <td><?php echo $response['part']; ?></td>
                                            <td><?php echo $response['question_text']; ?></td>
                                            <td>
                                                <span class="response-text"><?php echo $response['response']; ?></span>
                                                <input type="text" name="responses[<?php echo $response['question_id']; ?>]" value="<?php echo $response['response']; ?>" class="response-input" style="display: none;">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>                  
						 </form>
						 <button class="back-button" onclick="location.href='8_qs_tr.php'">Back</button>  
                    <?php else: ?>
                        <p>No responses found.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Assessment details not found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function enableEditing() {
            document.querySelectorAll('.response-text').forEach(function(el) {
                el.style.display = 'none';
            });
            document.querySelectorAll('.response-input').forEach(function(el) {
                el.style.display = 'inline';
            });
            document.querySelector('.edit-button').style.display = 'none';
            document.querySelector('.save-button').style.display = 'inline';
        }
    </script>
</body>
</html>
