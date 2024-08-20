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
?>
