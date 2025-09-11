<?php
$required_role = 'student';
include '../auth.php';
require_once '../db.php';

// --- Time Slot Definitions ---
$time_slots = [
    'GD_MORNING' => '9:00 AM - 9:30 AM',
    'CLASS_1' => '9:30 AM - 11:00 AM',
    'CLASS_2' => '11:30 AM - 1:30 PM',
    'LAB' => '2:30 PM - 4:30 PM',
    'GD_EVENING' => '4:30 PM - 6:00 PM'
];

// --- Week Selection & Date Calculation ---
$selected_week = $_GET['week'] ?? date('Y-\WW');
$year = (int)substr($selected_week, 0, 4);
$week_num = (int)substr($selected_week, 6, 2);
$date_obj = new DateTime();
$date_obj->setISODate($year, $week_num);
$week_start_date = $date_obj->format('Y-m-d');
$week_dates = [];
$days_of_week = [];
for ($i = 0; $i < 7; $i++) {
    $week_dates[] = $date_obj->format('Y-m-d');
    $days_of_week[] = $date_obj->format('l');
    $date_obj->modify('+1 day');
}
$week_end_date = end($week_dates);
// --- End Week Logic ---

$student_id = $_SESSION['user_id'];

// Fetch the timetable for the entire week
$stmt_timetable = $pdo->prepare("SELECT id, day_of_week, time_slot, class_name FROM erp_timetable WHERE week_start_date = ? ORDER BY day_of_week, time_slot");
$stmt_timetable->execute([$week_start_date]);
$timetable_data = [];
while($row = $stmt_timetable->fetch()){
    $timetable_data[$row['day_of_week']][$row['time_slot']] = $row['class_name'];
}

// Fetch the student's attendance for the entire week
$stmt_attendance = $pdo->prepare("
    SELECT tt.day_of_week, tt.time_slot, att.status 
    FROM erp_attendance att
    JOIN erp_timetable tt ON att.timetable_id = tt.id
    WHERE att.student_id = ? AND att.date BETWEEN ? AND ?
");
$stmt_attendance->execute([$student_id, $week_start_date, $week_end_date]);
$attendance_data = [];
while($row = $stmt_attendance->fetch()){
    $attendance_data[$row['day_of_week']][$row['time_slot']] = $row['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Weekly Attendance - Student | EV Academy ERP</title>
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
                    <a href="view_timetable.php" class="nav-link">View Timetable</a>
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="fee_payment.php" class="nav-link">Fee Payment</a>
                    <a href="upload_documents.php" class="nav-link">Upload Documents</a>
                    <a href="submit_assignment.php" class="nav-link">Submit Assignment</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b;">My Weekly Attendance</h2>
                <h3 style="color:#6b7a99; margin-bottom: 1.5rem;">Week of <?php echo date("F j, Y", strtotime($week_start_date)); ?></h3>
                 <form method="GET" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap; margin-bottom: 2rem;">
                    <label for="week" style="font-weight:500;">Select Week:</label>
                    <input type="week" name="week" id="week" value="<?php echo htmlspecialchars($selected_week); ?>" class="form-input" style="max-width:200px;">
                    <button type="submit" class="btn btn-primary">View</button>
                </form>
                
                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="text-align:left;">Day</th>
                                <?php foreach($time_slots as $time_string): ?>
                                    <th><?php echo $time_string; ?></th>
                                <?php endforeach; ?>
                                <th>Daily Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($days_of_week as $index => $day): ?>
                                <?php $date = $week_dates[$index]; ?>
                                <tr>
                                    <td style="text-align:left; font-weight:500;"><?php echo date('M d (l)', strtotime($date)); ?></td>
                                    <?php 
                                        $daily_total = 0;
                                        foreach (array_keys($time_slots) as $slot_key):
                                            $class_name = $timetable_data[$day][$slot_key] ?? null;
                                            $status = $attendance_data[$day][$slot_key] ?? null;

                                            if ($class_name) {
                                                if ($status == 'present') {
                                                    $daily_total++;
                                                    echo '<td style="background-color:#d4edda; color:#155724; text-align:center;">Present<br><small>('.htmlspecialchars($class_name).')</small></td>';
                                                } elseif ($status == 'absent') {
                                                    echo '<td style="background-color:#f8d7da; color:#721c24; text-align:center;">Absent<br><small>('.htmlspecialchars($class_name).')</small></td>';
                                                } else {
                                                    echo '<td style="text-align:center; color:#888;">Not Marked<br><small>('.htmlspecialchars($class_name).')</small></td>';
                                                }
                                            } else {
                                                echo '<td style="background-color:#f0f0f0;"></td>'; // No class scheduled
                                            }
                                        endforeach; 
                                    ?>
                                    <td style="text-align:center; font-weight:bold;"><?php echo $daily_total; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</body>
</html>