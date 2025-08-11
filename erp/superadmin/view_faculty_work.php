<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';

$stmt = $pdo->prepare('SELECT id, name FROM erp_users WHERE role = "faculty" ORDER BY name ASC');
$stmt->execute();
$faculty_list = $stmt->fetchAll();

$faculty_id = $_GET['faculty_id'] ?? ($faculty_list[0]['id'] ?? null);

$stmt = $pdo->prepare('SELECT id, name FROM erp_classes ORDER BY name ASC');
$stmt->execute();
$classes = $stmt->fetchAll();

$class_id = $_GET['class_id'] ?? ($classes[0]['id'] ?? null);
$month = $_GET['month'] ?? date('Y-m');
$work = [];

if ($faculty_id && $class_id) {
    $start = $month . '-01';
    $end = date('Y-m-t', strtotime($start));
    $stmt = $pdo->prepare('SELECT * FROM erp_faculty_work WHERE faculty_id = ? AND class_id = ? AND date BETWEEN ? AND ?');
    $stmt->execute([$faculty_id, $class_id, $start, $end]);
    foreach ($stmt->fetchAll() as $row) {
        $work[$row['date']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Faculty Work - Super Admin | EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .work-table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; }
        .work-table th, .work-table td { 
            padding: 0.8rem 1rem; 
            border-bottom: 1px solid #f0f2f8; 
            text-align: left; 
        }
        .work-table th { 
            background: #e3eafc; 
            color: #3a4a6b; 
            font-weight: 600;
        }
        .work-table tr:nth-child(even) { background: #f6f8fb; }
        .work-table tr:hover { background: #e3eafc; }
        .worked { 
            color: #28a745; 
            font-weight: 600; 
        }
        .no-work { 
            color: #6c757d; 
            font-style: italic;
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
        .work-summary {
            background: #f6f8fb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #b3c7f7;
        }
        .hours-badge {
            background: #d4edda;
            color: #155724;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .details-cell {
            max-width: 300px;
            word-wrap: break-word;
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
                    <a href="manage_classes.php" class="nav-link">Classes</a>
                    <a href="view_attendance.php" class="nav-link">Attendance</a>
                    <a href="view_faculty_work.php" class="nav-link active">Faculty Work</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Faculty Work Calendar</h2>
                
                <form method="GET" class="calendar-controls">
                    <label>
                        <i class="fa-solid fa-chalkboard-teacher"></i>
                        Faculty:
                        <select name="faculty_id" class="form-input">
                            <?php foreach ($faculty_list as $faculty): ?>
                                <option value="<?php echo $faculty['id']; ?>" <?php if ($faculty_id == $faculty['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($faculty['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
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
                        <i class="fa-solid fa-search"></i> View Work
                    </button>
                </form>

                <?php if ($faculty_id && $class_id): ?>
                    <?php 
                    $faculty_name = '';
                    $class_name = '';
                    foreach ($faculty_list as $f) {
                        if ($f['id'] == $faculty_id) {
                            $faculty_name = $f['name'];
                            break;
                        }
                    }
                    foreach ($classes as $c) {
                        if ($c['id'] == $class_id) {
                            $class_name = $c['name'];
                            break;
                        }
                    }
                    ?>
                    <div class="work-summary">
                        <h3 style="color:#3a4a6b; margin-bottom: 0.5rem;">
                            <i class="fa-solid fa-chart-line"></i> 
                            Work Summary for <?php echo htmlspecialchars($faculty_name); ?>
                        </h3>
                        <p style="color:#6b7a99; margin: 0;">
                            Showing work details for class "<?php echo htmlspecialchars($class_name); ?>" in <?php echo date('F Y', strtotime($month.'-01')); ?>
                        </p>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="work-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Hours Worked</th>
                                    <th>Class Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $days = range(1, date('t', strtotime($month.'-01')));
                                foreach ($days as $d) {
                                    $date = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                    $day_name = date('l', strtotime($date));
                                    $work_data = $work[$date] ?? null;
                                    
                                    if ($work_data) {
                                        echo '<tr class="worked">';
                                        echo '<td>' . date('M d, Y', strtotime($date)) . '</td>';
                                        echo '<td>' . $day_name . '</td>';
                                        echo '<td><span class="hours-badge">' . $work_data['hours'] . ' hrs</span></td>';
                                        echo '<td class="details-cell">' . htmlspecialchars($work_data['details']) . '</td>';
                                        echo '</tr>';
                                    } else {
                                        echo '<tr class="no-work">';
                                        echo '<td>' . date('M d, Y', strtotime($date)) . '</td>';
                                        echo '<td>' . $day_name . '</td>';
                                        echo '<td>-</td>';
                                        echo '<td>No work logged</td>';
                                        echo '</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($faculty_id || $class_id): ?>
                    <div class="alert">
                        <i class="fa-solid fa-info-circle"></i>
                        Please select both faculty and class to view work details.
                    </div>
                <?php else: ?>
                    <div class="alert">
                        <i class="fa-solid fa-info-circle"></i>
                        Please select a faculty and class to view work details.
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>