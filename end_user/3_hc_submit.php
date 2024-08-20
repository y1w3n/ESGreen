<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
$_SESSION['assessment_type'] = 'hc';
include('db_connection.php'); 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    die("Error: user_id is not set in the session.");
}

$user_id = $_SESSION['user_id'];
$assessment_type = $_SESSION['assessment_type']; 

if (!isset($_SESSION['assessment_id']) || isset($_SESSION['assessment_id'])) {
    $status = "in progress"; // Initial status
    $assessment_date = date('Y-m-d H:i:s');
    
    // Insert a new assessment
    $sql_insert = "INSERT INTO assessments (user_id, assessment_date, status, assessment_type) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isss", $user_id, $assessment_date, $status, $_SESSION['assessment_type']);
    
    if ($stmt_insert->execute()) {
        $_SESSION['assessment_id'] = $stmt_insert->insert_id;
        echo "New assessment created successfully. Assessment ID: " . $_SESSION['assessment_id'] . "<br>";
    } else {
        die("Error creating new assessment: " . $stmt_insert->error);
    }
    
    $stmt_insert->close();
}

$assessment_id = $_SESSION['assessment_id'];

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
        
        // Retrieve the part and subpart values from the hc_mt table based on question_id
        $part_query = "SELECT part, subpart FROM hc_mt WHERE question_id = $question_id";
        $part_result = $conn->query($part_query);
        if ($part_result->num_rows > 0) {
            $part_row = $part_result->fetch_assoc();
            $part = $conn->real_escape_string($part_row['part']);
            $subpart = $conn->real_escape_string($part_row['subpart']);
        } else {
            $part = 'Unknown'; // Default value if part is not found
            $subpart = 'Unknown'; // Default value if subpart is not found
        }

        $sql = "INSERT INTO hc_tr (assessment_id, question_id, user_id, response, response_date, part, subpart) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiissss", $assessment_id, $question_id, $user_id, $response, $response_date, $part, $subpart);
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error . "<br>";
        }
        
        $sql_analysis = "INSERT INTO hc_tr_analysis (assessment_id, question_id, user_id, response, response_date, part, subpart) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_analysis);
        $stmt->bind_param("iiissss", $assessment_id, $question_id, $user_id, $response, $response_date, $part, $subpart);
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

// Processing script from 5_calc.php

// Fetch data from the database for the current assessment_id
$sql = "SELECT response_id, part, subpart, response FROM hc_tr_analysis WHERE assessment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
if ($result->num_rows > 0) {
    // Convert query result to an array
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Insert processed data into the hc_tr_analysis_calc table
$stmt = $conn->prepare("INSERT INTO hc_tr_analysis_calc (assessment_id, part, score, original_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isii", $assessment_id, $part, $score, $original_id);

foreach ($data as $row) {
    $part = $row['part'];
    $score = ($row['response'] == 'YES') ? 1 : 0;
    $original_id = $row['response_id'];
    if (!$stmt->execute()) {
        // Log any errors during insertion
        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/capstone/end_user/log.txt', "Error inserting into hc_tr_analysis_calc: " . $stmt->error . "\n", FILE_APPEND);
    } else {
        // Log successful insertions
        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/capstone/end_user/log.txt', "Successfully inserted into hc_tr_analysis_calc: " . print_r($row, true) . "\n", FILE_APPEND);
    }
}

$stmt->close();

// Calculate overall scores and insert into hc_tr_analysis_overall table
$sql = "SELECT part, COUNT(*) AS total_questions, SUM(score) AS overall_score FROM hc_tr_analysis_calc WHERE assessment_id = ? GROUP BY part";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Prepare insert statement for hc_tr_analysis_overall table
    $stmt_overall = $conn->prepare("INSERT INTO hc_tr_analysis_overall (assessment_id, part, overall_score, percentage_score, category) VALUES (?, ?, ?, ?, ?)");
    $stmt_overall->bind_param("isdds", $assessment_id, $part, $overall_score, $percentage_score, $category);

    while ($row = $result->fetch_assoc()) {
        $part = $row['part'];
        $overall_score = $row['overall_score'];
        $total_questions = $row['total_questions'];
        $percentage_score = ($overall_score / $total_questions) * 100;

        // Debug statements
        echo "Part: " . $part . "<br>";
        echo "Overall Score: " . $overall_score . "<br>";
        echo "Total Questions: " . $total_questions . "<br>";
        echo "Percentage Score: " . $percentage_score . "<br>";

        // Categorize the score
        if ($percentage_score < 40) {
            $category = 'Startup';
        } elseif ($percentage_score >= 40 && $percentage_score < 70) {
            $category = 'Emerging';
        } else {
            $category = 'Mature';
        }

        echo "Category: " . $category . "<br>";

        if (!$stmt_overall->execute()) {
            file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/capstone/end_user/log.txt', "Error inserting into hc_tr_analysis_overall: " . $stmt_overall->error . "\n", FILE_APPEND);
        } else {
            file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/capstone/end_user/log.txt', "Successfully inserted into hc_tr_analysis_overall: " . print_r($row, true) . "\n", FILE_APPEND);
        }
    }

    $stmt_overall->close();
} else {
    echo "No data found for the specified assessment_id.";
}

$stmt->close();
$conn->close();

echo "<script>
    alert('Submission successful!');
    window.location.href = '5_hc_pipeline.php';
</script>";
?>
