<?php
session_start();
include('db_connection.php');

// Debugging: Check if session variables are set
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

// Assuming $user_id is obtained from the session or other secure method
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php"); // Redirect to login if user_id is not set
    exit();
}

$role = $_SESSION['role'] ?? '';
$is_admin = $role === 'admin';
$company_name = 'Guest';

// Check if user exists in geninfo_tr and assessments
$geninfo_exists = false;
$assessments_exists = false;

// Fetch company name and check existence in geninfo_tr
if ($stmt = $conn->prepare("SELECT company_name FROM geninfo_tr WHERE user_id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($fetched_company_name);
    $stmt->fetch();
    if ($fetched_company_name) {
        $company_name = $fetched_company_name;
        $geninfo_exists = true;
    }
    $stmt->close();
}

// Check existence in assessments
if ($stmt = $conn->prepare("SELECT COUNT(*) FROM assessments WHERE user_id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $assessments_exists = $count > 0;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Page</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
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
                <h1>ESG Overview</h1>
                <img src="assets/images/sunway-logo.png" alt="Logo 2" class="logo">
            </header>
            <div class="content">
                <div class="banner">
                    <div class="banner-text">
                        <h1>Welcome, <?php echo htmlspecialchars($company_name); ?></h1>
                        <p>Find best practices, guidelines, and more in just one click</p>
                    </div>
                </div>
                <ul class="stepper">
                    <li class="<?php echo $geninfo_exists ? 'done' : 'ready'; ?>">
                        <div class="item">
                            <a href="2_geninfo_gui.php">Generic Info</a>
                        </div>
                    </li>
                    <li class="<?php echo $assessments_exists ? 'done' : 'ready'; ?>">
                        <div class="item">
                            <a href="3_hc_intro.php">HealthCheck Assessment</a>
                            <a href="4_ma_gui.php">Materiality Assessment</a>
                        </div>
                    </li>
                    <li class="<?php echo $assessments_exists ? 'done' : 'ready'; ?>">
                        <div class="item">
                            <a href="6_history.php">Assessment Result</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <script>
        // Add a simple click event to log to the console for debugging
        document.getElementById('back-button').addEventListener('click', function() {
            console.log('Back button clicked');
        });

        // Apply animation delay to each step in the process flow
        document.querySelectorAll('.stepper li').forEach((step, index) => {
            step.style.animationDelay = `${index * 0.05}s`;
        });
    </script>
</body>
</html>