<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';

$faculty_list_stmt = $pdo->query("SELECT id, name FROM erp_users WHERE role IN ('faculty', 'faculty_admin') ORDER BY name ASC");
$faculty_list = $faculty_list_stmt->fetchAll();

$sql = "SELECT f.*, s.name as student_name, fac.name as faculty_name, tt.class_name 
        FROM erp_feedback f 
        JOIN erp_users s ON f.student_id = s.id 
        JOIN erp_timetable tt ON f.timetable_id = tt.id 
        LEFT JOIN erp_users fac ON tt.faculty_id = fac.id 
        WHERE 1";

$params = [];
if (!empty($_GET['faculty_id'])) {
    $sql .= " AND tt.faculty_id = ?";
    $params[] = $_GET['faculty_id'];
}
if (!empty($_GET['date'])) {
    $sql .= " AND DATE(f.created_at) = ?";
    $params[] = $_GET['date'];
}

$sql .= " ORDER BY f.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$feedback_data = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback - Super Admin | EV Academy ERP</title>
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
                    <a href="manage_timetable.php" class="nav-link">Manage Timetable</a>
                    <a href="manage_faculty.php" class="nav-link">Faculty</a>
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="view_feedback.php" class="nav-link active">View Feedback</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">View Student Feedback</h2>
                <form method="GET" style="display:flex; flex-wrap:wrap; gap:1.5rem; align-items:flex-end; margin-bottom:2rem;">
                    <div>
                        <label for="faculty_id" style="font-weight:500;">Filter by Faculty:</label>
                        <select name="faculty_id" id="faculty_id" class="form-input">
                            <option value="">All Faculty</option>
                            <?php foreach ($faculty_list as $faculty): ?>
                                <option value="<?php echo $faculty['id']; ?>" <?php if (isset($_GET['faculty_id']) && $_GET['faculty_id'] == $faculty['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($faculty['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="date" style="font-weight:500;">Filter by Date:</label>
                        <input type="date" name="date" id="date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>" class="form-input">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>

                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Faculty</th>
                                <th>Rating</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($feedback_data)): ?>
                                <tr><td colspan="6" style="text-align: center;">No feedback found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($feedback_data as $feedback): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($feedback['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($feedback['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['class_name']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['faculty_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($feedback['rating']); ?> / 5</td>
                                <td><?php echo nl2br(htmlspecialchars($feedback['comments'])); ?></td>
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