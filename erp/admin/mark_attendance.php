<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';
$message = '';

// --- Get current date from server (UTC) and convert to IST ---
$stmt_time = $pdo->query("SELECT NOW() as current_utc");
$current_utc_time = $stmt_time->fetchColumn();
$utc_date_obj = new DateTime($current_utc_time, new DateTimeZone('UTC'));
$utc_date_obj->setTimezone(new DateTimeZone('Asia/Kolkata'));
$date = $utc_date_obj->format('Y-m-d');
// --- End of date logic ---

$day_of_week = date('l', strtotime($date));

// Find the start of the week (Monday) for the selected date
$date_obj = new DateTime($date);
$date_obj->modify('monday this week');
$week_start_date = $date_obj->format('Y-m-d');


// Fetch today's classes from the timetable for the specific week
$stmt = $pdo->prepare('SELECT tt.*, u.name as faculty_name FROM erp_timetable tt LEFT JOIN erp_users u ON tt.faculty_id = u.id WHERE tt.week_start_date = ? AND tt.day_of_week = ? ORDER BY tt.time_slot ASC');
$stmt->execute([$week_start_date, $day_of_week]);
$todays_classes = $stmt->fetchAll();

// Fetch all students and admins
$stmt = $pdo->prepare('SELECT id, name, role FROM erp_users WHERE role IN ("student", "admin") ORDER BY name ASC');
$stmt->execute();
$users = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['timetable_id'])) {
    $timetable_id = $_POST['timetable_id'];
    $date_for_insert = $_POST['date'];
    
    // Security check: ensure the date being submitted is the current date
    if ($date_for_insert !== $date) {
        $message = "Error: Attendance can only be marked for the current date.";
    } else {
        foreach ($users as $user) {
            $status = $_POST['attendance'][$user['id']] ?? 'absent';

            $stmt = $pdo->prepare('INSERT INTO erp_attendance (student_id, timetable_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status=VALUES(status), marked_by=VALUES(marked_by)');
            $stmt->execute([$user['id'], $timetable_id, $date_for_insert, $status, $_SESSION['user_id']]);
        }
        $message = 'Attendance marked successfully!';
    }
}

// **NEW:** Fetch existing attendance for today to pre-fill the form
$stmt_existing_attendance = $pdo->prepare("SELECT timetable_id, student_id, status FROM erp_attendance WHERE date = ?");
$stmt_existing_attendance->execute([$date]);
$existing_attendance = [];
while ($row = $stmt_existing_attendance->fetch()) {
    $existing_attendance[$row['timetable_id']][$row['student_id']] = $row['status'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Admin | EV Academy ERP</title>
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
                    <a href="give_feedback.php" class="nav-link">Give Feedback</a>
                    <a href="submit_assignment.php" class="nav-link">Submit Assignment</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="form-section card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Mark Attendance for <?php echo date("F j, Y", strtotime($date)); ?> (<?php echo $day_of_week; ?>)</h2>
                <?php if ($message): ?>
                    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <div style="margin-bottom: 2em;">
                    <label for="date" style="font-weight: 500;">Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" class="form-input" readonly style="background-color: #f0f2f8; cursor: not-allowed;">
                </div>

                <?php if (empty($todays_classes)): ?>
                    <div class="alert">No classes scheduled for today in the timetable.</div>
                <?php else: ?>
                    <?php foreach ($todays_classes as $class): 
                        $is_marked = isset($existing_attendance[$class['id']]);
                    ?>
                        <div class="card" style="margin-bottom: 2em;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <h3 style="color:#3a4a6b;"><?php echo htmlspecialchars($class['class_name']); ?></h3>
                                <?php if ($is_marked): ?>
                                    <span style="background:#d4edda; color:#155724; padding: 0.3em 0.8em; border-radius:6px; font-weight:500;"><i class="fa-solid fa-check-circle"></i> Marked</span>
                                <?php endif; ?>
                            </div>
                            <p style="color:#6b7a99;">Faculty: <?php echo htmlspecialchars($class['faculty_name'] ?? 'N/A'); ?></p>
                            <form method="POST">
                                <input type="hidden" name="timetable_id" value="<?php echo $class['id']; ?>">
                                <input type="hidden" name="date" value="<?php echo $date; ?>">
                                <div style="overflow-x:auto;">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Role</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): 
                                                $current_status = $existing_attendance[$class['id']][$user['id']] ?? 'present';
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                                                <td style="text-align:center"><input type="radio" name="attendance[<?php echo $user['id']; ?>]" value="present" <?php if($current_status == 'present') echo 'checked'; ?>></td>
                                                <td style="text-align:center"><input type="radio" name="attendance[<?php echo $user['id']; ?>]" value="absent" <?php if($current_status == 'absent') echo 'checked'; ?>></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-primary" style="margin-top: 1em;"><?php echo $is_marked ? 'Update Attendance' : 'Mark Attendance'; ?> for this Class</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>