<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST data: ";
    print_r($_POST);
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$questionText = isset($_POST['question_text']) ? $conn->real_escape_string($_POST['question_text']) : '';
$responseType = isset($_POST['response_type']) ? $conn->real_escape_string($_POST['response_type']) : '';
$part = isset($_POST['part']) ? $conn->real_escape_string($_POST['part']) : '';
$industry = isset($_POST['industry']) ? $conn->real_escape_string($_POST['industry']) : '';

if ($id === null) {
    $sql = "INSERT INTO ma_mt (question_text, response_type, part, industry) VALUES ('$questionText', '$responseType', '$part', '$industry')";
} else {
    $sql = "UPDATE ma_mt SET question_text = '$questionText', response_type = '$responseType', part = '$part', industry = '$industry' WHERE id = $id";
}

if ($conn->query($sql) === TRUE) {
    echo "Record saved successfully";
	header("Location: 6_ma_mt.php");
} else {
    echo "Error saving record: " . $conn->error;
}

$conn->close();
?>
