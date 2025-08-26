<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';

$date = $_GET['date'] ?? date('Y-m-d');
$day_of_week = date('l', strtotime($date));

// Find the start of the week for the selected date
$date_obj = new DateTime($date);
$date_obj->modify('monday this week');
$week_start_date = $date_obj->format('Y-m-d');

// Fetch today's classes from the timetable
$stmt = $pdo->prepare('SELECT id, class_name FROM erp_timetable WHERE week_start_date = ? AND day_of_week = ? ORDER BY time_slot ASC');
$stmt->execute([$week_start_date, $day_of_week]);
$todays_classes = $stmt->fetchAll();

// Fetch all students and admins
$stmt = $pdo->prepare('SELECT id, name, role FROM erp_users WHERE role IN ("student", "admin") ORDER BY name ASC');
$stmt->execute();
$users = $stmt->fetchAll();

// Fetch attendance for the selected date
$attendance = [];
if (!empty($todays_classes)) {
    $timetable_ids = array_column($todays_classes, 'id');
    $placeholders = implode(',', array_fill(0, count($timetable_ids), '?'));

    $stmt = $pdo->prepare("SELECT student_id, timetable_id, status FROM erp_attendance WHERE date = ? AND timetable_id IN ($placeholders)");
    $params = array_merge([$date], $timetable_ids);
    $stmt->execute($params);
    foreach ($stmt->fetchAll() as $row) {
        $attendance[$row['student_id']][$row['timetable_id']] = $row['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance - Super Admin | EV Academy ERP</title>
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
                    <a href="manage_timetable.php" class="nav-link">Manage Timetable</a>
                    <a href="manage_faculty.php" class="nav-link">Faculty</a>
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="manage_fees.php" class="nav-link">Fees</a>
                    <a href="view_attendance.php" class="nav-link">Attendance</a>
                    <a href="view_faculty_work.php" class="nav-link">Faculty Work</a>
                    <a href="view_work_stats.php" class="nav-link">Work Stats</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b;">Attendance for <?php echo date("F j, Y", strtotime($date)); ?> (<?php echo $day_of_week; ?>)</h2>
                <form method="GET" style="margin-bottom: 2em;">
                    <label for="date" style="font-weight: 500;">Select Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" class="form-input" onchange="this.form.submit()">
                </form>
                <?php if (empty($todays_classes)): ?>
                    <div class="alert">No classes scheduled for today.</div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <?php foreach ($todays_classes as $class): ?>
                                        <th><?php echo htmlspecialchars($class['class_name']); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <?php foreach ($todays_classes as $class): ?>
                                            <td>
                                                <?php
                                                $status = $attendance[$user['id']][$class['id']] ?? 'N/A';
                                                $status_class = '';
                                                if ($status == 'present') {
                                                    $status_class = 'present';
                                                } elseif ($status == 'absent') {
                                                    $status_class = 'absent';
                                                }
                                                echo '<span class="' . $status_class . '">' . ucfirst($status) . '</span>';
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>