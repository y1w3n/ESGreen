<?php
session_start();
include('db_connection.php');

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM geninfo_mt WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("No record found with ID $id");
    }

    $entry = $result->fetch_assoc();
} else {
    die("ID parameter missing or empty in URL.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $fields = [];

    if (!empty($_POST['type'])) {
        $type = $_POST['type'];
        $fields[] = "type = ?";
    }

    if (!empty($_POST['value'])) {
        $value = $_POST['value'];
        $fields[] = "value = ?";
    }

    if (count($fields) > 0) {
        $sql = "UPDATE geninfo_mt SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $params = [];

        if (!empty($type)) {
            $params[] = &$type;
        }

        if (!empty($value)) {
            $params[] = &$value;
        }

        $params[] = &$id;
        $stmt->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);

        if ($stmt->execute()) {
            echo "Record updated successfully!";
            header("Location: 4_geninfo_mt.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "No fields to update.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Master Option</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
		<aside class="sidebar">
			<ul class="sidebar-menu">
                <li><a href="1_main.html">Overview</a></li>
                <li><a href="2_kb.php">Tasks</a></li>
				<li><a href="../end_user/1_overview_flow.php">Client Portal</a></li>
                <li><a href="3_user.php">Users Management</a></li>
                <li><a href="4_geninfo_mt.php">Generic Information Master Data</a></li>
x                <li><a href="6_ma_mt.php">Materiality Assessment Questions</a></li>
                <li><a href="7_hc_mt.php">Health Check Assessment Questions</a></li>
                <li><a href="8_qs_tr.php">Users Submissions Status</a></li>
                <li><a href="9_rp_vis.html">Reports</a></li>
                <li><a href="10_dev.html">Development Portal</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <div class="header">
				<img src="assets/images/esgreen.png" alt="Logo 1" class="logo1">
				<h1>Users' Generic Information</h1>
				<div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </div>
            <div class="content">
                <div class="form-container">
                    <form method="POST" action="4_geninfo_mt_edit.php?id=<?php echo htmlspecialchars($entry['id']); ?>">
                        <input type="hidden" name="update" value="1">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($entry['id']); ?>">
                        <input type="text" name="type" value="<?php echo htmlspecialchars($entry['type']); ?>" placeholder="Type">
                        <input type="text" name="value" value="<?php echo htmlspecialchars($entry['value']); ?>" placeholder="Value">
                        <button type="submit">Update</button>
                        <a href="4_geninfo_mt.php" class="back-button">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
