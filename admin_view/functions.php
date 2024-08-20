<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esg_assessment";

function getOptions($conn, $type, $selectedValue = '') {
    $options = '';
    $sql = "SELECT value FROM geninfo_mt WHERE type='$type'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $isSelected = ($row['value'] == $selectedValue) ? 'selected' : '';
            $options .= '<option value="'.$row['value'].'" '.$isSelected.'>'.$row['value'].'</option>';
        }
    }
    return $options;
}

// You can also define a function to establish a database connection
function getDbConnection() {
    global $servername, $username, $password, $dbname;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>
