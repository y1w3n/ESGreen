<?php
session_start();
include('db_connection.php'); // include your database connection

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    die('User ID not set in session.');
}

$user_id = $_SESSION['user_id'];

// Fetch the role of the user from the users table
$sql = "SELECT role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

$is_admin = ($role === 'admin');

if ($is_admin && isset($_POST['industry'])) {
    // If admin and industry is selected
    $industry = $_POST['industry'];
} elseif (!$is_admin) {
    // Fetch the industry from the geninfo_tr table for the user
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
    $_SESSION['industry'] = $industry;
} else {
    // If admin but no industry is selected yet
    $industry = null;
}

// Fetch all industries from geninfo_mt table where type is 'industry'
$sql = "SELECT DISTINCT value FROM geninfo_mt WHERE type = 'industry' ORDER BY value";
$result = $conn->query($sql);

$industries = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $industries[] = $row['value'];
    }
}

// Define parts of the questionnaire
$parts = ['Environmental', 'Social', 'Governance'];

// Determine which part of the questionnaire to display based on the query parameter or default to Environmental
$part_to_display = isset($_GET['part']) ? $_GET['part'] : 'Environmental';
$current_part_index = array_search($part_to_display, $parts);

// Fetch the questions based on the selected industry and part
if (!empty($industry) && !empty($part_to_display)) {
    $sql = "
        SELECT question_id, question_text, part, subpart 
        FROM hc_mt 
        WHERE FIND_IN_SET(?, industry) AND part = ?
        ORDER BY subpart, question_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $industry, $part_to_display);
    $stmt->execute();
    $result = $stmt->get_result();

    // Debugging SQL query execution
    if ($stmt->error) {
        die("Error executing query: " . $stmt->error);
    }

    // Fetch questions grouped by subpart
    $questions_by_subpart = [];
    $total_questions = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $questions_by_subpart[$row['subpart']][] = $row;
            $total_questions++;
        }
    } else {
        $questions_by_subpart = null;
    }

    $stmt->close();
} else {
    $questions_by_subpart = null;
    $total_questions = 0;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESG Healthcheck Assessment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/3_styles.css" />
    <script>
        function toggleQuestions(subpart) {
            var container = document.getElementById('questions-' + subpart);
            var icon = document.getElementById('icon-' + subpart);
            if (container.style.display === 'none' || container.style.display === '') {
                container.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                container.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        function validateForm() {
            var valid = true;
            var subparts = document.querySelectorAll('.questions-container');
            subparts.forEach(function(subpart) {
                var questions = subpart.querySelectorAll('.form-control');
                questions.forEach(function(question) {
                    var radios = question.querySelectorAll('input[type="radio"]');
                    var answered = false;
                    radios.forEach(function(radio) {
                        if (radio.checked) {
                            answered = true;
                        }
                    });
                    if (!answered) {
                        valid = false;
                    }
                });
            });
            if (!valid) {
                alert('Please answer all questions before submitting.');
            }
            return valid;
        }

        function updateProgress() {
            var totalQuestions = <?php echo $total_questions; ?>;
            var answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
            var progress = (answeredQuestions / totalQuestions) * 100;
            var progressBar = document.querySelector('.progress-bar');
            progressBar.style.width = progress + '%';
            progressBar.textContent = Math.round(progress) + '%';
        }

        document.addEventListener('DOMContentLoaded', function() {
            var radios = document.querySelectorAll('input[type="radio"]');
            radios.forEach(function(radio) {
                radio.addEventListener('change', updateProgress);
            });

            // Initial progress update
            updateProgress();

            var submitButton = document.getElementById('submit');
            if (submitButton) {
                submitButton.addEventListener('click', function(event) {
                    if (!validateForm()) {
                        event.preventDefault();
                    }
                });
            }

            // Handle scroll to update progress bar position
            var progressBarContainer = document.querySelector('.progress-bar-container');
            var scrollThreshold = 300; // Set the threshold value as needed

            window.addEventListener('scroll', function() {
                if (window.scrollY >= scrollThreshold) {
                    progressBarContainer.classList.add('fixed');
                } else {
                    progressBarContainer.classList.remove('fixed');
                }
            });
        });
    </script>
</head>
<body>
    <h1 id="title">ESG Healthcheck Assessment</h1>

    <div class="tabs">
        <a href="?part=Environmental" class="tab environmental <?php echo $part_to_display == 'Environmental' ? 'active' : ''; ?>">Environmental</a>
        <a href="?part=Social" class="tab social <?php echo $part_to_display == 'Social' ? 'active' : ''; ?>">Social</a>
        <a href="?part=Governance" class="tab governance <?php echo $part_to_display == 'Governance' ? 'active' : ''; ?>">Governance</a>
    </div>
    <div class="progress-bar-container">
        <div class="progress-bar" style="width: 0;">
            0%
        </div>
    </div>

    <?php if ($is_admin) : ?>
        <form method="POST">
            <label for="industry">Select Industry:</label>
            <select name="industry" id="industry">
                <option value="" disabled selected>Select Industry</option>
                <?php 
                foreach ($industries as $ind) : ?>
                    <option value="<?php echo htmlspecialchars($ind); ?>" <?php if (isset($industry) && $industry == $ind) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($ind); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Select Industry</button>
        </form>
    <?php endif; ?>

    <?php if ($questions_by_subpart) : ?>
        <form action="3_hc_submit.php" method="POST">
            <div class="part-container <?php echo strtolower($part_to_display); ?>">
                <?php foreach ($questions_by_subpart as $subpart => $questions) : ?>
                    <div class="subpart-container">
                        <h3 class="subpart-title" onclick="toggleQuestions('<?php echo $subpart; ?>')">
                            <?php echo htmlspecialchars($subpart); ?>
                            <i id="icon-<?php echo $subpart; ?>" class="fas fa-chevron-down"></i>
                        </h3>
                        <div id="questions-<?php echo $subpart; ?>" class="questions-container" style="display: none;">
                            <?php foreach ($questions as $question) : ?>
                                <div class="form-control">
                                    <label for="question_<?php echo $question['question_id']; ?>">
                                        <?php echo htmlspecialchars($question['question_text']); ?>
                                    </label>
                                    <div class="radio-group">
                                        <label for="question_<?php echo $question['question_id']; ?>_yes">
                                            <input type="radio" id="question_<?php echo $question['question_id']; ?>_yes" name="question_<?php echo $question['question_id']; ?>" value="YES"> Yes
                                        </label>
                                        <label for="question_<?php echo $question['question_id']; ?>_no">
                                            <input type="radio" id="question_<?php echo $question['question_id']; ?>_no" name="question_<?php echo $question['question_id']; ?>" value="NO"> No
                                        </label>
                                    </div>
                                    <!-- Include part and subpart as hidden fields -->
                                    <input type="hidden" name="part_<?php echo $question['question_id']; ?>" value="<?php echo htmlspecialchars($part_to_display); ?>">
                                    <input type="hidden" name="subpart_<?php echo $question['question_id']; ?>" value="<?php echo htmlspecialchars($subpart); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="button-container">
                <?php if ($current_part_index > 0): ?>
                    <a href="?part=<?php echo $parts[$current_part_index - 1]; ?>" class="nav-button">Previous</a>
                <?php endif; ?>               
                <?php if ($current_part_index < count($parts) - 1): ?>
                    <a href="?part=<?php echo $parts[$current_part_index + 1]; ?>" class="nav-button">Next</a>
                <?php endif; ?>            
            </div>
            <div class="button-container">
                <?php if (!$is_admin): ?>
                    <button type="submit" id="submit">Submit</button>
                <?php endif; ?>
                <button><a href="1_overview_flow.php" class="back-button">Back</a></button>
            </div>
        </form>
    <?php elseif (!$is_admin) : ?>
        <p>No questions found for the selected part.</p>
    <?php endif; ?>
</body>
</html>
