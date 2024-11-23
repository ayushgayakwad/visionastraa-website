<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
$host = 'localhost';
$db = 'u707137586_UserAccounts';
$user = 'u707137586_UserAccounts';
$pass = 'egtA*XgA+J>2';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize or retrieve the question index from session
if (!isset($_SESSION['questions_answered'])) {
    $_SESSION['questions_answered'] = [];
}

// Handle form submission (store answers, calculate total marks)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Store the user's answer to the current question
    if (isset($_POST['answers'])) {
        $question_id = $_POST['question_id'];
        $_SESSION['answers'][$question_id] = $_POST['answers'];  // Store answer for this question
    }

    // Calculate the total marks after the last question
    if (isset($_POST['submit_quiz'])) {
        $total_marks = 0;
        foreach ($_SESSION['answers'] as $qid => $answer) {
            // Fetch the correct answer and marks for the question
            $stmt = $pdo->prepare("SELECT correct_option, marks FROM quiz_questions WHERE id = ?");
            $stmt->execute([$qid]);
            $question = $stmt->fetch();

            if ($question) {
                // If the answer is correct, add the marks, otherwise add 0
                if ($question['correct_option'] == $answer) {
                    $total_marks += $question['marks'];
                }
            }
        }

        // Store the total marks in the database
        $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, total_marks) VALUES (?, ?)");
        $stmt->execute([$user_id, $total_marks]);

        // Redirect to a result page
        $_SESSION['quiz_submitted'] = true;
        header("Location: dashboard.php");
        exit();
    }
}

// Get all question IDs and shuffle them to get a random order
$question_ids = range(1, 30);  // IDs for 30 questions
shuffle($question_ids);  // Shuffle the array to get a random order

// Store shuffled question order in the session
if (!isset($_SESSION['question_order'])) {
    $_SESSION['question_order'] = $question_ids;
}

// Initialize or update the current question index
if (!isset($_SESSION['current_question_index'])) {
    $_SESSION['current_question_index'] = 0;  // Start with the first question
}

// Get the current question ID
$current_question_index = $_SESSION['current_question_index'];
$current_question_id = $_SESSION['question_order'][$current_question_index];

// Fetch the question data from the database
$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE id = ?");
$stmt->execute([$current_question_id]);
$question = $stmt->fetch();

// Handle navigation (Next and Previous)
if (isset($_GET['next'])) {
    $_SESSION['current_question_index']++;
    header("Location: quiz.php");
    exit();
}

if (isset($_GET['previous']) && $_SESSION['current_question_index'] > 0) {
    $_SESSION['current_question_index']--;
    header("Location: quiz.php");
    exit();
}

// Set a default timer for 30 minutes (1800 seconds)
if (!isset($_SESSION['quiz_start_time'])) {
    $_SESSION['quiz_start_time'] = time(); // Mark quiz start time
}

// Calculate remaining time
$time_left = 1800 - (time() - $_SESSION['quiz_start_time']);
if ($time_left <= 0) {
    // If time is up, submit the quiz automatically
    $_SESSION['quiz_expired'] = true;
    // Calculate and store marks as if the quiz is submitted
    header("Location: quiz.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electric Vehicle Quiz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1c1c1c;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #333;
            border-radius: 10px;
        }
        h1 {
            text-align: center;
        }
        .question {
            margin-bottom: 20px;
        }
        .options {
            margin-bottom: 20px;
        }
        .options label {
            display: block;
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            background-color: #56a3ff;
            padding: 10px 20px;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #1c77cc;
        }
        .timer {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Electric Vehicle Quiz</h1>
        
        <!-- Timer -->
        <div class="timer">
            <p>Time left: <span id="timer"></span></p>
        </div>

        <form method="POST">
            <div class="question">
                <p><strong>Question <?php echo $current_question_index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></strong></p>
            </div>

            <div class="options">
                <label><input type="radio" name="answers" value="1" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 1) ? 'checked' : ''; ?>> <?php echo htmlspecialchars($question['option_1']); ?></label>
                <label><input type="radio" name="answers" value="2" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 2) ? 'checked' : ''; ?>> <?php echo htmlspecialchars($question['option_2']); ?></label>
                <label><input type="radio" name="answers" value="3" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 3) ? 'checked' : ''; ?>> <?php echo htmlspecialchars($question['option_3']); ?></label>
                <label><input type="radio" name="answers" value="4" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 4) ? 'checked' : ''; ?>> <?php echo htmlspecialchars($question['option_4']); ?></label>
            </div>

            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">

            <!-- Display 'Next' or 'Previous' button -->
            <div class="btn-container">
                <?php if ($current_question_index > 0): ?>
                    <a href="quiz.php?previous=true" class="btn">Previous</a>
                <?php endif; ?>

                <?php if ($current_question_index < 29): ?>
                    <a href="quiz.php?next=true" class="btn">Next</a>
                <?php else: ?>
                    <button type="submit" name="submit_quiz" class="btn">Submit</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
        // Countdown Timer Script
        var timeLeft = <?php echo $time_left; ?>;
        var timerDisplay = document.getElementById('timer');

        function updateTimer() {
            var minutes = Math.floor(timeLeft / 60);
            var seconds = timeLeft % 60;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            timerDisplay.innerHTML = minutes + ':' + seconds;

            if (timeLeft <= 0) {
                // Submit the quiz automatically when time is up
                document.forms[0].submit();
            } else {
                timeLeft--;
                setTimeout(updateTimer, 1000);
            }
        }

        updateTimer();
    </script>
</body>
</html>
