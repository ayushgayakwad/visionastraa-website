<?php
$required_role = 'faculty_admin';
include '../auth.php';
require_once '../db.php';
$message = '';
$tab = $_GET['tab'] ?? 'pending';
$reviewer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_id'], $_POST['action'])) {
    $log_id = (int)$_POST['log_id'];
    $action = $_POST['action'];
    $comments = $_POST['comments'] ?? '';

    if ($action === 'approved' || $action === 'rejected') {
        $stmt = $pdo->prepare('UPDATE erp_faculty_logs SET status = ?, reviewer_id = ?, review_comments = ?, reviewed_at = NOW() WHERE id = ?');
        $stmt->execute([$action, $reviewer_id, $comments, $log_id]);
        $message = "Log has been " . $action . "d.";
    }
}

$sql = 'SELECT l.*, u.name as faculty_name, tt.class_name 
        FROM erp_faculty_logs l 
        JOIN erp_users u ON l.faculty_id = u.id 
        JOIN erp_timetable tt ON l.timetable_id = tt.id
        WHERE l.status = ? 
        ORDER BY l.date DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute([$tab]);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Work Logs - Faculty Admin | EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .tab-btns { display: flex; gap: 1em; margin-bottom: 2em; flex-wrap: wrap; }
        .tab-btn { padding: 0.7em 2em; border-radius: 8px; background: #e3eafc; color: #3a4a6b; font-weight: 500; border: none; cursor: pointer; text-align:center; text-decoration: none; }
        .tab-btn.active { background: #3a4a6b; color: #fff; }
    </style>
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
                    <a href="manage_faculty.php" class="nav-link">Manage Faculty</a>
                    <a href="manage_timetable.php" class="nav-link">Manage Timetable</a>
                    <a href="review_logs.php" class="nav-link">Review Work Logs</a>
                    <a href="submit_work_log.php" class="nav-link">Submit My Log</a>
                    <a href="view_work_stats.php" class="nav-link">Work Stats</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Review Faculty Work Logs</h2>
                <?php if ($message): ?>
                    <div class="alert"><?php echo $message; ?></div>
                <?php endif; ?>
                <div class="tab-btns">
                    <a href="?tab=pending" class="tab-btn <?php echo ($tab === 'pending' ? 'active' : ''); ?>">Pending</a>
                    <a href="?tab=approved" class="tab-btn <?php echo ($tab === 'approved' ? 'active' : ''); ?>">Approved</a>
                    <a href="?tab=rejected" class="tab-btn <?php echo ($tab === 'rejected' ? 'active' : ''); ?>">Rejected</a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Faculty</th>
                                <th>Date</th>
                                <th>Class</th>
                                <th>Topics Covered</th>
                                <th>Assignment</th>
                                <th>Document</th>
                                <?php if ($tab === 'pending'): ?>
                                    <th>Actions</th>
                                <?php else: ?>
                                     <th>Comments</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['faculty_name']); ?></td>
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
                                <?php if ($tab === 'pending'): ?>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="log_id" value="<?php echo $log['id']; ?>">
                                        <textarea name="comments" placeholder="Add comments..." rows="2" class="form-input" style="margin-bottom: 0.5rem;"></textarea>
                                        <button type="submit" name="action" value="approved" class="btn btn-primary" style="margin-right: 0.5rem;">Approve</button>
                                        <button type="submit" name="action" value="rejected" class="btn">Reject</button>
                                    </form>
                                </td>
                                <?php else: ?>
                                    <td><?php echo nl2br(htmlspecialchars($log['review_comments'])); ?></td>
                                <?php endif; ?>
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
