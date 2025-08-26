<?php
$required_role = 'faculty';
include '../auth.php';
require_once '../db.php';
$message = '';
$faculty_id = $_SESSION['user_id'];

// Get the selected date, default to today
$date = $_GET['date'] ?? date('Y-m-d');
$day_of_week = date('l', strtotime($date));
$date_obj = new DateTime($date);
$date_obj->modify('monday this week');
$week_start_date = $date_obj->format('Y-m-d');

// Fetch classes assigned to this faculty for the selected date from the timetable
$stmt = $pdo->prepare('SELECT id, class_name FROM erp_timetable WHERE faculty_id = ? AND week_start_date = ? AND day_of_week = ? ORDER BY time_slot ASC');
$stmt->execute([$faculty_id, $week_start_date, $day_of_week]);
$assigned_classes = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $timetable_id = $_POST['timetable_id'];
    $topics_covered = $_POST['topics_covered'];
    $assignment_details = $_POST['assignment_details'];
    $document_path = null;

    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        // Handle file upload
        $upload_dir = '../uploads/faculty_work/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . uniqid() . '_' . basename($_FILES['document']['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['document']['tmp_name'], $target_path)) {
            $document_path = $filename;
        } else {
            $message = "Failed to upload document.";
        }
    }

    if (empty($message)) {
        $stmt = $pdo->prepare('INSERT INTO erp_faculty_logs (faculty_id, date, timetable_id, topics_covered, document_path, assignment_details) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$faculty_id, $date, $timetable_id, $topics_covered, $document_path, $assignment_details]);
        $message = 'Work log submitted successfully for review!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Work Log - Faculty | EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="dashboard.php" class="logo" style="display:flex;align-items:center;gap:0.5em;">
                    <div class="logo-icon"><span>VA</span></div>
                    <span class="logo-text">EV Academy ERP</span>
                </a>
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="submit_work_log.php" class="nav-link">Submit Work Log</a>
                    <a href="view_work_logs.php" class="nav-link">View Work Logs</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="form-section card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Submit Daily Work Log</h2>
                <?php if ($message): ?>
                    <div class="alert"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form method="GET" style="margin-bottom: 2rem;">
                    <label style="display:block; margin-bottom:0.5rem;">Select Date to View Your Classes:</label>
                    <input type="date" name="date" class="form-input" value="<?php echo htmlspecialchars($date); ?>" onchange="this.form.submit()">
                </form>

                <?php if (empty($assigned_classes)): ?>
                    <div class="alert">You have no classes assigned in the timetable for <?php echo htmlspecialchars($date); ?>.</div>
                <?php else: ?>
                    <form method="POST" enctype="multipart/form-data" style="display: grid; gap: 1rem;">
                        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                        <div>
                            <label style="display:block; margin-bottom:0.5rem;">Class for <?php echo htmlspecialchars($date); ?></label>
                            <select name="timetable_id" required class="form-input">
                                <option value="">Select a Class from Your Schedule</option>
                                <?php foreach ($assigned_classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:0.5rem;">Topics Covered</label>
                            <textarea name="topics_covered" required class="form-input" rows="4"></textarea>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:0.5rem;">Assignment Given</label>
                            <textarea name="assignment_details" class="form-input" rows="3"></textarea>
                        </div>
                         <div>
                            <label style="display:block; margin-bottom:0.5rem;">Upload Relevant Document (Optional)</label>
                            <input type="file" name="document" class="form-input">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit for Review</button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>
