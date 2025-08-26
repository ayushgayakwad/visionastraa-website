<?php
$required_role = 'student';
include '../auth.php';
require_once '../db.php';
$student_id = $_SESSION['user_id'];

$date = $_GET['date'] ?? date('Y-m-d');
$day_of_week = date('l', strtotime($date));

// Find the start of the week for the selected date
$date_obj = new DateTime($date);
$date_obj->modify('monday this week');
$week_start_date = $date_obj->format('Y-m-d');

// Fetch today's classes from the timetable for that specific week
$stmt = $pdo->prepare('SELECT tt.*, u.name as faculty_name FROM erp_timetable tt LEFT JOIN erp_users u ON tt.faculty_id = u.id WHERE tt.week_start_date = ? AND tt.day_of_week = ? ORDER BY tt.time_slot ASC');
$stmt->execute([$week_start_date, $day_of_week]);
$todays_classes = $stmt->fetchAll();

// Fetch student's attendance for the selected date
$attendance = [];
if (!empty($todays_classes)) {
    $timetable_ids = array_column($todays_classes, 'id');
    $placeholders = implode(',', array_fill(0, count($timetable_ids), '?'));

    $stmt = $pdo->prepare("SELECT timetable_id, status FROM erp_attendance WHERE student_id = ? AND date = ? AND timetable_id IN ($placeholders)");
    $params = array_merge([$student_id, $date], $timetable_ids);
    $stmt->execute($params);
    foreach ($stmt->fetchAll() as $row) {
        $attendance[$row['timetable_id']] = $row['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance - Student | EV Academy ERP</title>
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
                    <a href="view_attendance.php" class="nav-link active">View Attendance</a>
                    <a href="fee_payment.php" class="nav-link">Fee Payment</a>
                    <a href="upload_documents.php" class="nav-link">Upload Documents</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b;">My Attendance for <?php echo date("F j, Y", strtotime($date)); ?> (<?php echo $day_of_week; ?>)</h2>
                <form method="GET" style="margin-bottom: 2em;">
                    <label for="date" style="font-weight: 500;">Select Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" class="form-input" onchange="this.form.submit()">
                </form>
                <?php if (empty($todays_classes)): ?>
                    <div class="alert">No classes scheduled for today.</div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Faculty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todays_classes as $class): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($class['faculty_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $status = $attendance[$class['id']] ?? 'Not Marked';
                                        $status_class = '';
                                        if ($status == 'present') {
                                            $status_class = 'present';
                                        } elseif ($status == 'absent') {
                                            $status_class = 'absent';
                                        }
                                        echo '<span class="' . $status_class . '">' . ucfirst($status) . '</span>';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>