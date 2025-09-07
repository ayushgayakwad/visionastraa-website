<?php
$required_role = 'student';
include '../auth.php';
require_once '../db.php';
$message = '';
$student_id = $_SESSION['user_id'];

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = '../uploads/documents/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $files_to_upload = [
        'acknowledgement_form' => 'acknowledgement_form',
        'application_form' => 'application_form',
        'resume' => 'resume',
        'certificates' => 'certificates'
    ];

    foreach ($files_to_upload as $input_name => $db_column) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
            $file = $_FILES[$input_name];
            $filename = time() . '_' . uniqid() . '_' . basename($file['name']);
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $stmt = $pdo->prepare("UPDATE erp_users SET $db_column = ? WHERE id = ?");
                $stmt->execute([$filename, $student_id]);
                $message .= ucfirst(str_replace('_', ' ', $input_name)) . " uploaded successfully!<br>";
            } else {
                $message .= "Failed to upload " . ucfirst(str_replace('_', ' ', $input_name)) . ".<br>";
            }
        }
    }
}

// Fetch current documents
$stmt = $pdo->prepare('SELECT acknowledgement_form, application_form, resume, certificates FROM erp_users WHERE id = ?');
$stmt->execute([$student_id]);
$documents = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents - Student | EV Academy ERP</title>
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
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="fee_payment.php" class="nav-link">Fee Payment</a>
                    <a href="upload_documents.php" class="nav-link">Upload Documents</a>
                    <a href="submit_assignment.php" class="nav-link">Submit Assignment</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card" style="max-width: 800px; margin: 2em auto;">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Upload Documents</h2>
                <?php if ($message): ?>
                    <div class="alert"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" style="display: grid; gap: 1.5rem;">
                    <div>
                        <label for="acknowledgement_form" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Acknowledgement Form</label>
                        <input type="file" id="acknowledgement_form" name="acknowledgement_form" class="form-input">
                        <?php if ($documents['acknowledgement_form']): ?>
                            <p style="margin-top: 0.5rem;"><a href="../uploads/documents/<?php echo htmlspecialchars($documents['acknowledgement_form']); ?>" target="_blank">View Uploaded File</a></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="application_form" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Application Form</label>
                        <input type="file" id="application_form" name="application_form" class="form-input">
                        <?php if ($documents['application_form']): ?>
                            <p style="margin-top: 0.5rem;"><a href="../uploads/documents/<?php echo htmlspecialchars($documents['application_form']); ?>" target="_blank">View Uploaded File</a></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="resume" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Resume</label>
                        <input type="file" id="resume" name="resume" class="form-input" accept=".pdf,.doc,.docx">
                        <?php if ($documents['resume']): ?>
                            <p style="margin-top: 0.5rem;"><a href="../uploads/documents/<?php echo htmlspecialchars($documents['resume']); ?>" target="_blank">View Uploaded File</a></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="certificates" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">SSLC, PUC, and Degree Certificates (Single PDF)</label>
                        <input type="file" id="certificates" name="certificates" class="form-input" accept=".pdf">
                        <?php if ($documents['certificates']): ?>
                            <p style="margin-top: 0.5rem;"><a href="../uploads/documents/<?php echo htmlspecialchars($documents['certificates']); ?>" target="_blank">View Uploaded File</a></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Upload Files</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>