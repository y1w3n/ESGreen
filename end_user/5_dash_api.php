<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db_connection.php'); // Include your database connection

header('Content-Type: application/json');

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;
if ($assessment_id === 0) {
    echo json_encode(["error" => "Invalid assessment_id"]);
    exit;
}

// Fetch data from as_geninfo_pl table
$sql_geninfo = "SELECT * FROM as_geninfo_pl WHERE assessments_assessment_id = ?";
$stmt_geninfo = $conn->prepare($sql_geninfo);
$stmt_geninfo->bind_param("i", $assessment_id);
$stmt_geninfo->execute();
$result_geninfo = $stmt_geninfo->get_result();
$geninfo_data = $result_geninfo->fetch_all(MYSQLI_ASSOC);

// Fetch data from hc_tr_analysis_overall table
$sql_analysis = "SELECT * FROM hc_tr_analysis_overall WHERE assessment_id = ?";
$stmt_analysis = $conn->prepare($sql_analysis);
$stmt_analysis->bind_param("i", $assessment_id);
$stmt_analysis->execute();
$result_analysis = $stmt_analysis->get_result();
$analysis_data = $result_analysis->fetch_all(MYSQLI_ASSOC);

// Fetch data from hc_pl table and summarize part, subpart, and score
$sql_questions = "SELECT hc_tr_analysis_part AS part, hc_tr_analysis_subpart AS subpart, hc_tr_analysis_calc_score AS score
                  FROM hc_pl WHERE hc_tr_analysis_assessment_id = ?";
$stmt_questions = $conn->prepare($sql_questions);
$stmt_questions->bind_param("i", $assessment_id);
$stmt_questions->execute();
$result_questions = $stmt_questions->get_result();
$questions_data = $result_questions->fetch_all(MYSQLI_ASSOC);

$response = [
    "geninfo" => $geninfo_data,
    "analysis" => $analysis_data,
    "questions" => $questions_data
];

echo json_encode($response);

$stmt_geninfo->close();
$stmt_analysis->close();
$stmt_questions->close();
$conn->close();
?>
