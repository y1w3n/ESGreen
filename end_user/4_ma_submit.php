<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$_SESSION['assessment_type'] = 'ma';
include('db_connection.php'); // Include your database connection

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    die("Error: user_id is not set in the session.");
}

if (!isset($_SESSION['assessment_type'])) {
    die("Error: assessment_type is not set in the session.");
}

$user_id = $_SESSION['user_id'];
$assessment_type = $_SESSION['assessment_type']; // Get the assessment type from the session

// Ensure assessment_id is set in session or create a new one
if (isset($_SESSION['assessment_id'])) {
    $status = "in progress"; // Initial status
    $assessment_date = date('Y-m-d H:i:s');
    
    // Insert a new assessment
    $sql_insert = "INSERT INTO assessments (user_id, assessment_date, status, assessment_type) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isss", $user_id, $assessment_date, $status, $_SESSION['assessment_type']);
    
    if ($stmt_insert->execute()) {
        // Get the last inserted ID and set it in the session
        $_SESSION['assessment_id'] = $stmt_insert->insert_id;
        echo "New assessment created successfully. Assessment ID: " . $_SESSION['assessment_id'] . "<br>";
    } else {
        die("Error creating new assessment: " . $stmt_insert->error);
    }
    
    $stmt_insert->close();
}

$assessment_id = $_SESSION['assessment_id'];

echo "Debug: assessment_id = $assessment_id, user_id = $user_id, assessment_type = $assessment_type<br>";

// Verify that the assessment_id exists in the assessments table
$sql_check = "SELECT assessment_id FROM assessments WHERE assessment_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $assessment_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    die("Error: assessment_id does not exist in the assessments table.");
}

// Continue with the insertion process
$response_date = date('Y-m-d H:i:s');

foreach ($_POST as $key => $value) {
    if (strpos($key, 'question_') === 0) {
        $question_id = intval(str_replace('question_', '', $key));
        $response = $conn->real_escape_string($value);
        
        // Retrieve the part value from the hc_mt table based on question_id
        $part_query = "SELECT part FROM hc_mt WHERE question_id = $question_id";
        $part_result = $conn->query($part_query);
        if ($part_result->num_rows > 0) {
            $part_row = $part_result->fetch_assoc();
            $part = $conn->real_escape_string($part_row['part']);
        } else {
            $part = 'Unknown'; // Default value if part is not found
        }

        $sql = "INSERT INTO ma_tr (assessment_id, question_id, user_id, response, response_date, part) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisss", $assessment_id, $question_id, $user_id, $response, $response_date, $part);
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error . "<br>";
        }
		$sql = "INSERT INTO ma_tr_analysis (assessment_id, question_id, user_id, response, response_date, part) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisss", $assessment_id, $question_id, $user_id, $response, $response_date, $part);
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error . "<br>";
        }
        $stmt->close();
    }
}

// Optionally update the assessments table
$update_assessment_sql = "UPDATE assessments SET status = 'completed' WHERE assessment_id = ?";
$stmt_update = $conn->prepare($update_assessment_sql);
$stmt_update->bind_param("i", $assessment_id);
if (!$stmt_update->execute()) {
    echo "Error updating assessment: " . $stmt_update->error . "<br>";
}
$stmt_update->close();

$conn->close();

echo "<script>
    alert('Submission successful!');
    window.location.href = '1_overview_flow.php';
</script>";
?>
