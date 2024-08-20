<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question</title>
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
                <h1>Add New Question</h1>
				<div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </header>
            <div class="content">
                <form method="POST" action="7_hc_mt_save.php">
                    <label for="question_text">Question Text:</label>
                    <input type="text" name="question_text" required>
                    <label for="question_type">Question Type:</label>
                    <input type="text" name="question_type" required>
                    <label for="part">Part:</label>
                    <input type="text" name="part" required>
                    <button type="submit" name="add_question">Add Question</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
