<?php
require 'assets/vendor/autoload.php'; // Ensure you have the autoload file from Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Database credentials
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

    // Function to add data to a worksheet
    function addDataToSheet($spreadsheet, $data, $sheetName) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($sheetName);

        if (!empty($data['data'])) {
            // Set headers
            $sheet->fromArray($data['headers'], NULL, 'A1');

            // Set rows
            $sheet->fromArray($data['data'], NULL, 'A2');
        }
    }

    // Create a new Spreadsheet object
    $spreadsheet = new Spreadsheet();

    // Fetch data from tables
    $assessments = fetchTableData($pdo, 'SELECT * FROM assessments');
    $geninfo_tr = fetchTableData($pdo, 'SELECT * FROM geninfo_tr');

    // Prefix columns with table names
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

    $prefixedAssessments = prefixTableColumns('assessments', $assessments);
    $prefixedGeninfo = prefixTableColumns('geninfo_tr', $geninfo_tr);

    // Merge the prefixed data based on user_id
    $mergedAssessmentsGeninfo = [];
    foreach ($prefixedAssessments as $assessmentRow) {
        $user_id = $assessmentRow['assessments_user_id'];
        $mergedRow = $assessmentRow;
        
        foreach ($prefixedGeninfo as $geninfoRow) {
            if ($geninfoRow['geninfo_tr_user_id'] === $user_id) {
                $mergedRow = array_merge($mergedRow, $geninfoRow);
                break; // Found the matching geninfo row, no need to continue
            }
        }
        $mergedAssessmentsGeninfo[] = $mergedRow;
    }

    // Prepare headers and data for the merged sheet
    $mergedHeaders = !empty($mergedAssessmentsGeninfo) ? array_keys($mergedAssessmentsGeninfo[0]) : [];
    $mergedData = [
        'headers' => $mergedHeaders,
        'data' => $mergedAssessmentsGeninfo,
    ];

    // Fetch other tables
    $hc_mt = fetchTableData($pdo, 'SELECT * FROM hc_mt');
    $hc_tr_analysis = fetchTableData($pdo, 'SELECT * FROM hc_tr_analysis');
    $hc_tr_analysis_calc = fetchTableData($pdo, 'SELECT * FROM hc_tr_analysis_calc');
    $hc_tr_analysis_overall = fetchTableData($pdo, 'SELECT * FROM hc_tr_analysis_overall');

    // Merge hc_tr_analysis and hc_tr_analysis_calc
    function mergeTablesColumns($tableNames, ...$tables) {
        $merged = [];
        $headers = [];

        // Collect headers with table name prefix
        foreach ($tables as $index => $table) {
            if (!empty($table)) {
                foreach (array_keys($table[0]) as $column) {
                    $headers[] = $tableNames[$index] . '_' . $column;
                }
            }
        }

        // Collect data
        $rowsCount = max(array_map('count', $tables));
        for ($i = 0; $i < $rowsCount; $i++) {
            $row = [];
            foreach ($tables as $table) {
                if (isset($table[$i])) {
                    foreach ($table[$i] as $value) {
                        $row[] = $value;
                    }
                } else {
                    $row = array_merge($row, array_fill(0, count($headers) / count($tables), null));
                }
            }
            $merged[] = $row;
        }

        return ['headers' => $headers, 'data' => $merged];
    }

    $merged_hc = mergeTablesColumns(['hc_tr_analysis', 'hc_tr_analysis_calc'], $hc_tr_analysis, $hc_tr_analysis_calc);

    // Add hc_mt.question_type if it matches hc_tr_analysis.question_id
    if (!empty($hc_mt)) {
        $hc_mt_question_types = [];
        foreach ($hc_mt as $row) {
            $hc_mt_question_types[$row['question_id']] = $row['question_text'];
        }
        foreach ($merged_hc['data'] as &$row) {
            $question_id_index = array_search('hc_tr_analysis_question_id', $merged_hc['headers']);
            $hc_tr_analysis_question_id = $row[$question_id_index];
            $mt_question_type = isset($hc_mt_question_types[$hc_tr_analysis_question_id]) ? $hc_mt_question_types[$hc_tr_analysis_question_id] : null;
            array_splice($row, $question_id_index + 1, 0, $mt_question_type);
        }
        array_splice($merged_hc['headers'], $question_id_index + 1, 0, 'hc_mt_question_text');
    }

    // Save data to the spreadsheet
    addDataToSheet($spreadsheet, $mergedData, 'assessments_geninfo');
    addDataToSheet($spreadsheet, ['headers' => array_keys($hc_mt[0]), 'data' => $hc_mt], 'hc_mt');
    addDataToSheet($spreadsheet, $merged_hc, 'hc_combined'); // Save the merged data to a new sheet
    addDataToSheet($spreadsheet, ['headers' => array_keys($hc_tr_analysis_overall[0]), 'data' => $hc_tr_analysis_overall], 'hc_tr_analysis_overall');

    // Save the spreadsheet to a file
    $writer = new Xlsx($spreadsheet);
    $outputFilePath = 'output3.xlsx';
    $writer->save($outputFilePath);

    echo "Data pipeline completed successfully. Excel file saved to $outputFilePath";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
