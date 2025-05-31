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
$db = 'u707137586_UserData';
$user = 'u707137586_UserData';
$pass = '7eJ@>/K#vLLm';
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

$question_table = 'quiz_questions';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['answers']) || isset($_POST['subjective_answer'])) {
        $question_id = $_POST['question_id'];

        $stmt = $pdo->prepare("SELECT marks, qtype FROM $question_table WHERE id = ?");
        $stmt->execute([$question_id]);
        $question = $stmt->fetch();

        if ($question) {
            if (!isset($_SESSION['total_marks'])) {
                $_SESSION['total_marks'] = 0;
            }

            $qtype = $question['qtype'];
            $marks_array = json_decode($question['marks'], true);

            if (isset($_SESSION['answers'][$question_id])) {
                $prev_answer = $_SESSION['answers'][$question_id];
                $_SESSION['total_marks'] -= $marks_array[$prev_answer] ?? 0;
            }

            if ($qtype === 'objective') {
                $selected_answer = $_POST['answers'];
                $_SESSION['answers'][$question_id] = $selected_answer;
                $_SESSION['total_marks'] += $marks_array[$selected_answer] ?? 0;
            } elseif ($qtype === 'subjective') {
                $user_answer = trim($_POST['subjective_answer']);
                $_SESSION['answers'][$question_id] = $user_answer;
            }
        }
    }

    if (isset($_POST['submit_quiz']) || (isset($_POST['forced_submit']) && $_POST['forced_submit'] == "1")) {
        $final_marks = 0;

        foreach ($_SESSION['answers'] as $qid => $answer) {
            $stmt = $pdo->prepare("SELECT marks, qtype FROM $question_table WHERE id = ?");
            $stmt->execute([$qid]);
            $question = $stmt->fetch();

            if ($question) {
                $marks_array = json_decode($question['marks'], true);
                if ($question['qtype'] === 'objective') {
                    $final_marks += $marks_array[$answer] ?? 0;
                }
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

if (!isset($_SESSION['question_order'])) {
    $stmt = $pdo->query("SELECT id FROM $question_table ORDER BY RAND() LIMIT 50");
    $question_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($question_ids) < 50) {
        die("Not enough questions in the database.");
    }

    $_SESSION['question_order'] = $question_ids;
}

if (!isset($_SESSION['current_question_index'])) {
    $_SESSION['current_question_index'] = 0; 
}

$current_question_index = $_SESSION['current_question_index'];
$current_question_id = $_SESSION['question_order'][$current_question_index];

$stmt = $pdo->prepare("SELECT * FROM $question_table WHERE id = ?");
$stmt->execute([$current_question_id]);
$question = $stmt->fetch();

if (isset($_GET['next'])) {
    $_SESSION['current_question_index']++;
    header("Location: assessment.php");
    exit();
}

if (isset($_GET['previous']) && $_SESSION['current_question_index'] > 0) {
    $_SESSION['current_question_index']--;
    header("Location: assessment.php");
    exit();
}

if (!isset($_SESSION['quiz_start_time'])) {
    $_SESSION['quiz_start_time'] = time(); 
}

try {
    $stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $quiz_result = $stmt->fetch();
} catch (PDOException $e) {
    die("Error checking quiz submission: " . $e->getMessage());
}

if ($quiz_result) {
    header("Location: dashboard.php?quiz_submitted=1");
    exit();
} else {
    
}

$time_left = 1200 - (time() - $_SESSION['quiz_start_time']);
if ($time_left <= 0) {
    if (!isset($_SESSION['quiz_submitted'])) {
        $final_marks = 0;

        foreach ($_SESSION['answers'] as $qid => $answer) {
            $stmt = $pdo->prepare("SELECT marks, qtype FROM $question_table WHERE id = ?");
            $stmt->execute([$qid]);
            $question = $stmt->fetch();

            if ($question) {
                $marks_array = json_decode($question['marks'], true);
                if ($question['qtype'] === 'objective') {
                    $final_marks += $marks_array[$answer] ?? 0;
                }
            }
        }

        $_SESSION['total_marks'] = $final_marks;

        $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, total_marks) VALUES (?, ?)");
        $stmt->execute([$user_id, $final_marks]);
    }

    header("Location: dashboard.php?quiz_submitted=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2PV0BKLV94"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-2PV0BKLV94');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionnaire for Student Entrepreneurs</title>
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

        * {
            -webkit-user-drag: none;
            user-drag: none;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            user-select: none;
        }
        
        .monitoring-banner {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: var(--success);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            opacity: 0;
            animation: fadeIn 2s forwards;
        }

        .monitoring-banner i {
            margin-right: 10px;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 14px 16px;
            font-size: 16px;
            border: 1px solid var(--border-medium);
            border-radius: 8px;
            background-color: var(--bg-lighter);
            color: var(--text-primary);
            font-family: 'Roboto', sans-serif;
            resize: vertical;
            box-shadow: var(--shadow-sm);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            margin: 10px 0;
        }
        
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            background-color: var(--bg-white);
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
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: var(--shadow-sm);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: var(--shadow-md);
        }
        
        .btn-secondary {
            background-color: var(--bg-white);
            color: var(--text-primary);
            border: 1px solid var(--border-medium);
        }
        
        .btn-secondary:hover {
            background-color: var(--bg-light);
        }
        
        .btn-icon {
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }
        
        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(8px);
        }

        .popup-content {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow-xl);
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes popIn {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .popup-icon {
            font-size: 3rem;
            color: var(--success);
            margin-bottom: 1.5rem;
        }

        .popup-title {
            color: var(--text-primary);
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .popup-text {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: 1rem;
        }

    </style>
</head>
<body>
    <div class="monitoring-banner">
        <i class="fas fa-eye"></i> You are being monitored during this assessment. Any form of cheating will result in disqualification.
    </div>
    <div class="container">
        <h1>Questionnaire for Student Entrepreneurs</h1>
        
        <div class="timer">
            <p>Time left: <span id="timer"></span></p>
        </div>

        <form method="POST">
            <div class="question">
                <p><strong>Question <?php echo $current_question_index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></strong></p>
            </div>
            
            <?php if (!empty($question['img_url'])): ?>
                <div class="question-image" style="text-align:center; margin:20px 0;">
                    <img src="<?php echo htmlspecialchars($question['img_url']); ?>" alt="Question Image" style="max-width:100%; height:auto;">
                </div>
            <?php endif; ?>

            <div class="options">
                <?php if ($question['qtype'] === 'objective'): ?>
                    <label><input type="radio" name="answers" value="1" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 1) ? 'checked' : ''; ?> onchange="this.form.submit();"> <?php echo htmlspecialchars($question['option_1']); ?></label>
                    <label><input type="radio" name="answers" value="2" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 2) ? 'checked' : ''; ?> onchange="this.form.submit();"> <?php echo htmlspecialchars($question['option_2']); ?></label>
                    <label><input type="radio" name="answers" value="3" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 3) ? 'checked' : ''; ?> onchange="this.form.submit();"> <?php echo htmlspecialchars($question['option_3']); ?></label>
                    <label><input type="radio" name="answers" value="4" <?php echo (isset($_SESSION['answers'][$current_question_id]) && $_SESSION['answers'][$current_question_id] == 4) ? 'checked' : ''; ?> onchange="this.form.submit();"> <?php echo htmlspecialchars($question['option_4']); ?></label>
                <?php elseif ($question['qtype'] === 'subjective'): ?>
                    <textarea name="subjective_answer" rows="5" style="width: 100%; padding: 10px;" placeholder="Type your answer here..." onblur="this.form.submit();"><?php echo isset($_SESSION['answers'][$current_question_id]) ? htmlspecialchars($_SESSION['answers'][$current_question_id]) : ''; ?></textarea>
                <?php endif; ?>
            </div>

            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
            <input type="hidden" name="forced_submit" id="forced_submit" value="0">

            <div class="btn-container">
                <?php if ($current_question_index > 0): ?>
                    <a href="assessment.php?previous=true" class="btn"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php endif; ?>

                <?php if ($current_question_index < 49): ?>
                    <a href="assessment.php?next=true" class="btn">Next <i class="fas fa-chevron-right"></i></a>
                <?php else: ?>
                    <button type="submit" name="submit_quiz" class="btn">Submit <i class="fas fa-check-circle"></i></button>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div id="warning-popup" class="popup" style="display: none;">
        <div class="popup-content">
            <div class="popup-icon" style="color: #f59e0b;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="popup-title">
                Warning: Attention Required
            </div>
            <div class="popup-text">
                You are being monitored. Do not switch tabs or minimize the window during the assessment.
            </div>
            <button onclick="closePopup()" class="btn btn-primary">
                Close
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });

        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'x')) {
                e.preventDefault(); 
            }
        });
    </script>

    <script>
        let warningShown = false;

        document.addEventListener('DOMContentLoaded', () => {
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then((stream) => {
                    stream.getTracks().forEach(track => track.stop());
                })
                .catch((err) => {
                    window.location.href = "dashboard.php?permission_denied=1";
                });
        });

        function handleViolation() {
            if (!warningShown) {
                warningShown = true;
                showWarningPopup();
            } else {
                document.getElementById('forced_submit').value = "1";
                document.forms[0].submit();
            }
        }
        
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                handleViolation();
            }
        });
    
        window.addEventListener('blur', function () {
            handleViolation();
        });

        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey && (e.key === 't' || e.key === 'w')) {
                e.preventDefault();
                handleViolation();
            }
        });

        function showWarningPopup() {
            document.getElementById('warning-popup').style.display = 'flex';
        }
        
        function closePopup() {
            document.getElementById('warning-popup').style.display = 'none';
        }
    </script>

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
