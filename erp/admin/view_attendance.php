<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';
$stmt = $pdo->prepare('SELECT id, name FROM erp_classes ORDER BY name ASC');
$stmt->execute();
$classes = $stmt->fetchAll();
$class_id = $_GET['class_id'] ?? ($classes[0]['id'] ?? null);
$month = $_GET['month'] ?? date('Y-m');
$students = [];
$attendance = [];
if ($class_id) {
    $stmt = $pdo->prepare('SELECT id, name FROM erp_users WHERE role = "student" ORDER BY name ASC');
    $stmt->execute();
    $students = $stmt->fetchAll();
    $start = $month . '-01';
    $end = date('Y-m-t', strtotime($start));
    $stmt = $pdo->prepare('SELECT * FROM erp_attendance WHERE class_id = ? AND date BETWEEN ? AND ?');
    $stmt->execute([$class_id, $start, $end]);
    foreach ($stmt->fetchAll() as $row) {
        $attendance[$row['student_id']][$row['date']] = $row['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance - Admin | EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .present { color: #2e7d32; font-weight: 600; }
        .absent { color: #c62828; font-weight: 600; }
        .table th, .table td { text-align: center; }
    </style>
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
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="mark_attendance.php" class="nav-link">Mark Attendance</a>
                    <a href="view_attendance.php" class="nav-link active">View Attendance</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b;">Attendance Calendar</h2>
                <form method="get" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
                    <label>Class:
                        <select name="class_id" class="form-input">
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php if ($class_id == $class['id']) echo 'selected'; ?>><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Month:
                        <input type="month" name="month" value="<?php echo htmlspecialchars($month); ?>" class="form-input">
                    </label>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-eye"></i> View</button>
                </form>
                <?php if ($class_id): ?>
                <div style="overflow-x:auto;">
                <table class="table">
                    <tr>
                        <th>Student</th>
                        <?php
                        $days = range(1, date('t', strtotime($month.'-01')));
                        foreach ($days as $d) {
                            echo '<th>'.$d.'</th>';
                        }
                        ?>
                    </tr>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <?php
                        foreach ($days as $d) {
                            $date = $month.'-'.str_pad($d,2,'0',STR_PAD_LEFT);
                            $status = $attendance[$student['id']][$date] ?? '';
                            if ($status === 'present') {
                                echo '<td class="present">P</td>';
                            } elseif ($status === 'absent') {
                                echo '<td class="absent">A</td>';
                            } else {
                                echo '<td></td>';
                            }
                        }
                        ?>
                    </tr>
                    <?php endforeach; ?>
                </table>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>