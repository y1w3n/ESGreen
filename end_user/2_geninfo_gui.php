<?php
session_start();
include('db_connection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generic Information Form</title>
    <link rel="stylesheet" href="assets/css/2_style.css">
</head>
<body>
    <div class="wrapper">
        <h1>Submit Generic Information</h1>
        <form action="2_geninfo_submit.php" method="POST">
            <div class="field">
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" required>
            </div>
            <div class="field">
                <label for="industry">Industry</label>
                <select id="industry" name="industry" required>
                    <option value="" disabled selected>Select an option</option>
                    <?php echo getOptions($conn, 'industry'); ?>
                </select>
            </div>
            <div class="field">
                <label for="function">Function</label>
                <select id="function" name="function" required>
                    <option value="" disabled selected>Select an option</option>
                    <?php echo getOptions($conn, 'function'); ?>
                </select>
            </div>
            <div class="field">
                <label for="company_size">Company Size</label>
                <select id="company_size" name="company_size" required>
                    <option value="" disabled selected>Select an option</option>
                    <?php echo getOptions($conn, 'company_size'); ?>
                </select>
            </div>
            <div class="field">
                <label for="annual_turnover">Annual Turnover</label>
                <select id="annual_turnover" name="annual_turnover" required>
                    <option value="" disabled selected>Select an option</option>
                    <?php echo getOptions($conn, 'annual_turnover'); ?>
                </select>
            </div>
            <div class="field">
                <label for="job_title">Job Title</label>
                <select id="job_title" name="job_title" required>
                    <option value="" disabled selected>Select an option</option>
                    <?php echo getOptions($conn, 'job_title'); ?>
                </select>
            </div>
            <div class="field btn">
                <button type="submit">Submit</button>
				<a href="1_overview_flow.php" class="skip-button">Skip</a>
            </div>
        </form>
    </div>
</body>
</html>
