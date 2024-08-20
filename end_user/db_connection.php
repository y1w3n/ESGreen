<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esg_assessment";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getOptions($conn, $type) {
    $options = '';
    $sql = "SELECT value FROM geninfo_mt WHERE type='$type'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $options .= '<option value="'.$row['value'].'">'.$row['value'].'</option>';
        }
    } else {
        echo "No results found for type: " . $type;
    }
    return $options;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Guest';

// Initialize variables
$company_name = 'Admin';
$is_admin = false;

// Check if user is logged in
if ($user_id !== 'Guest') {
    // Prepare and execute SQL query to get the role from the users table
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();
    $stmt->close();

    // Check if the user is an admin
    $is_admin = ($role === 'admin');

    // If the user is not an admin, fetch the company_name from geninfo_tr
    if (!$is_admin) {
        $stmt = $conn->prepare("SELECT company_name FROM geninfo_tr WHERE user_id = ?");
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($company_name);
        if (!$stmt->fetch()) {
            $company_name = 'Admin';
        }
        $stmt->close();
    }
    echo "<script>console.log('Company Name: " . htmlspecialchars($company_name) . "');</script>";
    echo "<script>console.log('Role: " . htmlspecialchars($role) . "');</script>";
}
?>
