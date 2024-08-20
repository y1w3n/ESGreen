<?php
session_start();
include('db_connection.php');

// Function to update user generic information in the database
function updateUserGenericInfo($conn, $user_id, $company_name, $industry, $function, $company_size, $annual_turnover, $job_title) {
    $sql = "UPDATE geninfo_tr SET company_name = ?, industry = ?, function = ?, company_size = ?, annual_turnover = ?, job_title = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $company_name, $industry, $function, $company_size, $annual_turnover, $job_title, $user_id);
    return $stmt->execute();
}

// Retrieve the user ID from the session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $company_name = $_POST['company_name'];
    $industry = $_POST['industry'];
    $function = $_POST['function'];
    $company_size = $_POST['company_size'];
    $annual_turnover = $_POST['annual_turnover'];
    $job_title = $_POST['job_title'];

    if (updateUserGenericInfo($conn, $user_id, $company_name, $industry, $function, $company_size, $annual_turnover, $job_title)) {
        header("Location: 2_geninfo_view.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
