<?php
$required_role = 'faculty_admin';
include '../auth.php';
require_once '../db.php';
$message = '';
$faculty_admin_id = $_SESSION['user_id'];

$date = $_GET['date'] ?? date('Y-m-d');
$day_of_week = date('l', strtotime($date));
$date_obj = new DateTime($date);
$date_obj->modify('monday this week');
$week_start_date = $date_obj->format('Y-m-d');

// Fetch all classes for the selected day for the dropdown
$stmt = $pdo->prepare('SELECT id, class_name FROM erp_timetable WHERE week_start_date = ? AND day_of_week = ? ORDER BY time_slot ASC');
$stmt->execute([$week_start_date, $day_of_week]);
$todays_classes = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $timetable_id = $_POST['timetable_id'];
    $topics_covered = $_POST['topics_covered'];
    $assignment_details = $_POST['assignment_details'] ?? null;
    $assignment_deadline = !empty($_POST['assignment_deadline']) ? $_POST['assignment_deadline'] : null;
    $document_path = null;

    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $upload_dir = '../uploads/faculty_work/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . '_' . uniqid() . '_' . basename($_FILES['document']['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['document']['tmp_name'], $target_path)) {
            $document_path = $filename;
        } else {
            $message = "Failed to upload document.";
        }
    }

    if (empty($message)) {
        // Auto-approve for faculty admins
        $status = 'approved';
        $reviewer_id = $faculty_admin_id; // Self-approved
        $review_comments = 'Auto-approved for Faculty Admin.';
        
        $stmt = $pdo->prepare(
            'INSERT INTO erp_faculty_logs (faculty_id, date, timetable_id, topics_covered, document_path, assignment_details, status, reviewer_id, review_comments, reviewed_at, assignment_deadline) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)'
        );
        $stmt->execute([$faculty_admin_id, $date, $timetable_id, $topics_covered, $document_path, $assignment_details, $status, $reviewer_id, $review_comments, $assignment_deadline]);
        $message = 'Work log submitted and auto-approved!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Work Log - Faculty Admin | EV Academy ERP</title>
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
                    <a href="manage_faculty.php" class="nav-link">Manage Faculty</a>
                    <a href="manage_timetable.php" class="nav-link">Manage Timetable</a>
                    <a href="view_timetable.php" class="nav-link">View Timetable</a>
                    <a href="review_logs.php" class="nav-link">Review Work Logs</a>
                    <a href="submit_work_log.php" class="nav-link">Submit My Log</a>
                    <a href="view_submissions.php" class="nav-link">View Submissions</a>
                    <a href="view_work_stats.php" class="nav-link">Work Stats</a>
                    <a href="view_feedback.php" class="nav-link">View Feedback</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="form-section card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Submit My Daily Work Log</h2>
                <?php if ($message): ?>
                    <div class="alert"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="GET" style="margin-bottom: 2rem;">
                    <label style="display:block; margin-bottom:0.5rem;">Select Date to View Classes:</label>
                    <input type="date" name="date" class="form-input" value="<?php echo htmlspecialchars($date); ?>" onchange="this.form.submit()">
                </form>

                <?php if (empty($todays_classes)): ?>
                    <div class="alert">There are no classes in the timetable for <?php echo htmlspecialchars($date); ?>.</div>
                <?php else: ?>
                    <form method="POST" enctype="multipart/form-data" style="display: grid; gap: 1rem;" onsubmit="return validateForm()">
                        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                        <div>
                            <label style="display:block; margin-bottom:0.5rem;">Class for <?php echo htmlspecialchars($date); ?></label>
                            <select name="timetable_id" required class="form-input">
                                <option value="">Select a Class from Today's Schedule</option>
                                <?php foreach ($todays_classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:0.5rem;">Topics Covered</label>
                            <textarea name="topics_covered" required class="form-input" rows="4"></textarea>
                        </div>
                        
                        <div>
                            <label for="add_assignment_switch" style="display: block; margin-bottom: 0.5rem;">Add Assignment</label>
                            <input type="checkbox" id="add_assignment_switch" onchange="toggleAssignmentFields()">
                        </div>

                        <div id="assignment_fields" style="display: none;">
                            <div>
                                <label style="display:block; margin-bottom:0.5rem;">Assignment Description</label>
                                <textarea name="assignment_details" id="assignment_details" class="form-input" rows="3"></textarea>
                            </div>
                             <div>
                                <label style="display:block; margin-bottom:0.5rem;">Upload Relevant Document (Optional)</label>
                                <input type="file" name="document" id="assignment_document" class="form-input">
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:0.5rem;">Assignment Deadline</label>
                                <input type="date" name="assignment_deadline" id="assignment_deadline" class="form-input">
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Submit & Auto-Approve</button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <script>
        function toggleAssignmentFields() {
            const assignmentFields = document.getElementById('assignment_fields');
            const addAssignmentSwitch = document.getElementById('add_assignment_switch');
            assignmentFields.style.display = addAssignmentSwitch.checked ? 'block' : 'none';
        }

        function validateForm() {
            const addAssignmentSwitch = document.getElementById('add_assignment_switch');
            if (addAssignmentSwitch.checked) {
                const assignmentDetails = document.getElementById('assignment_details').value;
                const assignmentDeadline = document.getElementById('assignment_deadline').value;

                if (assignmentDetails.trim() === '') {
                    alert('Please provide a description for the assignment.');
                    return false;
                }
                if (assignmentDeadline.trim() === '') {
                    alert('Please provide a deadline for the assignment.');
                    return false;
                }
            }
            return true;
        }
    </script>
</body>
</html>
