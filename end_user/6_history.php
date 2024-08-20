<?php
session_start();
include('db_connection.php');

// Retrieve the user ID from the session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Guest';

// Function to retrieve user assessments from the database
function getUserAssessments($conn, $user_id) {
    $sql = "SELECT a.assessment_id, a.assessment_date, a.status, a.assessment_type, 
                   COALESCE(c.progress, 'No consultation') AS consultation_progress
            FROM assessments a 
            LEFT JOIN consultation c ON a.assessment_id = c.assessment_id
            WHERE a.user_id = ? 
            ORDER BY a.assessment_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Retrieve user's assessment history if user ID is set
$user_assessments = $user_id !== 'Guest' ? getUserAssessments($conn, $user_id) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessments History</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
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
                <div class="history-container">
                    <h2>Assessment History</h2>
                    <?php if ($user_assessments): ?>
                        <table>
                            <tr>
                                <th>Assessment ID</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Consultation Status</th>
                                <th>Type</th>
                                <th>Details</th>
                            </tr>
                            <?php foreach ($user_assessments as $assessment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assessment['assessment_id']); ?></td>
                                    <td><?php echo htmlspecialchars($assessment['assessment_date']); ?></td>
                                    <td><?php echo htmlspecialchars($assessment['status']); ?></td>
                                    <td><?php echo htmlspecialchars($assessment['consultation_progress']); ?></td>
                                    <td><?php echo htmlspecialchars($assessment['assessment_type']); ?></td>
                                    <td>
                                        <a href="5_dash.html?assessment_id=<?php echo $assessment['assessment_id']; ?>" class="details-button">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>No assessment history found for this user.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
