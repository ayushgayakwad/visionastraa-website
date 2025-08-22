<?php
$required_role = 'super_admin';
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
    <title>View Attendance - Super Admin | EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .attendance-table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; }
        .attendance-table th, .attendance-table td { 
            padding: 0.5rem 0.75rem; 
            border-bottom: 1px solid #f0f2f8; 
            text-align: center; 
            font-size: 0.9rem;
        }
        .attendance-table th { 
            background: #e3eafc; 
            color: #3a4a6b; 
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        .attendance-table tr:nth-child(even) { background: #f6f8fb; }
        .present { 
            color: #28a745; 
            font-weight: 600; 
            background: #d4edda;
            border-radius: 4px;
            padding: 0.2rem 0.4rem;
        }
        .absent { 
            color: #dc3545; 
            font-weight: 600; 
            background: #f8d7da;
            border-radius: 4px;
            padding: 0.2rem 0.4rem;
        }
        .calendar-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .calendar-controls label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #3a4a6b;
            font-weight: 500;
        }
        .calendar-controls select,
        .calendar-controls input {
            min-width: 150px;
        }
        .attendance-summary {
            background: #f6f8fb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #b3c7f7;
        }
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
                    <a href="manage_admins.php" class="nav-link">Admins</a>
                    <a href="manage_faculty.php" class="nav-link">Faculty</a>
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="manage_fees.php" class="nav-link">Fees</a>
                    <a href="manage_classes.php" class="nav-link">Classes</a>
                    <a href="view_attendance.php" class="nav-link active">Attendance</a>
                    <a href="view_faculty_work.php" class="nav-link">Faculty Work</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Attendance Calendar</h2>
                
                <form method="GET" class="calendar-controls">
                    <label>
                        <i class="fa-solid fa-graduation-cap"></i>
                        Class:
                        <select name="class_id" class="form-input">
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php if ($class_id == $class['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <i class="fa-solid fa-calendar"></i>
                        Month:
                        <input type="month" name="month" value="<?php echo htmlspecialchars($month); ?>" class="form-input">
                    </label>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-search"></i> View Attendance
                    </button>
                </form>

                <?php if ($class_id && !empty($students)): ?>
                    <div class="attendance-summary">
                        <h3 style="color:#3a4a6b; margin-bottom: 0.5rem;">
                            <i class="fa-solid fa-chart-bar"></i> 
                            Attendance Summary for <?php echo htmlspecialchars($classes[array_search($class_id, array_column($classes, 'id'))]['name']); ?>
                        </h3>
                        <p style="color:#6b7a99; margin: 0;">
                            Showing attendance for <?php echo count($students); ?> students in <?php echo date('F Y', strtotime($month.'-01')); ?>
                        </p>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th style="text-align: left; min-width: 150px;">Student</th>
                                    <?php
                                    $days = range(1, date('t', strtotime($month.'-01')));
                                    foreach ($days as $d) {
                                        $day_name = date('D', strtotime($month.'-'.$d));
                                        echo '<th title="'.$day_name.'">'.$d.'<br><small style="font-size: 0.7rem; opacity: 0.7;">'.$day_name.'</small></th>';
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td style="text-align: left; font-weight: 500;">
                                        <?php echo htmlspecialchars($student['name']); ?>
                                    </td>
                                    <?php
                                    foreach ($days as $d) {
                                        $date = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                        $status = $attendance[$student['id']][$date] ?? '';
                                        $cell_class = '';
                                        $cell_content = '';
                                        
                                        if ($status === 'present') {
                                            $cell_class = 'present';
                                            $cell_content = '✓';
                                        } elseif ($status === 'absent') {
                                            $cell_class = 'absent';
                                            $cell_content = '✗';
                                        }
                                        
                                        echo '<td class="'.$cell_class.'">'.$cell_content.'</td>';
                                    }
                                    ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($class_id): ?>
                    <div class="alert">
                        <i class="fa-solid fa-info-circle"></i>
                        No students found for the selected class.
                    </div>
                <?php else: ?>
                    <div class="alert">
                        <i class="fa-solid fa-info-circle"></i>
                        Please select a class to view attendance.
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>