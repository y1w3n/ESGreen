<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php"); // Ensure this file exists and is correctly named

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Check if email already exists
    $check_email_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $check_email_stmt->store_result();

    if ($check_email_stmt->num_rows > 0) {
        echo "Error: Email already exists.";
        $check_email_stmt->close();
        $conn->close();
        exit();
    }
    $check_email_stmt->close();

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die('prepare() failed: ' . htmlspecialchars($conn->error));
    }
    $bind = $stmt->bind_param("sss", $email, $hashed_password, $role);
    if ($bind === false) {
        die('bind_param() failed: ' . htmlspecialchars($stmt->error));
    }
    $execute = $stmt->execute();
    if ($execute === false) {
        die('execute() failed: ' . htmlspecialchars($stmt->error));
    } else {
        // JavaScript to show the alert and redirect
        echo '<script type="text/javascript">';
        echo 'alert("Successfully registered!");';
        echo 'window.location.href = "index.html";';
        echo '</script>';
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
