<?php
include('db_connection.php');

$sql = "SELECT consultation.consultation_id, consultation.assessment_id, consultation.progress, consultation.user_id, geninfo_tr.company_name
        FROM consultation
        LEFT JOIN geninfo_tr ON consultation.user_id = geninfo_tr.user_id";
$result = $conn->query($sql);

$consultations = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $consultations[] = array(
            'consultation_id' => $row['consultation_id'],
            'assessment_id' => $row['assessment_id'],
            'progress' => $row['progress'],
            'user_id' => $row['user_id'],
            'company_name' => $row['company_name']
        );
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($consultations);
?>
