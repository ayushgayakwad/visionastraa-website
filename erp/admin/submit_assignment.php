<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';
$message = '';
$admin_id = $_SESSION['user_id'];

// Handle assignment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $log_id = $_POST['log_id'];
    $solution_file = $_FILES['solution_file'];

    if ($solution_file['error'] == 0) {
        $upload_dir = '../uploads/assignment_solutions/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . uniqid() . '_' . basename($solution_file['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($solution_file['tmp_name'], $target_path)) {
            $stmt = $pdo->prepare('INSERT INTO erp_student_assignments (student_id, log_id, solution_path) VALUES (?, ?, ?)');
            $stmt->execute([$admin_id, $log_id, $filename]);
            $message = 'Assignment submitted successfully!';
        } else {
            $message = 'Failed to upload assignment solution.';
        }
    } else {
        $message = 'Error uploading file.';
    }
}

// Fetch all assignments with deadlines
$stmt = $pdo->prepare("
    SELECT fl.id, fl.assignment_details, fl.assignment_deadline, fl.document_path, tt.class_name, u.name as faculty_name
    FROM erp_faculty_logs fl
    JOIN erp_timetable tt ON fl.timetable_id = tt.id
    JOIN erp_users u ON fl.faculty_id = u.id
    WHERE fl.assignment_deadline IS NOT NULL AND fl.status = 'approved'
    ORDER BY fl.assignment_deadline DESC
");
$stmt->execute();
$assignments = $stmt->fetchAll();

// Fetch submitted assignments
$stmt_submitted = $pdo->prepare("SELECT log_id FROM erp_student_assignments WHERE student_id = ?");
$stmt_submitted->execute([$admin_id]);
$submitted_assignments = $stmt_submitted->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Assignment - Admin | EV Academy ERP</title>
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
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="view_timetable.php" class="nav-link">View Timetable</a>
                    <a href="mark_attendance.php" class="nav-link">Mark Attendance</a>
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="upload_documents.php" class="nav-link">Upload Documents</a>
                    <a href="fee_payment.php" class="nav-link">Fee Payment</a>
                    <a href="submit_assignment.php" class="nav-link">Submit Assignment</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card" style="max-width: 800px; margin: 2em auto;">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Submit Assignments</h2>
                <?php if ($message): ?>
                    <div class="alert"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if (empty($assignments)): ?>
                    <div class="alert">No assignments found.</div>
                <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="card" style="margin-bottom: 2em;">
                            <h3 style="color:#3a4a6b;"><?php echo htmlspecialchars($assignment['class_name']); ?></h3>
                            <p style="color:#6b7a99;">Faculty: <?php echo htmlspecialchars($assignment['faculty_name']); ?></p>
                            <p><strong>Assignment:</strong> <?php echo nl2br(htmlspecialchars($assignment['assignment_details'])); ?></p>
                            <p><strong>Deadline:</strong> <?php echo date('F j, Y', strtotime($assignment['assignment_deadline'])); ?></p>
                            <?php if ($assignment['document_path']): ?>
                                <p><a href="../uploads/faculty_work/<?php echo htmlspecialchars($assignment['document_path']); ?>" target="_blank">View Assignment Document</a></p>
                            <?php endif; ?>
                            
                            <?php if (in_array($assignment['id'], $submitted_assignments)): ?>
                                <div class="alert" style="background:#d4edda; color:#155724;">You have already submitted this assignment.</div>
                            <?php elseif (date('Y-m-d') > $assignment['assignment_deadline']): ?>
                                <div class="alert" style="background:#f8d7da; color:#721c24;">The deadline for this assignment has passed.</div>
                            <?php else: ?>
                                <form method="POST" enctype="multipart/form-data" style="margin-top: 1em;">
                                    <input type="hidden" name="log_id" value="<?php echo $assignment['id']; ?>">
                                    <div>
                                        <label for="solution_file" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Upload Solution (PDF only)</label>
                                        <input type="file" id="solution_file" name="solution_file" class="form-input" accept=".pdf" required>
                                    </div>
                                    <div>
                                        <button type="submit" name="submit_assignment" class="btn btn-primary" style="margin-top: 1em;"><i class="fa-solid fa-upload"></i> Submit Assignment</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>