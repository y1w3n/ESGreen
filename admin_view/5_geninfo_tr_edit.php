<?php
session_start();
include('db_connection.php');

$id = $_GET['id'];
$sql = "SELECT * FROM geninfo_tr WHERE id=$id";
$result = $conn->query($sql);
$submission = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $companyName = $_POST['companyName'];
    $industry = $_POST['industry'];
    $function = $_POST['function'];
    $companySize = $_POST['companySize'];
    $annualTurnover = $_POST['annualTurnover'];
    $jobTitle = $_POST['jobTitle'];

    $sql = "UPDATE geninfo_tr SET 
                company_name='$companyName',
                industry='$industry',
                function='$function',
                company_size='$companySize',
                annual_turnover='$annualTurnover',
                job_title='$jobTitle'
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: 5_geninfo_tr.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Submission</title>
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
				<h1>Edit Submission</h1>
				<div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </header>
            <div class="content">
                <div class="form-container">
                    <form action="" method="POST">
                        <div class="field">
                            <label for="companyName">Company Name:</label>
                            <input type="text" id="companyName" name="companyName" value="<?php echo htmlspecialchars($submission['company_name']); ?>" required>
                        </div>
                        
                        <div class="field">
                            <label for="industry">Industry:</label>
                            <select id="industry" name="industry" required>
                                <option value="">Select Industry</option>
                                <?php echo getOptions($conn, 'industry', $submission['industry']); ?>
                            </select>
                        </div>
                        
                        <div class="field">
                            <label for="function">Function:</label>
                            <select id="function" name="function" required>
                                <option value="">Select Function</option>
                                <?php echo getOptions($conn, 'function', $submission['function']); ?>
                            </select>
                        </div>
                        
                        <div class="field">
                            <label for="companySize">Company Size:</label>
                            <select id="companySize" name="companySize" required>
                                <option value="">Select Company Size</option>
                                <?php echo getOptions($conn, 'company_size', $submission['company_size']); ?>
                            </select>
                        </div>
                        
                        <div class="field">
                            <label for="annualTurnover">Annual Turnover:</label>
                            <select id="annualTurnover" name="annualTurnover" required>
                                <option value="">Select Annual Turnover</option>
                                <?php echo getOptions($conn, 'annual_turnover', $submission['annual_turnover']); ?>
                            </select>
                        </div>
                        
                        <div class="field">
                            <label for="jobTitle">Job Title:</label>
                            <select id="jobTitle" name="jobTitle" required>
                                <option value="">Select Job Title</option>
                                <?php echo getOptions($conn, 'job_title', $submission['job_title']); ?>
                            </select>
                        </div>
                        
                        <div class="field btn">
                            <div class="btn-layer"></div>
                            <button type="submit">Update</button>
                            <a href="5_geninfo_tr.php" class="back-button">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php $conn->close(); ?>
</body>
</html>