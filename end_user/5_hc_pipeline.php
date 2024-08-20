<?php
require 'assets/vendor/autoload.php'; // Ensure you have the autoload file from Composer

// Include PDO database credentials
$host = 'localhost';
$db = 'esg_assessment';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Function to fetch table data with a query
    function fetchTableData($pdo, $query) {
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Function to prefix table columns
    function prefixTableColumns($tableName, $tableData) {
        $prefixedData = [];
        foreach ($tableData as $row) {
            $prefixedRow = [];
            foreach ($row as $column => $value) {
                $prefixedRow[$tableName . '_' . $column] = $value;
            }
            $prefixedData[] = $prefixedRow;
        }
        return $prefixedData;
    }

    // Fetch and merge assessments, geninfo_tr, and consultations data
    $assessments = fetchTableData($pdo, 'SELECT * FROM assessments');
    $geninfo_tr = fetchTableData($pdo, 'SELECT * FROM geninfo_tr');
    $consultations = fetchTableData($pdo, 'SELECT * FROM consultation'); // Change table name to 'consultation'

    // Prefix columns with table names
    $prefixedAssessments = prefixTableColumns('assessments', $assessments);
    $prefixedGeninfo = prefixTableColumns('geninfo_tr', $geninfo_tr);
    $prefixedConsultations = prefixTableColumns('consultation', $consultations); // Change prefix to 'consultation'

    // Merge the data based on assessment_id
    $mergedAssessmentsGeninfo = [];
    foreach ($prefixedAssessments as $assessmentRow) {
        $user_id = $assessmentRow['assessments_user_id'];
        $assessment_id = $assessmentRow['assessments_assessment_id'];
        $mergedRow = $assessmentRow;

        foreach ($prefixedGeninfo as $geninfoRow) {
            if ($geninfoRow['geninfo_tr_user_id'] === $user_id) {
                $mergedRow = array_merge($mergedRow, $geninfoRow);
                break; // Found the matching geninfo row, no need to continue
            }
        }

        foreach ($prefixedConsultations as $consultationRow) {
            if ($consultationRow['consultation_assessment_id'] === $assessment_id) { // Change column name to 'consultation_assessment_id'
                $mergedRow = array_merge($mergedRow, $consultationRow);
                break; // Found the matching consultation row, no need to continue
            }
        }

        $mergedAssessmentsGeninfo[] = $mergedRow;
    }

    // Fetch hc_tr_analysis, hc_tr_analysis_calc, and hc_mt data
    $hc_tr_analysis = fetchTableData($pdo, 'SELECT * FROM hc_tr_analysis');
    $hc_tr_analysis_calc = fetchTableData($pdo, 'SELECT * FROM hc_tr_analysis_calc');
    $hc_mt = fetchTableData($pdo, 'SELECT * FROM hc_mt');

    // Prefix columns with table names
    $prefixedHcTrAnalysis = prefixTableColumns('hc_tr_analysis', $hc_tr_analysis);
    $prefixedHcTrAnalysisCalc = prefixTableColumns('hc_tr_analysis_calc', $hc_tr_analysis_calc);
    $prefixedHcMt = prefixTableColumns('hc_mt', $hc_mt);

    // Function to merge hc_tr_analysis and hc_tr_analysis_calc data with hc_mt question text
    function mergeTablesWithQuestionText($hc_tr_analysis, $hc_tr_analysis_calc, $hc_mt) {
        // Create a map for question_id to question_text
        $questionTextMap = [];
        foreach ($hc_mt as $row) {
            $questionTextMap[$row['hc_mt_question_id']] = $row['hc_mt_question_text'];
        }

        // Merge the tables
        $merged = [];
        foreach ($hc_tr_analysis as $index => $row) {
            $mergedRow = array_merge($row, $hc_tr_analysis_calc[$index] ?? []);
            $question_id = $row['hc_tr_analysis_question_id'];
            $mergedRow['hc_mt_question_text'] = $questionTextMap[$question_id] ?? null;
            $merged[] = $mergedRow;
        }

        return $merged;
    }

    $merged_hc = mergeTablesWithQuestionText($prefixedHcTrAnalysis, $prefixedHcTrAnalysisCalc, $prefixedHcMt);

    // Insert mergedAssessmentsGeninfo into as_geninfo_pl table
    $tableName = 'as_geninfo_pl';
    foreach ($mergedAssessmentsGeninfo as $row) {
        $columns = array_keys($row);
        $placeholders = rtrim(str_repeat('?,', count($columns)), ',');
        $insertQuery = "INSERT INTO $tableName (" . implode(',', $columns) . ") VALUES ($placeholders)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute(array_values($row));
    }

    // Insert merged_hc into hc_pl table
    $tableName = 'hc_pl';
    foreach ($merged_hc as $row) {
        $columns = array_keys($row);
        $placeholders = rtrim(str_repeat('?,', count($columns)), ',');
        $insertQuery = "INSERT INTO $tableName (" . implode(',', $columns) . ") VALUES ($placeholders)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute(array_values($row));
    }

    echo "Data pipeline completed successfully. Merged data inserted into SQL tables.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Fetch or create assessment_id
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Error: user_id is not set in the session.");
}

$user_id = $_SESSION['user_id'];
$assessment_type = $_SESSION['assessment_type'] ?? 'hc';

$assessment_id = $_SESSION['assessment_id'];

// Redirect to the dashboard with the assessment_id as a parameter
echo "<script>
    alert('Submission successful!');
    window.location.href = '5_dash.html?assessment_id=" . $_SESSION['assessment_id'] . "';
</script>";
?>
