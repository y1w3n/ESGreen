<?php
session_start();
include('db_connection.php');

if (isset($_POST['delete_question'])) {
    $questionId = (int)$_POST['delete_question'];
    $deleteQuery = "DELETE FROM ma_mt WHERE question_id = $questionId";
    $conn->query($deleteQuery);
}

$conn->close();

header("Location: 6_ma_mt.php");
exit();
?>
