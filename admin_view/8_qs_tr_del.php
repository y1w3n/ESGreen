<?php
session_start();
include('db_connection.php');

$id = $_GET['id'];

$sql = "DELETE FROM 8_qs_tr WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "Entry deleted successfully.";
} else {
    echo "Error deleting entry: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: 8_qs_tr.php");
exit();
?>
