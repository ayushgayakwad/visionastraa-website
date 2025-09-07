<?php
$required_role = 'faculty';
include '../auth.php';
require_once '../db.php';
$faculty_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submission'])) {
    $submission_id = $_POST['submission_id'];
    $marks_scored = $_POST['marks_scored'];
    $max_marks = $_POST['max_marks'];
    $feedback = $_POST['feedback'];
    $status = $_POST['status'];

    if ($marks_scored > $max_marks) {
        $message = '<div class="alert" style="background-color: #f8d7da; color: #721c24;">Error: Marks scored cannot be greater than maximum marks.</div>';
    } else {
        $stmt = $pdo->prepare('UPDATE erp_student_assignments SET marks_scored = ?, feedback = ?, status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?');
        $stmt->execute([$marks_scored, $feedback, $status, $faculty_id, $submission_id]);
        $message = '<div class="alert" style="background-color: #d4edda; color: #155724;">Assignment reviewed successfully!</div>';
    }
}


// Fetch assignments created by this faculty member
$stmt = $pdo->prepare("
    SELECT fl.id, fl.assignment_details, fl.max_marks, tt.class_name
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
        SELECT sa.id, sa.log_id, sa.solution_path, sa.submitted_at, u.name as student_name, sa.status, sa.marks_scored, sa.feedback
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
                <?php if ($message) { echo $message; } ?>

                <?php if (empty($assignments)): ?>
                    <div class="alert">You have not created any assignments.</div>
                <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="card" style="margin-bottom: 2em;">
                            <h3 style="color:#3a4a6b;"><?php echo htmlspecialchars($assignment['class_name']); ?></h3>
                            <p><strong>Assignment:</strong> <?php echo nl2br(htmlspecialchars($assignment['assignment_details'])); ?></p>
                            <p><strong>Max Marks:</strong> <?php echo htmlspecialchars($assignment['max_marks'] ?? 'N/A'); ?></p>
                            
                            <h4 style="color:#3a4a6b; margin-top: 1.5em;">Submissions</h4>
                            <?php if (isset($submissions[$assignment['id']])): ?>
                                <div style="overflow-x:auto;">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Submitted At</th>
                                            <th>Solution</th>
                                            <th>Status</th>
                                            <th>Marks</th>
                                            <th>Feedback</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($submissions[$assignment['id']] as $submission): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                                                <td><?php echo date('M j, Y, g:i a', strtotime($submission['submitted_at'])); ?></td>
                                                <td><a href="../uploads/assignment_solutions/<?php echo htmlspecialchars($submission['solution_path']); ?>" target="_blank" class="btn">View</a></td>
                                                <form method="POST">
                                                <td class="status-<?php echo htmlspecialchars($submission['status']); ?>"><?php echo ucfirst(htmlspecialchars($submission['status'])); ?></td>
                                                <td><?php echo htmlspecialchars($submission['marks_scored']); ?> / <?php echo htmlspecialchars($assignment['max_marks']); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($submission['feedback'])); ?></td>
                                                <td>
                                                    
                                                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                                        <input type="hidden" name="max_marks" value="<?php echo $assignment['max_marks']; ?>">
                                                        <div style="min-width: 250px;">
                                                        <select name="status" class="form-input" style="margin-bottom: 0.5rem;">
                                                            <option value="pending" <?php if($submission['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                                            <option value="approved" <?php if($submission['status'] == 'approved') echo 'selected'; ?>>Approve</option>
                                                            <option value="rejected" <?php if($submission['status'] == 'rejected') echo 'selected'; ?>>Reject</option>
                                                        </select>
                                                        <input type="number" name="marks_scored" class="form-input" placeholder="Marks Scored" value="<?php echo htmlspecialchars($submission['marks_scored']); ?>" max="<?php echo htmlspecialchars($assignment['max_marks']); ?>" style="margin-bottom: 0.5rem;">
                                                        <textarea name="feedback" class="form-input" placeholder="Feedback/Comments" rows="2" style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($submission['feedback']); ?></textarea>
                                                        <button type="submit" name="review_submission" class="btn btn-primary">Save Review</button>
                                                        </div>
                                                </td>
                                                </form>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
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