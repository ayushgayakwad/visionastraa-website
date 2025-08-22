<?php
$required_role = 'faculty';
include '../auth.php';
require_once '../db.php';
$message = '';
$faculty_id = $_SESSION['user_id'];

// Fetch classes assigned to the logged-in faculty
$stmt = $pdo->prepare('SELECT id, name FROM erp_classes WHERE faculty_id = ? ORDER BY name ASC');
$stmt->execute([$faculty_id]);
$classes = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $class_id = $_POST['class_id'];
    $topics_covered = $_POST['topics_covered'];
    $assignment_details = $_POST['assignment_details'];

    // Calculate hours worked
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    $diff = $end->diff($start);
    $hours_worked = $diff->h + ($diff->i / 60);

    $document_path = null;
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $upload_dir = '../uploads/faculty_work/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . uniqid() . '_' . basename($_FILES['document']['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['document']['tmp_name'], $target_path)) {
            $document_path = $filename;
        } else {
            $message = "Failed to upload document.";
        }
    }

    if (empty($message)) {
        $stmt = $pdo->prepare('INSERT INTO erp_faculty_logs (faculty_id, date, start_time, end_time, hours_worked, class_id, topics_covered, document_path, assignment_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$faculty_id, $date, $start_time, $end_time, $hours_worked, $class_id, $topics_covered, $document_path, $assignment_details]);
        $message = 'Work log submitted successfully for review!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Work Log - Faculty | EV Academy ERP</title>
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
                    <a href="submit_work_log.php" class="nav-link active">Submit Work Log</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="form-section card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Submit Daily Work Log</h2>
                <?php if ($message): ?>
                    <div class="alert"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" style="display: grid; gap: 1rem;">
                    <div style="display:grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                        <div>
                            <label style="display:block; margin-bottom:0.5rem;">Date</label>
                            <input type="date" name="date" required class="form-input" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:0.5rem;">Start Time</label>
                            <input type="time" name="start_time" required class="form-input">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:0.5rem;">End Time</label>
                            <input type="time" name="end_time" required class="form-input">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:0.5rem;">Class</label>
                            <select name="class_id" required class="form-input">
                                <option value="">Select an Assigned Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:0.5rem;">Topics Covered</label>
                        <textarea name="topics_covered" required class="form-input" rows="4"></textarea>
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:0.5rem;">Assignment Given</label>
                        <textarea name="assignment_details" class="form-input" rows="3"></textarea>
                    </div>
                     <div>
                        <label style="display:block; margin-bottom:0.5rem;">Upload Relevant Document (Optional)</label>
                        <input type="file" name="document" class="form-input">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit for Review</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
