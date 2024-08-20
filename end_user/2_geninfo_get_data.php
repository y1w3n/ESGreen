<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esg_assessment"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("INSERT INTO user_backgroundinfo (companyName, industry, function, companySize, annualTurnover, jobTitle) VALUES (?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssss", $companyName, $industry, $function, $companySize, $annualTurnover, $jobTitle);

$companyName = $_POST['companyName'];
$industry = $_POST['industry'];
$function = $_POST['function'];
$companySize = $_POST['companySize'];
$annualTurnover = $_POST['annualTurnover'];
$jobTitle = $_POST['jobTitle'];

$stmt->execute();

$stmt->close();
$conn->close();
