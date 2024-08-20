<?php
session_start();
include('db_connection.php'); 

if (!isset($_GET['id'])) {
    header('Location: 3_user.php');
    exit();
}

$id = intval($_GET['id']);

$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header('Location: 3_user.php');
exit();
?>
