<?php
$required_role = 'faculty';
include '../auth.php';
require_once '../db.php';
$faculty_id = $_SESSION['user_id'];

// Fetch assignments created by this faculty member
$stmt = $pdo->prepare("
    SELECT fl.id, fl.assignment_details, tt.class_name
    FROM erp_faculty_logs fl
    JOIN erp_timetable tt ON fl.timetable_id = tt.id
    WHERE fl.faculty_id = ? AND fl.assignment_details IS NOT NULL AND fl.assignment_details != ''
    ORDER BY fl.created_at DESC
");
$stmt->execute([$faculty_id]);
$assignments = $stmt->fetchAll();

// Fetch submissions for these assignments
$submissions = [];
if (!empty($assignments)) {
    $assignment_ids = array_column($assignments, 'id');
    $placeholders = implode(',', array_fill(0, count($assignment_ids), '?'));
    
    $stmt_submissions = $pdo->prepare("
        SELECT sa.log_id, sa.solution_path, sa.submitted_at, u.name as student_name
        FROM erp_student_assignments sa
        JOIN erp_users u ON sa.student_id = u.id
        WHERE sa.log_id IN ($placeholders)
        ORDER BY sa.submitted_at DESC
    ");
    $stmt_submissions->execute($assignment_ids);
    
    while ($row = $stmt_submissions->fetch()) {
        $submissions[$row['log_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignment Submissions - Faculty | EV Academy ERP</title>
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
                    <a href="view_timetable.php" class="nav-link">View Timetable</a>
                    <a href="submit_work_log.php" class="nav-link">Submit Work Log</a>
                    <a href="view_work_logs.php" class="nav-link">View Work Logs</a>
                    <a href="view_submissions.php" class="nav-link">View Submissions</a>
                    <a href="view_work_stats.php" class="nav-link">My Work Stats</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">View Assignment Submissions</h2>

                <?php if (empty($assignments)): ?>
                    <div class="alert">You have not created any assignments.</div>
                <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="card" style="margin-bottom: 2em;">
                            <h3 style="color:#3a4a6b;"><?php echo htmlspecialchars($assignment['class_name']); ?></h3>
                            <p><strong>Assignment:</strong> <?php echo nl2br(htmlspecialchars($assignment['assignment_details'])); ?></p>
                            
                            <h4 style="color:#3a4a6b; margin-top: 1.5em;">Submissions</h4>
                            <?php if (isset($submissions[$assignment['id']])): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Submitted At</th>
                                            <th>Download Solution</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($submissions[$assignment['id']] as $submission): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                                                <td><?php echo date('F j, Y, g:i a', strtotime($submission['submitted_at'])); ?></td>
                                                <td>
                                                    <a href="../uploads/assignment_solutions/<?php echo htmlspecialchars($submission['solution_path']); ?>" target="_blank" class="btn">Download</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No submissions for this assignment yet.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>