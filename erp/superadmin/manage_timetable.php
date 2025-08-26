<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';
$message = '';

// --- Week Selection Logic ---
$selected_week = $_GET['week'] ?? date('Y-\WW');
$year = (int)substr($selected_week, 0, 4);
$week_num = (int)substr($selected_week, 6, 2);
$date_obj = new DateTime();
$date_obj->setISODate($year, $week_num);
$week_start_date = $date_obj->format('Y-m-d');
// --- End Week Selection Logic ---

// **CHANGE:** Updated time slots array for display and logic
$all_time_slots = [
    'GD_MORNING' => '9:00 AM - 9:30 AM',
    'CLASS_1' => '9:30 AM - 11:00 AM',
    'BREAK' => '11:00 AM - 11:30 AM',
    'CLASS_2' => '11:30 AM - 1:30 PM',
    'LUNCH' => '1:30 PM - 2:30 PM',
    'LAB' => '2:30 PM - 4:30 PM',
    'GD_EVENING' => '4:30 PM - 6:00 PM'
];

// **CHANGE:** Filtered array for the form dropdown (excluding breaks)
$bookable_time_slots = array_filter($all_time_slots, function($key) {
    return $key !== 'BREAK' && $key !== 'LUNCH';
}, ARRAY_FILTER_USE_KEY);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_slot'])) {
        $week_start_for_insert = $_POST['week_start_date'];
        $day_of_week = $_POST['day_of_week'];
        $time_slot_key = $_POST['time_slot'];
        $class_name = $_POST['class_name'];
        $class_type = $_POST['class_type'];
        $faculty_id = !empty($_POST['faculty_id']) ? $_POST['faculty_id'] : null;

        try {
            $stmt = $pdo->prepare('INSERT INTO erp_timetable (week_start_date, day_of_week, time_slot, class_name, class_type, faculty_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$week_start_for_insert, $day_of_week, $time_slot_key, $class_name, $class_type, $faculty_id, $_SESSION['user_id']]);
            $message = 'New slot added to the timetable!';
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $message = '<span style="color:red;">Error: This time slot is already booked.</span>';
            } else {
                $message = '<span style="color:red;">An unexpected error occurred: ' . $e->getMessage() . '</span>';
            }
        }
    } 
    elseif (isset($_POST['edit_slot'])) {
        $slot_id = $_POST['edit_slot_id'];
        $class_name = $_POST['edit_class_name'];
        $class_type = $_POST['edit_class_type'];
        $faculty_id = !empty($_POST['edit_faculty_id']) ? $_POST['edit_faculty_id'] : null;

        try {
            $stmt = $pdo->prepare('UPDATE erp_timetable SET class_name = ?, class_type = ?, faculty_id = ? WHERE id = ?');
            $stmt->execute([$class_name, $class_type, $faculty_id, $slot_id]);
            $message = 'Timetable slot updated successfully!';
        } catch (PDOException $e) {
            $message = '<span style="color:red;">Error updating slot: ' . $e->getMessage() . '</span>';
        }
    }
    elseif (isset($_POST['delete_slot'])) {
        $slot_id = $_POST['slot_id'];
        $stmt = $pdo->prepare('DELETE FROM erp_timetable WHERE id = ?');
        $stmt->execute([$slot_id]);
        $message = 'Slot removed from the timetable!';
    }
}

$stmt = $pdo->prepare('SELECT id, name FROM erp_users WHERE role IN ("faculty", "faculty_admin") ORDER BY name ASC');
$stmt->execute();
$faculty_list = $stmt->fetchAll();

$timetable = [];
$stmt = $pdo->prepare('SELECT tt.*, u.name as faculty_name FROM erp_timetable tt LEFT JOIN erp_users u ON tt.faculty_id = u.id WHERE tt.week_start_date = ? ORDER BY FIELD(day_of_week, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday"), FIELD(time_slot, "GD_MORNING", "CLASS_1", "BREAK", "CLASS_2", "LUNCH", "LAB", "GD_EVENING")');
$stmt->execute([$week_start_date]);
while ($row = $stmt->fetch()) {
    $timetable[$row['day_of_week']][] = $row;
}
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Timetable - Super Admin | EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="dashboard.php" class="logo" style="display:flex;align-items:center;gap:0.5em;"><div class="logo-icon"><span>VA</span></div><span class="logo-text">EV Academy ERP</span></a>
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
            <section class="form-section card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Manage Timetable for the Week of <?php echo date("F j, Y", strtotime($week_start_date)); ?></h2>
                <form method="GET" style="margin-bottom: 2em; display:flex; gap:1rem; align-items:center;">
                    <label for="week" style="font-weight: 500;">Select a Week:</label>
                    <input type="week" id="week" name="week" value="<?php echo htmlspecialchars($selected_week); ?>" class="form-input" style="max-width:200px;">
                    <button type="submit" class="btn">View Timetable</button>
                </form>
                <?php if ($message): ?>
                    <div class="alert"><?php echo $message; ?></div>
                <?php endif; ?>
                <h3 style="color:#3a4a6b; margin-top:2rem;">Add New Class to this Week's Timetable</h3>
                <form method="POST">
                    <input type="hidden" name="week_start_date" value="<?php echo $week_start_date; ?>">
                    <div style="display:grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                        <div>
                            <label>Day of the Week</label>
                            <select name="day_of_week" required class="form-input"><?php foreach ($days as $day): ?><option value="<?php echo $day; ?>"><?php echo $day; ?></option><?php endforeach; ?></select>
                        </div>
                        <div>
                            <label>Time Slot</label>
                            <select name="time_slot" required class="form-input">
                                <?php foreach ($bookable_time_slots as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div><label>Class Name</label><input type="text" name="class_name" required class="form-input" placeholder="e.g., Battery Technology"></div>
                        <div>
                            <label>Class Type</label>
                            <select name="class_type" required class="form-input">
                                <option value="Theory">Theory</option><option value="Practical">Practical</option><option value="Presentation">Presentation</option><option value="Group Discussion">Group Discussion</option><option value="Others">Others</option>
                            </select>
                        </div>
                        <div>
                            <label>Assign Faculty</label>
                            <select name="faculty_id" class="form-input">
                                <option value="">Select Faculty</option>
                                <?php foreach ($faculty_list as $faculty): ?><option value="<?php echo $faculty['id']; ?>"><?php echo htmlspecialchars($faculty['name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_slot" class="btn btn-primary" style="margin-top: 1em;">Add Slot</button>
                </form>
            </section>
            <section class="card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Timetable for Week Starting <?php echo date("F j, Y", strtotime($week_start_date)); ?></h2>
                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead><tr><th>Day</th><th>Time</th><th>Class Name</th><th>Type</th><th>Faculty</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($days as $day): ?>
                                <?php if (isset($timetable[$day]) && !empty($timetable[$day])): ?>
                                    <?php foreach ($timetable[$day] as $slot): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($slot['day_of_week']); ?></td>
                                            <td><?php echo $all_time_slots[$slot['time_slot']]; ?></td>
                                            <td><?php echo htmlspecialchars($slot['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($slot['class_type']); ?></td>
                                            <td><?php echo htmlspecialchars($slot['faculty_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <button onclick="openEditModal(<?php echo $slot['id']; ?>)" class="btn" style="background:#e3eafc; color:#3a4a6b; margin-right: 0.5rem;"><i class="fa-solid fa-edit"></i> Edit</button>
                                                <form method="POST" onsubmit="return confirm('Are you sure?');" style="display:inline-block;"><input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>"><button type="submit" name="delete_slot" class="btn" style="background: #e74c3c; color: #fff;">Delete</button></form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td><?php echo $day; ?></td><td colspan="5" style="text-align: center; color: #777;">No classes scheduled</td></tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; padding:2rem; border-radius:14px; width:90%; max-width:600px;">
            <h3 style="color:#3a4a6b; margin-bottom:1.5rem;">Edit Timetable Slot</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="edit_slot_id" id="edit_slot_id">
                <div style="display:grid; gap:1rem;">
                    <div><label>Class Name</label><input type="text" id="edit_class_name" name="edit_class_name" required class="form-input"></div>
                    <div>
                        <label>Class Type</label>
                        <select id="edit_class_type" name="edit_class_type" required class="form-input">
                            <option value="Theory">Theory</option><option value="Practical">Practical</option><option value="Presentation">Presentation</option><option value="Group Discussion">Group Discussion</option><option value="Others">Others</option>
                        </select>
                    </div>
                    <div>
                        <label>Assign Faculty</label>
                        <select id="edit_faculty_id" name="edit_faculty_id" class="form-input">
                            <option value="">Select Faculty</option>
                            <?php foreach ($faculty_list as $faculty): ?><option value="<?php echo $faculty['id']; ?>"><?php echo htmlspecialchars($faculty['name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1rem;">
                        <button type="button" class="btn" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" name="edit_slot" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        const editModal = document.getElementById('editModal');
        function openEditModal(slotId) {
            fetch(`get_timetable_slot.php?id=${slotId}`).then(response => response.json()).then(data => {
                if(data.error){ alert(data.error); return; }
                document.getElementById('edit_slot_id').value = data.id;
                document.getElementById('edit_class_name').value = data.class_name;
                document.getElementById('edit_class_type').value = data.class_type;
                document.getElementById('edit_faculty_id').value = data.faculty_id || '';
                editModal.style.display = 'flex';
            });
        }
        function closeEditModal() { editModal.style.display = 'none'; }
    </script>
</body>
</html>