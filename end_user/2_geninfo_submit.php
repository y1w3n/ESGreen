<?php
session_start();
include('db_connection.php'); // include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // assuming user_id is stored in session after login
    $company_name = $_POST['company_name'];
    $industry = $_POST['industry'];
    $function = $_POST['function'];
    $company_size = $_POST['company_size'];
    $annual_turnover = $_POST['annual_turnover'];
    $job_title = $_POST['job_title'];

    $sql = "INSERT INTO geninfo_tr (user_id, company_name, industry, function, company_size, annual_turnover, job_title)
            VALUES ('$user_id', '$company_name', '$industry', '$function', '$company_size', '$annual_turnover', '$job_title')";

    if (mysqli_query($conn, $sql)) {
        header("Location: 1_overview_flow.php?user_id=$user_id");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
