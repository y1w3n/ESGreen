<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['assessment_type'])) {
    die("Error: assessment_type is not set in the session.");
}
$assessment_type = $_SESSION['assessment_type'];

$table = '';
if ($assessment_type === 'ma') {
    $table = 'ma_tr';
} elseif ($assessment_type === 'hc') {
    $table = 'hc_tr';
} else {
    die("Error: Invalid assessment type.");
}

$data = json_decode(file_get_contents('php://input'), true);

$response_ids = array_keys($data);
$responses = array_values($data);

for ($i = 0; $i < count($response_ids); $i++) {
    $response_id = $response_ids[$i];
    $response = $responses[$i];

    $sql = "UPDATE $table SET response = ? WHERE response_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("si", $response, $response_id);

    if (!$stmt->execute()) {
        echo "Error updating entry: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();

echo "Responses updated successfully.";
?>
