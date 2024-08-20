<?php
session_start();
include('db_connection.php');

function getUserGenericInfo($conn, $user_id) {
    $sql = "SELECT company_name, industry, function, company_size, annual_turnover, job_title FROM geninfo_tr WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$user_info = $user_id ? getUserGenericInfo($conn, $user_id) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Page</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <script>
        function toggleEdit() {
            var viewFields = document.querySelectorAll('.view');
            var editFields = document.querySelectorAll('.editable');
            var editButton = document.querySelector('.edit-btn');
            var saveButton = document.querySelector('.save-btn');

            viewFields.forEach(function(field) {
                field.style.display = 'none';
            });

            editFields.forEach(function(field) {
                field.style.display = 'block';
            });

            editButton.style.display = 'none';
            saveButton.style.display = 'inline-block';
        }
    </script>
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
                <h1>General Information</h1>
                <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
            </header>
            <div class="content">
                <div class="user-info form-container">
                    <h2>Generic Information</h2>
                    <?php if ($user_info): ?>
                        <form action="update_info.php" method="post">
                            <table>
                                <tr>
                                    <td>Company Name</td>
                                    <td><span class="view"><?php echo htmlspecialchars($user_info['company_name']); ?></span>
                                        <input type="text" name="company_name" class="editable" value="<?php echo htmlspecialchars($user_info['company_name']); ?>"></td>
                                </tr>
                                <tr>
                                    <td>Industry</td>
                                    <td><span class="view"><?php echo htmlspecialchars($user_info['industry']); ?></span>
                                        <input type="text" name="industry" class="editable" value="<?php echo htmlspecialchars($user_info['industry']); ?>"></td>
                                </tr>
                                <tr>
                                    <td>Function</td>
                                    <td><span class="view"><?php echo htmlspecialchars($user_info['function']); ?></span>
                                        <input type="text" name="function" class="editable" value="<?php echo htmlspecialchars($user_info['function']); ?>"></td>
                                </tr>
                                <tr>
                                    <td>Company Size</td>
                                    <td><span class="view"><?php echo htmlspecialchars($user_info['company_size']); ?></span>
                                        <input type="text" name="company_size" class="editable" value="<?php echo htmlspecialchars($user_info['company_size']); ?>"></td>
                                </tr>
                                <tr>
                                    <td>Annual Turnover</td>
                                    <td><span class="view"><?php echo htmlspecialchars($user_info['annual_turnover']); ?></span>
                                        <input type="text" name="annual_turnover" class="editable" value="<?php echo htmlspecialchars($user_info['annual_turnover']); ?>"></td>
                                </tr>
                                <tr>
                                    <td>Job Title</td>
                                    <td><span class="view"><?php echo htmlspecialchars($user_info['job_title']); ?></span>
                                        <input type="text" name="job_title" class="editable" value="<?php echo htmlspecialchars($user_info['job_title']); ?>"></td>
                                </tr>
                            </table>
                            <button type="button" class="edit-btn" onclick="toggleEdit()">Edit</button>
                            <button type="submit" class="save-btn" style="display:none;">Save</button>
                        </form>
                    <?php else: ?>
                        <p>No generic information found for this user.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
