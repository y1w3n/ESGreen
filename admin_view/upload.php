<?php
require 'PhpSpreadsheet-2.1.0/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    $conn = new mysqli("your_server", "your_username", "your_password", "your_database");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    foreach ($data as $row) {
        $column1 = $row[0];
        $column2 = $row[1];

        $sql = "INSERT INTO factsubmission (column1, column2) VALUES ('$column1', '$column2')";

        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
} else {
    echo "No file uploaded.";
}
?>
