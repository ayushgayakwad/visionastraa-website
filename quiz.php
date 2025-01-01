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
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #1c1c1c;
            color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #2b2b2b;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        h1 {
            text-align: center;
            color: #56a3ff;
        }
        .timer {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .timer span {
            font-weight: bold;
            color: #56a3ff;
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
            background-color: #3d3d3d;
            padding: 10px;
            border-radius: 6px;
            margin: 8px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .options label:hover {
            background-color: #4f4f4f;
        }
        .options input[type="radio"] {
            margin-right: 10px;
        }
        .btn-container {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #56a3ff;
            padding: 12px 24px;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            margin: 5px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #1c77cc;
        }
        .btn:disabled {
            background-color: #555;
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
    <script>
    function fetchDebugLogs() {
        fetch('quiz.php?debug=1')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Debug Logs:', data);
            })
            .catch(error => {
                console.error('Error fetching debug logs:', error);
            });
    }

    setInterval(fetchDebugLogs, 10000);

    fetchDebugLogs();
</script>
</body>
</html>
