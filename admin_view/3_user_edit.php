<?php
session_start();
include('db_connection.php');

if (!isset($_GET['user_id'])) {
    header('Location: 3_user.php');
    exit();
}
$id = $_GET['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = $conn->real_escape_string($_POST['password']);

    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET email = ?, role = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $email, $role, $password, $id);
    } else {
        $sql = "UPDATE users SET email = ?, role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $email, $role, $id);
    }

    if ($stmt->execute()) {
        header('Location: 3_user.php');
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

$sql = "SELECT user_id, email, role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "No user found with ID: " . htmlspecialchars($id);
    $user = ['email' => '', 'role' => ''];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
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
                <li><a href="5_geninfo_tr.php">Users Background</a></li>
                <li><a href="6_ma_mt.php">Materiality Assessment Questions</a></li>
                <li><a href="7_hc_mt.php">Health Check Assessment Questions</a></li>
                <li><a href="8_qs_tr.php">Users Submissions Status</a></li>
                <li><a href="9_rp_vis.html">Reports</a></li>
				<li><a href="10_dev.html">Development Portal</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <header class="header">
				<img src="assets/images/esgreen.png" alt="Logo 1" class="logo1">
				<img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
				<h1>Edit User</h1>
            </header>
            <div class="content">
                <form action="3_user_edit.php?user_id=<?php echo $id; ?>" method="post">
                    <div class="form-control">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-control">
                        <label for="role">Role</label>
                        <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($user['role']); ?>" required>
                    </div>
                    <div class="form-control">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password">
                        <small>Leave blank if you don't want to change the password</small>
                    </div>
                    <div class="button-container">
                        <button type="submit">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
