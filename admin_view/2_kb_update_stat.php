<?php
include('db_connection.php');

if (isset($_POST['consultation_id']) && isset($_POST['new_status'])) {
    $consultation_id = mysqli_real_escape_string($conn, $_POST['consultation_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);

    $sql = "UPDATE consultation SET progress = '$new_status' WHERE consultation_id = '$consultation_id'";

    if ($conn->query($sql) === TRUE) {
        $response = array('status' => 'success', 'message' => 'Task status updated successfully');
        echo json_encode($response);
    } else {
        $response = array('status' => 'error', 'message' => 'Error updating task status: ' . $conn->error);
        echo json_encode($response);
    }
} else {
    $response = array('status' => 'error', 'message' => 'Missing consultation_id or new_status parameters');
    echo json_encode($response);
}

$conn->close();
?>
