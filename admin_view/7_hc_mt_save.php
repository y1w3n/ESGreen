<?php
session_start();
include('db_connection.php');

if (isset($_POST['add_question'])) {
    if (isset($_POST['question_text']) && isset($_POST['response_type']) && isset($_POST['part'])) {
        $questionText = $conn->real_escape_string($_POST['question_text']);
        $responseType = $conn->real_escape_string($_POST['response_type']);
        $part = $conn->real_escape_string($_POST['part']);

        $insertQuery = "INSERT INTO hc_mt (question_text, response_type, part) VALUES ('$questionText', '$responseType', '$part')";
        if ($conn->query($insertQuery) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $insertQuery . "<br>" . $conn->error;
        }
    } else {
        echo "All fields are required for adding a question.";
    }
}

if (isset($_POST['save_question'])) {
    if (isset($_POST['question_id']) && isset($_POST['question_text']) && isset($_POST['response_type']) && isset($_POST['part'])) {
        $questionId = (int)$_POST['question_id'];
        $questionText = $conn->real_escape_string($_POST['question_text']);
        $responseType = $conn->real_escape_string($_POST['response_type']);
        $part = $conn->real_escape_string($_POST['part']);

        $updateQuery = "UPDATE hc_mt SET question_text = '$questionText', response_type = '$responseType', part = '$part' WHERE question_id = $questionId";
        if ($conn->query($updateQuery) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error: " . $updateQuery . "<br>" . $conn->error;
        }
    } else {
        echo "All fields are required for updating a question.";
    }
}

$conn->close();

header("Location: 7_hc_mt.php");
exit();
?>
