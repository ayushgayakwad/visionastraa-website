<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';
$message = '';
$stmt = $pdo->prepare('SELECT id, name FROM erp_classes ORDER BY name ASC');
$stmt->execute();
$classes = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT id, name FROM erp_users WHERE role = "student" ORDER BY name ASC');
$stmt->execute();
$students = $stmt->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'], $_POST['date'])) {
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    foreach ($students as $student) {
        $status = $_POST['attendance'][$student['id']] ?? 'absent';
        $stmt = $pdo->prepare('INSERT INTO erp_attendance (student_id, class_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status=VALUES(status), marked_by=VALUES(marked_by)');
        $stmt->execute([$student['id'], $class_id, $date, $status, $_SESSION['user_id']]);
    }
    $message = 'Attendance marked!';
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
                    <div class="logo-icon"><span>VA</span></div>
                    <span class="logo-text">EV Academy ERP</span>
                </a>
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="mark_attendance.php" class="nav-link active">Mark Attendance</a>
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="form-section card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Mark Attendance</h2>
                <?php if ($message): ?>
                    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <form method="post" style="display: grid; gap: 1rem;">
                    <div style="display:grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                        <div>
                            <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Class</label>
                            <select name="class_id" required class="form-input">
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Date</label>
                            <input type="date" name="date" required class="form-input">
                        </div>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td style="text-align:center"><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" checked></td>
                                    <td style="text-align:center"><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Mark Attendance</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>