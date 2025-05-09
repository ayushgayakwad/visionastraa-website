<?php
session_start();

if (isset($_GET['quiz_submitted']) && $_GET['quiz_submitted'] == 1) {
    $quiz_submitted = true;
} else {
    $quiz_submitted = false;
}

if (isset($_GET['debug']) && $_GET['debug'] == 1) {
    header('Content-Type: application/json');

    $debug_data = [
        'total_marks' => $_SESSION['total_marks'] ?? 0,
        'answers' => $_SESSION['answers'] ?? [],
        'question_order' => $_SESSION['question_order'] ?? [],
        'current_question_index' => $_SESSION['current_question_index'] ?? 0,
    ];

    echo json_encode($debug_data);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

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

if (!isset($_SESSION['questions_answered'])) {
    $_SESSION['questions_answered'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['answers'])) {
        $question_id = $_POST['question_id'];
        $selected_answer = $_POST['answers'];

        $stmt = $pdo->prepare("SELECT correct_option, marks FROM quiz_questions WHERE id = ?");
        $stmt->execute([$question_id]);
        $question = $stmt->fetch();

        if ($question) {
            if (!isset($_SESSION['total_marks'])) {
                $_SESSION['total_marks'] = 0;
            }

            if (isset($_SESSION['answers'][$question_id])) {
                $previous_answer = $_SESSION['answers'][$question_id];

                if ($previous_answer == $question['correct_option']) {
                    $_SESSION['total_marks'] -= $question['marks'];
                }
            }

            $_SESSION['answers'][$question_id] = $selected_answer;

            if ($selected_answer == $question['correct_option']) {
                $_SESSION['total_marks'] += $question['marks'];
            }
        }
    }

    if (isset($_POST['submit_quiz'])) {
        $final_marks = 0;
    
        foreach ($_SESSION['answers'] as $qid => $answer) {
            $stmt = $pdo->prepare("SELECT correct_option, marks FROM quiz_questions WHERE id = ?");
            $stmt->execute([$qid]);
            $question = $stmt->fetch();
    
            if ($question && $question['correct_option'] == $answer) {
                $final_marks += $question['marks'];
            }
        }
    
        $_SESSION['total_marks'] = $final_marks;
    
        $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, total_marks) VALUES (?, ?)");
        $stmt->execute([$user_id, $final_marks]);
    
        $_SESSION['quiz_submitted'] = true;
        header("Location: dashboard.php?quiz_submitted=1");
        exit();
    }
}

$question_ids = range(1, 30); 
shuffle($question_ids); 

if (!isset($_SESSION['question_order'])) {
    $_SESSION['question_order'] = $question_ids;
}

if (!isset($_SESSION['current_question_index'])) {
    $_SESSION['current_question_index'] = 0; 
}

$current_question_index = $_SESSION['current_question_index'];
$current_question_id = $_SESSION['question_order'][$current_question_index];

$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE id = ?");
$stmt->execute([$current_question_id]);
$question = $stmt->fetch();

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

if (!isset($_SESSION['quiz_start_time'])) {
    $_SESSION['quiz_start_time'] = time(); 
}

$time_left = 1800 - (time() - $_SESSION['quiz_start_time']);
if ($time_left <= 0) {
    if (!isset($_SESSION['quiz_submitted'])) {
        $final_marks = 0;

        foreach ($_SESSION['answers'] as $qid => $answer) {
            $stmt = $pdo->prepare("SELECT correct_option, marks FROM quiz_questions WHERE id = ?");
            $stmt->execute([$qid]);
            $question = $stmt->fetch();

            if ($question && $question['correct_option'] == $answer) {
                $final_marks += $question['marks'];
            }
        }

        $_SESSION['total_marks'] = $final_marks;

        $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, total_marks) VALUES (?, ?)");
        $stmt->execute([$user_id, $final_marks]);

        $_SESSION['quiz_submitted'] = true;
    }

    header("Location: dashboard.php?quiz_submitted=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electric Vehicle Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #0ea5e9;
            --accent: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-tertiary: #6b7280;
            --text-light: #9ca3af;
            
            --bg-white: #ffffff;
            --bg-light: #f9fafb;
            --bg-lighter: #f3f4f6;
            --bg-lightest: #f1f5f9;
            
            --border-light: #e5e7eb;
            --border-medium: #d1d5db;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background-color: var(--bg-white);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
        }

        h1 {
            text-align: center;
            color: var(--primary);
            font-weight: 500;
        }

        .timer {
            text-align: center;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .timer span {
            font-weight: bold;
            color: var(--success);
        }

        .question {
            margin-bottom: 20px;
        }

        .question p {
            font-size: 18px;
            line-height: 1.5;
        }

        .options label {
            display: block;
            background-color: var(--bg-lighter);
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 16px;
            cursor: pointer;
            border: 1px solid var(--border-medium);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .options input[type="radio"]:checked + label {
            background-color: var(--primary-light);
            border-color: var(--primary-dark);
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            font-size: 16px;
            background-color: var(--primary);
            color: var(--bg-white);
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        .btn i {
            margin-right: 8px;
        }

        .btn:disabled {
            background-color: var(--bg-lightest);
            color: var(--text-light);
            cursor: not-allowed;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Electric Vehicle Quiz</h1>
        
        <div class="timer">
            <p>Time left: <span id="timer"></span></p>
        </div>

        <form method="POST">
            <div class="question">
                <p><strong>Question <?php echo $current_question_index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></strong></p>
            </div>

            <div class="options">
                <label><input type="radio" name="answers" value="1" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 1) ? 'checked' : ''; ?> onchange="this.form.submit();"> <?php echo htmlspecialchars($question['option_1']); ?></label>
                <label><input type="radio" name="answers" value="2" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 2) ? 'checked' : ''; ?> onchange="this.form.submit();"> <?php echo htmlspecialchars($question['option_2']); ?></label>
                <label><input type="radio" name="answers" value="3" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 3) ? 'checked' : ''; ?> onchange="this.form.submit();"> <?php echo htmlspecialchars($question['option_3']); ?></label>
                <label><input type="radio" name="answers" value="4" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 4) ? 'checked' : ''; ?> onchange="this.form.submit();"> <?php echo htmlspecialchars($question['option_4']); ?></label>
            </div>

            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">

            <div class="btn-container">
                <?php if ($current_question_index > 0): ?>
                    <a href="quiz.php?previous=true" class="btn"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php endif; ?>

                <?php if ($current_question_index < 29): ?>
                    <a href="quiz.php?next=true" class="btn">Next <i class="fas fa-chevron-right"></i></a>
                <?php else: ?>
                    <button type="submit" name="submit_quiz" class="btn">Submit <i class="fas fa-check-circle"></i></button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
        var timeLeft = <?php echo $time_left; ?>;
        var timerDisplay = document.getElementById('timer');

        function updateTimer() {
            var minutes = Math.floor(timeLeft / 60);
            var seconds = timeLeft % 60;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            timerDisplay.innerHTML = minutes + ':' + seconds;

            if (timeLeft <= 0) {
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
