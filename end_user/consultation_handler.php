<?php
session_start();
include('db_connection.php');


// Check if assessment_id is set in POST request
if (isset($_POST['assessment_id']) || !isset($_POST['assessment_id'])) {
    $assessment_id = $_SESSION['assessment_id'];
} else {
    // die("Assessment ID not specified.");
    $assessment_id = $_SESSION['assessment_id'];
}

// Fetch the user_id from the assessments table
$user_id_sql = "SELECT user_id FROM assessments WHERE assessment_id = ?";
if ($user_stmt = $conn->prepare($user_id_sql)) {
    $user_stmt->bind_param("i", $assessment_id);
    $user_stmt->execute();
    $user_stmt->bind_result($user_id);
    $user_stmt->fetch();
    $user_stmt->close();

    // Check if user_id was found
    if ($user_id) {
        // Insert into the consultation table, including user_id
        $sql = "INSERT INTO consultation (assessment_id, consultation_date, user_id) 
                VALUES (?, NOW(), ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $assessment_id, $user_id);
            if ($stmt->execute()) {
                // Insertion successful
                echo "Consultation request recorded.";
				echo "<script>
   					alert('We will contact you soon!');
    				window.location.href = '1_overview_flow.php';
					</script>";
            } else {
                // Insertion failed
                echo "Execute error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Preparation failed
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
    } else {
        // user_id not found for the given assessment_id
        echo "No user found for the given assessment ID.";
    }
} else {
    // Preparation of user_id query failed
    echo "User ID query preparation failed: (" . $conn->errno . ") " . $conn->error;
}

$conn->close();
?>
