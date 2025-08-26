<?php
$required_role = 'faculty';
include '../auth.php';
require_once '../db.php';
$faculty_id = $_SESSION['user_id'];

// Fetch all work logs for the logged-in faculty
$sql = 'SELECT l.*, tt.class_name 
        FROM erp_faculty_logs l 
        JOIN erp_timetable tt ON l.timetable_id = tt.id
        WHERE l.faculty_id = ? 
        ORDER BY l.date DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute([$faculty_id]);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Work Logs - Faculty | EV Academy ERP</title>
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
                    <a href="submit_work_log.php" class="nav-link">Submit Work Log</a>
                    <a href="view_work_logs.php" class="nav-link">View Work Logs</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">My Work Log History</h2>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Class</th>
                                <th>Topics Covered</th>
                                <th>Assignment</th>
                                <th>Document</th>
                                <th>Status</th>
                                <th>Review Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">You have not submitted any work logs yet.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($log['date'])); ?></td>
                                <td><?php echo htmlspecialchars($log['class_name']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($log['topics_covered'])); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($log['assignment_details'])); ?></td>
                                <td>
                                    <?php if ($log['document_path']): ?>
                                        <a href="../uploads/faculty_work/<?php echo htmlspecialchars($log['document_path']); ?>" target="_blank">View Doc</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td class="status-<?php echo htmlspecialchars($log['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($log['status'])); ?>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($log['review_comments'])); ?></td>
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
