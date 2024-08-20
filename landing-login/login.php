<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE email = ?");
    if ($stmt === false) {
        error_log('prepare() failed: ' . htmlspecialchars($conn->error));
        die('An error occurred. Please try again later.');
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $role);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = $role;

            // Determine the redirect URL based on role
            if ($role === 'admin') {
                header("Location: ../admin_view/1_main.html");
            } else {
                header("Location: ../end_user/2_geninfo_gui.php");
            }
            exit();
        } else {
            echo "Error: Incorrect password.";
        }
    } else {
        echo "Error: No user found with that email.";
    }

    $stmt->close();
    $conn->close();
}
?>
