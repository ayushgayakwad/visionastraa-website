<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';
$message = '';
$admin_id = $_SESSION['user_id'];

// --- Get current date from server (UTC) and convert to IST ---
$stmt_time = $pdo->query("SELECT NOW() as current_utc");
$current_utc_time = $stmt_time->fetchColumn();
$utc_date_obj = new DateTime($current_utc_time, new DateTimeZone('UTC'));
$utc_date_obj->setTimezone(new DateTimeZone('Asia/Kolkata'));
$date = $utc_date_obj->format('Y-m-d');
$day_of_week = $utc_date_obj->format('l');

$date_obj = new DateTime($date);
$date_obj->modify('monday this week');
$week_start_date = $date_obj->format('Y-m-d');

// Fetch today's classes from the timetable
$stmt_classes = $pdo->prepare('SELECT tt.id, tt.class_name, u.name as faculty_name FROM erp_timetable tt LEFT JOIN erp_users u ON tt.faculty_id = u.id WHERE tt.week_start_date = ? AND tt.day_of_week = ? ORDER BY tt.time_slot ASC');
$stmt_classes->execute([$week_start_date, $day_of_week]);
$todays_classes = $stmt_classes->fetchAll();

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $timetable_id = $_POST['timetable_id'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];

    $stmt = $pdo->prepare('INSERT INTO erp_feedback (student_id, timetable_id, rating, comments) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating=VALUES(rating), comments=VALUES(comments)');
    $stmt->execute([$admin_id, $timetable_id, $rating, $comments]);
    $message = 'Thank you for your feedback!';
}

// Fetch existing feedback for today to show submitted feedback
$stmt_existing_feedback = $pdo->prepare("SELECT timetable_id, rating, comments FROM erp_feedback WHERE student_id = ? AND DATE(created_at) = ?");
$stmt_existing_feedback->execute([$admin_id, $date]);
$existing_feedback = [];
while ($row = $stmt_existing_feedback->fetch()) {
    $existing_feedback[$row['timetable_id']] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Give Feedback - Admin | EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="dashboard.php" class="logo" style="display:flex;align-items:center;gap:0.5em;">
                    <img src="../logo.png" alt="Logo" style="height: 80px;">
                </a>
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="view_timetable.php" class="nav-link">View Timetable</a>
                    <a href="mark_attendance.php" class="nav-link">Mark Attendance</a>
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="upload_documents.php" class="nav-link">Upload Documents</a>
                    <a href="fee_payment.php" class="nav-link">Fee Payment</a>
                    <a href="give_feedback.php" class="nav-link active">Give Feedback</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="form-section card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Feedback for <?php echo date("F j, Y"); ?></h2>
                <?php if ($message): ?>
                    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if (empty($todays_classes)): ?>
                    <div class="alert">No classes scheduled for today.</div>
                <?php else: ?>
                    <?php foreach ($todays_classes as $class): 
                        $is_submitted = isset($existing_feedback[$class['id']]);
                    ?>
                        <div class="card" style="margin-bottom: 2em;">
                            <h3 style="color:#3a4a6b;"><?php echo htmlspecialchars($class['class_name']); ?></h3>
                            <p style="color:#6b7a99;">Faculty: <?php echo htmlspecialchars($class['faculty_name'] ?? 'N/A'); ?></p>
                            
                            <?php if ($is_submitted): ?>
                                <div class="alert" style="background:#d4edda; color:#155724;">You have already submitted feedback for this class.</div>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="timetable_id" value="<?php echo $class['id']; ?>">
                                    <div>
                                        <label>Rating (1-5):</label>
                                        <select name="rating" required class="form-input">
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Very Good</option>
                                            <option value="3">3 - Good</option>
                                            <option value="2">2 - Fair</option>
                                            <option value="1">1 - Poor</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label>Comments:</label>
                                        <textarea name="comments" class="form-input" rows="3"></textarea>
                                    </div>
                                    <button type="submit" name="submit_feedback" class="btn btn-primary" style="margin-top: 1em;">Submit Feedback</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>