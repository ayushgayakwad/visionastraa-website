<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';

// --- Week Selection & Date Calculation ---
$selected_week = $_GET['week'] ?? date('Y-\WW');
$year = (int)substr($selected_week, 0, 4);
$week_num = (int)substr($selected_week, 6, 2);
$date_obj = new DateTime();
$date_obj->setISODate($year, $week_num);
$week_start_date = $date_obj->format('Y-m-d');
$week_dates = [];
for ($i = 0; $i < 5; $i++) {
    $week_dates[] = $date_obj->format('Y-m-d');
    $date_obj->modify('+1 day');
}
$week_end_date = end($week_dates);
// --- End Week Logic ---

$stmt_users = $pdo->prepare('SELECT id, name, role FROM erp_users WHERE role IN ("student", "admin") ORDER BY name ASC');
$stmt_users->execute();
$users = $stmt_users->fetchAll();

$stmt_attendance = $pdo->prepare("
    SELECT student_id, date, COUNT(*) as attended_classes
    FROM erp_attendance
    WHERE date BETWEEN ? AND ? AND status = 'present'
    GROUP BY student_id, date
");
$stmt_attendance->execute([$week_start_date, $week_end_date]);
$attendance_data = [];
while ($row = $stmt_attendance->fetch()) {
    $attendance_data[$row['student_id']][$row['date']] = $row['attended_classes'];
}

// Time slots for the modal display
$time_slots = [
    'GD_MORNING' => '9:00 AM - 9:30 AM', 'CLASS_1' => '9:30 AM - 11:00 AM',
    'CLASS_2' => '11:30 AM - 1:30 PM', 'LAB' => '2:30 PM - 4:30 PM', 'GD_EVENING' => '4:30 PM - 6:00 PM'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Attendance - Admin | EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="dashboard.php" class="logo" style="display:flex;align-items:center;gap:0.5em;"><img src="../logo.png" alt="Logo" style="height: 80px;"></a>
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a><a href="manage_students.php" class="nav-link">Students</a><a href="mark_attendance.php" class="nav-link">Mark Attendance</a><a href="view_attendance.php" class="nav-link active">View Attendance</a><a href="upload_documents.php" class="nav-link">Upload Documents</a><a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b;">Weekly Attendance Summary</h2>
                <h3 style="color:#6b7a99; margin-bottom: 1.5rem;">Week of <?php echo date("F j, Y", strtotime($week_start_date)); ?></h3>
                 <form method="GET" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap; margin-bottom: 2rem;">
                    <label for="week" style="font-weight:500;">Select Week:</label>
                    <input type="week" name="week" id="week" value="<?php echo htmlspecialchars($selected_week); ?>" class="form-input" style="max-width:200px;"><button type="submit" class="btn btn-primary">View</button>
                </form>
                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="text-align:left;">Student Name</th>
                                <?php foreach ($week_dates as $date): ?><th><?php echo date('M d (l)', strtotime($date)); ?></th><?php endforeach; ?>
                                <th>Weekly Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="7" style="text-align:center;">No users found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td style="text-align:left; font-weight:500;">
                                        <a href="#" onclick="openAttendanceModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['name'])); ?>', '<?php echo $selected_week; ?>')" style="text-decoration:underline; color:#3a4a6b;"><?php echo htmlspecialchars($user['name']); ?></a>
                                    </td>
                                    <?php 
                                        $weekly_total = 0;
                                        foreach ($week_dates as $date): 
                                            $attended = $attendance_data[$user['id']][$date] ?? 0;
                                            $weekly_total += $attended;
                                    ?>
                                        <td style="text-align:center;"><?php echo $attended; ?></td>
                                    <?php endforeach; ?>
                                    <td style="text-align:center; font-weight:bold;"><?php echo $weekly_total; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <div id="attendanceModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; padding:2rem; border-radius:14px; width:90%; max-width:900px; max-height:80vh; overflow-y:auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h3 id="modalTitle" style="color:#3a4a6b; margin:0;">Detailed Attendance</h3>
                <button onclick="closeAttendanceModal()" class="btn" style="background:#f0f0f0; color:#333;">Close</button>
            </div>
            <div id="modalContent" style="overflow-x:auto;"></div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('attendanceModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        
        function openAttendanceModal(studentId, studentName, week) {
            modalTitle.innerText = `Detailed Attendance for ${studentName}`;
            modalContent.innerHTML = '<p>Loading...</p>';
            modal.style.display = 'flex';

            fetch(`get_student_attendance.php?student_id=${studentId}&week=${week}`)
                .then(response => response.json())
                .then(data => {
                    if(data.error) {
                        modalContent.innerHTML = `<p style="color:red;">Error: ${data.error}</p>`;
                        return;
                    }
                    
                    let tableHtml = '<table class="table"><thead><tr><th style="text-align:left;">Day</th>';
                    const timeSlots = <?php echo json_encode($time_slots); ?>;
                    for (const key in timeSlots) {
                        tableHtml += `<th>${timeSlots[key]}</th>`;
                    }
                    tableHtml += '<th>Daily Total</th></tr></thead><tbody>';
                    
                    const weekDates = <?php echo json_encode($week_dates); ?>;
                    const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

                    weekDates.forEach((date, index) => {
                        const day = daysOfWeek[index];
                        const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', weekday: 'long' });
                        tableHtml += `<tr><td style="text-align:left; font-weight:500;">${formattedDate}</td>`;
                        
                        let dailyTotal = 0;
                        for (const slotKey in timeSlots) {
                            const className = data.timetable[day] && data.timetable[day][slotKey] ? data.timetable[day][slotKey] : null;
                            const status = data.attendance[day] && data.attendance[day][slotKey] ? data.attendance[day][slotKey] : null;
                            
                            if (className) {
                                if (status === 'present') {
                                    dailyTotal++;
                                    tableHtml += `<td style="background-color:#d4edda; color:#155724; text-align:center;">Present<br><small>(${className})</small></td>`;
                                } else if (status === 'absent') {
                                    tableHtml += `<td style="background-color:#f8d7da; color:#721c24; text-align:center;">Absent<br><small>(${className})</small></td>`;
                                } else {
                                    tableHtml += `<td style="text-align:center; color:#888;">Not Marked<br><small>(${className})</small></td>`;
                                }
                            } else {
                                tableHtml += '<td style="background-color:#f0f0f0;"></td>';
                            }
                        }
                        tableHtml += `<td style="text-align:center; font-weight:bold;">${dailyTotal}</td></tr>`;
                    });
                    
                    tableHtml += '</tbody></table>';
                    modalContent.innerHTML = tableHtml;
                })
                .catch(error => {
                    modalContent.innerHTML = `<p style="color:red;">Failed to load attendance data.</p>`;
                    console.error('Error fetching attendance:', error);
                });
        }

        function closeAttendanceModal() {
            modal.style.display = 'none';
        }
    </script>
</body>
</html>