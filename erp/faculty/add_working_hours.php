<?php
$required_role = 'faculty';
include '../auth.php';
require_once '../db.php';
$message = '';
// Fetch all classes assigned to this faculty
$stmt = $pdo->prepare('SELECT id, name FROM erp_classes WHERE faculty_id = ? ORDER BY name ASC');
$stmt->execute([$_SESSION['user_id']]);
$classes = $stmt->fetchAll();
// Handle add working hours + class details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'], $_POST['date'], $_POST['hours'], $_POST['details'])) {
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $hours = $_POST['hours'];
    $details = $_POST['details'];
    $stmt = $pdo->prepare('INSERT INTO erp_faculty_work (faculty_id, class_id, date, hours, details) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$_SESSION['user_id'], $class_id, $date, $hours, $details]);
    $message = 'Working hours and class details added!';
}
// Calendar view
$class_id = $_GET['class_id'] ?? ($classes[0]['id'] ?? null);
$month = $_GET['month'] ?? date('Y-m');
$work = [];
if ($class_id) {
    $start = $month . '-01';
    $end = date('Y-m-t', strtotime($start));
    $stmt = $pdo->prepare('SELECT * FROM erp_faculty_work WHERE faculty_id = ? AND class_id = ? AND date BETWEEN ? AND ?');
    $stmt->execute([$_SESSION['user_id'], $class_id, $start, $end]);
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
    <title>Add Working Hours - Faculty | EV Academy ERP</title>
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
                    <a href="add_working_hours.php" class="nav-link active">Add Working Hours</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <h1 style="text-align:center; color:#1b5e20; margin:2rem 0;">Add Working Hours & Class Details</h1>
            <?php if ($message): ?>
                <div class="alert alert-success" style="color: #1b5e20; text-align:center; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <section class="form-section card">
                <h2 style="color:#3a4a6b;">Add Working Hours</h2>
                <form method="post" style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    <label>Class:
                        <select name="class_id" required class="form-input">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php if ($class_id == $class['id']) echo 'selected'; ?>><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Date:
                        <input type="date" name="date" required class="form-input">
                    </label>
                    <label>Hours Worked:
                        <input type="number" name="hours" step="0.25" min="0" required class="form-input">
                    </label>
                    <label>Class Details (What was taught):
                        <textarea name="details" required class="form-input" style="min-width:300px;min-height:80px;"></textarea>
                    </label>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            </section>
            <section class="card">
                <h2 style="color:#3a4a6b;">Work Log Calendar</h2>
                <form method="get" style="display:flex; gap:1rem; align-items:center;">
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
                    <button type="submit" class="btn btn-primary">View</button>
                </form>
                <?php if ($class_id): ?>
                <table class="table">
                    <tr>
                        <th>Day</th>
                        <th>Hours Worked</th>
                        <th>Class Details</th>
                    </tr>
                    <?php
                    $days = range(1, date('t', strtotime($month.'-01')));
                    foreach ($days as $d) {
                        $date = $month.'-'.str_pad($d,2,'0',STR_PAD_LEFT);
                        $row = $work[$date] ?? null;
                        echo '<tr><td>'.$d.'</td>';
                        if ($row) {
                            echo '<td class="worked">'.htmlspecialchars($row['hours']).'</td>';
                            echo '<td>'.htmlspecialchars($row['details']).'</td>';
                        } else {
                            echo '<td class="no-work">-</td><td></td>';
                        }
                        echo '</tr>';
                    }
                    ?>
                </table>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>