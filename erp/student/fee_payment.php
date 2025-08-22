<?php
$required_role = 'student';
include '../auth.php';
require_once '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo '<p style="color:red">Student session not found or not a student. Please login again.</p>';
    exit;
}
$student_id = $_SESSION['user_id'];
$total_fee = 100300;
// Fetch all fee records for the student
$stmt = $pdo->prepare("SELECT * FROM student_fees WHERE student_id = ? ORDER BY created_at ASC");
$stmt->execute([$student_id]);
$fees = $stmt->fetchAll();
$paid = 0;
$status = 'pending';
$screenshots = [];
foreach ($fees as $fee) {
    $paid += $fee['paid_amount'];
    if ($fee['status'] == 'pending') {
        $status = 'pending';
    } elseif ($fee['status'] == 'approved') {
        $status = 'approved';
    }
    if ($fee['screenshot']) {
        $screenshots[] = $fee['screenshot'];
    }
}
$remaining = $total_fee - $paid;

// Move form processing before any output
if (isset($_POST['submit_fee'])) {
    $paid_amount = floatval($_POST['paid_amount']);
    if ($paid_amount < 1 || $paid_amount > $remaining) {
        $error_message = "<p style='color:red'>Invalid amount. You cannot pay more than the remaining balance.</p>";
    } else {
        $file = $_FILES['screenshot'];
        $filename = time() . '_' . basename($file['name']);
        $target = '../uploads/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $remaining_balance = $remaining - $paid_amount;
            $stmt = $pdo->prepare("INSERT INTO student_fees (student_id, paid_amount, screenshot, remaining_balance) VALUES (?, ?, ?, ?)");
            $stmt->execute([$student_id, $paid_amount, $filename, $remaining_balance]);
            header('Location: fee_payment.php?success=1');
            exit;
        } else {
            $error_message = "<p style='color:red'>File upload failed.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Payment - EV Academy ERP</title>
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
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="fee_payment.php" class="nav-link active">Fee Payment</a>
                    <a href="upload_documents.php" class="nav-link">Upload Documents</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content card" style="max-width: 600px; margin: 2em auto; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.07); border-radius: 12px; padding: 2em;">
                    <h2 class="hero-title" style="text-align:center; color:#3a4a6b; margin-bottom:1em;"><i class="fa-solid fa-indian-rupee-sign"></i> Fee Payment</h2>
                    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                        <div style="background:#e3eafc;color:#2ecc71;padding:1em;border-radius:8px;margin-bottom:1em;text-align:center;font-weight:500;">
                            <i class="fa-solid fa-circle-check"></i> Fee payment submitted successfully!
                        </div>
                    <?php endif; ?>
                    <div style="margin-bottom:1.5em;">
                        <p style="font-size:1.1em; margin-bottom:0.5em;">Total Fee: <b>₹<?php echo number_format($total_fee); ?></b></p>
                        <p style="font-size:1.1em; margin-bottom:0.5em;">Paid Amount: <b>₹<?php echo number_format($paid); ?></b></p>
                        <p style="font-size:1.1em; margin-bottom:0.5em;">Remaining Balance: <b>₹<?php echo number_format($remaining); ?></b></p>
                        <p style="font-size:1.1em; margin-bottom:0.5em;">Status: <b style="color:
                            <?php 
                                $overall_status = 'pending';
                                foreach ($fees as $fee) {
                                    if ($fee['status'] == 'rejected') {
                                        $overall_status = 'rejected';
                                        break;
                                    } elseif ($fee['status'] == 'approved') {
                                        $overall_status = 'approved';
                                    }
                                }
                                echo ($overall_status == 'approved') ? '#2ecc71' : (($overall_status == 'rejected') ? '#e74c3c' : '#e67e22');
                            ?>;">
                            <?php 
                                if ($overall_status == 'approved') {
                                    echo 'Fees Approved';
                                } elseif ($overall_status == 'rejected') {
                                    echo 'Fees Rejected';
                                } else {
                                    echo 'Approval Pending';
                                }
                            ?>
                        </b></p>
                        <?php if (count($screenshots) > 0): ?>
                            <div style="margin-top:1em;">
                                <b>Uploaded Screenshots:</b><br>
                                <?php foreach ($fees as $fee): ?>
                                    <?php if ($fee['screenshot']): ?>
                                        <div style="margin-bottom:0.5em;">
                                            <a href="../uploads/<?php echo htmlspecialchars($fee['screenshot']); ?>" target="_blank" style="color:#3a4a6b;text-decoration:underline;">
                                                <?php echo date('d M Y, h:i A', strtotime($fee['created_at'])); ?> - ₹<?php echo number_format($fee['paid_amount']); ?>
                                            </a>
                                           <span style="color:<?php 
                                                echo ($fee['status'] == 'approved') ? '#2ecc71' : 
                                                    (($fee['status'] == 'rejected') ? '#e74c3c' : '#e67e22'); ?>; 
                                                font-weight:500; margin-left:1em;">
                                                <?php 
                                                echo ($fee['status'] == 'approved') ? 'Approved' : 
                                                    (($fee['status'] == 'rejected') ? 'Rejected' : 'Pending'); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($remaining > 0): ?>
                    <?php if (isset($error_message)) echo $error_message; ?>
                    <form method="post" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:1em;">
                        <label style="font-weight:500;">Enter Paid Amount:</label>
                        <input type="number" name="paid_amount" min="1" max="<?php echo $remaining; ?>" required class="form-input" style="padding:0.7em;">
                        <label style="font-weight:500;">Upload Screenshot:</label>
                        <input type="file" name="screenshot" accept="image/*" required class="form-input" style="padding:0.7em;">
                        <button type="submit" name="submit_fee" class="btn btn-primary" style="margin-top:1em;">Submit</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
