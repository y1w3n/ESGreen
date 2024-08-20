<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('db_connection.php');

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$questionId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($questionId === null) {
    die("Invalid question ID.");
}

$stmt = $conn->prepare("SELECT * FROM hc_mt WHERE question_id = ?");
$stmt->bind_param("i", $questionId);
$stmt->execute();
$result = $stmt->get_result();

$question = [];
if ($result->num_rows > 0) {
    $question = $result->fetch_assoc();
} else {
    die("Question not found.");
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question</title>
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
                <h1>Edit Question</h1>
				<div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </header>
            <div class="content">
                <form method="POST" action="7_hc_mt_save.php">
                    <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($questionId); ?>">
                    <label for="question_text">Question Text:</label>
                    <input type="text" name="question_text" value="<?php echo htmlspecialchars($question['question_text']); ?>" required>
                    <label for="response_type">Response Type:</label>
                    <input type="text" name="response_type" value="<?php echo htmlspecialchars($question['response_type']); ?>" required>
                    <label for="part">Part:</label>
                    <input type="text" name="part" value="<?php echo htmlspecialchars($question['part']); ?>" required>
                    <label for="industry">Industry:</label>
                    <input type="text" name="industry" value="<?php echo htmlspecialchars($question['industry']); ?>" required>
                    <button type="submit" name="save_question">Save</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
