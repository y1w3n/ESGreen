<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esg_assessment";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$company_name = $_POST['companyName'];
$industry = $_POST['industry'];
$function = $_POST['function'];
$company_size = $_POST['companySize'];
$annual_turnover = $_POST['annualTurnover']; // Added this line
$job_title = $_POST['jobTitle'];

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO submissions (company_name, industry, function, company_size, annual_turnover, job_title) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $company_name, $industry, $function, $company_size, $annual_turnover, $job_title); // Updated bind_param

// Execute the statement
if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();
?>
