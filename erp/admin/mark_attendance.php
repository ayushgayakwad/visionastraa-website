<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';
$message = '';
// Fetch all classes
$stmt = $pdo->prepare('SELECT id, name FROM erp_classes ORDER BY name ASC');
$stmt->execute();
$classes = $stmt->fetchAll();
// Fetch all students
$stmt = $pdo->prepare('SELECT id, name FROM erp_users WHERE role = "student" ORDER BY name ASC');
$stmt->execute();
$students = $stmt->fetchAll();
// Handle attendance submission
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
                <h2 style="color:#3a4a6b;">Mark Attendance</h2>
                <?php if ($message): ?>
                    <div class="alert alert-success" style="color: #1b5e20; text-align:center; margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                <form method="post">
                    <label>Class:
                        <select name="class_id" required class="form-input">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Date:
                        <input type="date" name="date" required class="form-input">
                    </label>
                    <table class="table">
                        <tr>
                            <th>Student</th>
                            <th>Present</th>
                            <th>Absent</th>
                        </tr>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" checked></td>
                            <td><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent"></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <button type="submit" class="btn btn-primary">Mark Attendance</button>
                </form>
            </section>
            <section class="card">
                <h2 style="color:#3a4a6b;">Attendance List</h2>
                <!-- Table styled with .table -->
                <!-- ... existing table code ... -->
            </section>
        </div>
    </main>
</body>
</html>