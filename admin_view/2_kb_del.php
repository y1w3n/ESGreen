<?php
include('db_connection.php');

$consultation_id = $_POST['consultation_id'];
$sql = "DELETE FROM consultation WHERE consultation_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $consultation_id);

$response = array();

if ($stmt->execute()) {
    $response['status'] = 'success';
    $response['message'] = 'Consultation deleted successfully.';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error deleting consultation: ' . $conn->error;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
