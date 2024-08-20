<?php
session_start();
$_SESSION['assessment_type'] = 'ma';

include('db_connection.php'); // include your database connection

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    die('User ID not set in session.');
}

// Fetch the industry from the geninfo_tr table for the user
$user_id = $_SESSION['user_id'];
$sql = "SELECT industry FROM geninfo_tr WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($industry);
$stmt->fetch();
$stmt->close();

// Debugging information
if (empty($industry)) {
    die("Industry not found for user ID: " . $user_id);
}
$_SESSION['industry'] = $industry; // Set the industry in the session

// Fetch questions from the database based on industry
$sql = "SELECT question_id, question_text, part, response_type FROM ma_mt WHERE industry = ? ORDER BY part, question_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['industry']); // Using session variable here
$stmt->execute();
$result = $stmt->get_result();

// Debugging SQL query execution
if ($stmt->error) {
    die("Error executing query: " . $stmt->error);
}

// Fetch questions grouped by part
$questions_by_part = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions_by_part[$row['part']][] = $row;
    }
} else {
    $questions_by_part = null;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiality Assessment</title>
    <link rel="stylesheet" type="text/css" href="assets/css/3_styles.css" />
</head>
<body>
    <h1 id="title">Materiality Assessment</h1>
    <?php if ($questions_by_part) : ?>
        <form action="4_ma_submit.php" method="POST">
            <?php foreach ($questions_by_part as $part => $questions) : ?>
                <h2 class="part-title"><?php echo htmlspecialchars($part); ?></h2>
                <?php foreach ($questions as $question) : ?>
                    <div class="form-control">
                        <label for="question_<?php echo $question['question_id']; ?>">
                            <?php echo htmlspecialchars($question['question_text']); ?>
                        </label>
                        <?php if ($question['response_type'] == 'RANGE') : ?>
                            <div class="range-group">
                                <input type="range" id="question_<?php echo $question['question_id']; ?>" name="question_<?php echo $question['question_id']; ?>" min="1" max="10">
                            </div>
                        <?php elseif ($question['response_type'] == 'TEXT') : ?>
                            <div class="text-group">
                                <textarea id="question_<?php echo $question['question_id']; ?>" name="question_<?php echo $question['question_id']; ?>"></textarea>
                            </div>
                        <?php endif; ?>
                        <!-- Include part as a hidden field -->
                        <input type="hidden" name="part_<?php echo $question['question_id']; ?>" value="<?php echo htmlspecialchars($part); ?>">
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <div class="button-container">
                <button type="submit" value="submit" id="submit">Submit</button>
            </div>
        </form>
    <?php else : ?>
        <p>No questions found for the selected industry.</p>
    <?php endif; ?>
</body>
</html>
