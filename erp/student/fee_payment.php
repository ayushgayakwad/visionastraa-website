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

// Calculate paid amount based only on 'approved' transactions
$paid = 0;
foreach ($fees as $fee) {
    if ($fee['status'] == 'approved') {
        $paid += $fee['paid_amount'];
    }
}
$remaining = $total_fee - $paid;

// Handle form processing
if (isset($_POST['submit_fee'])) {
    $paid_amount = floatval($_POST['paid_amount']);
    
    // **THE FIX:** Calculate the remaining balance considering pending payments, but ignoring rejected ones.
    $potential_remaining = $total_fee;
    foreach ($fees as $fee) {
        if ($fee['status'] != 'rejected') { // Only subtract approved or pending payments
            $potential_remaining -= $fee['paid_amount'];
        }
    }

    if ($paid_amount < 1 || $paid_amount > $potential_remaining) {
        $error_message = "<p style='color:red'>Invalid amount. You cannot pay more than the total remaining balance (₹" . number_format($potential_remaining) . ").</p>";
    } else {
        $file = $_FILES['screenshot'];
        $filename = time() . '_' . basename($file['name']);
        $target = '../uploads/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO student_fees (student_id, paid_amount, screenshot, remaining_balance) VALUES (?, ?, ?, ?)");
            // The 'remaining_balance' stored here is informational for the admin.
            $stmt->execute([$student_id, $paid_amount, $filename, $remaining - $paid_amount]);
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
                    <img src="../logo.png" alt="Logo" style="height: 80px;">
                </a>
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="fee_payment.php" class="nav-link">Fee Payment</a>
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
                            <i class="fa-solid fa-circle-check"></i> Fee payment submitted successfully! Awaiting approval.
                        </div>
                    <?php endif; ?>
                    <div style="margin-bottom:1.5em;">
                        <p style="font-size:1.1em; margin-bottom:0.5em;">Total Fee: <b>₹<?php echo number_format($total_fee); ?></b></p>
                        <p style="font-size:1.1em; margin-bottom:0.5em;">Approved Paid Amount: <b>₹<?php echo number_format($paid); ?></b></p>
                        <p style="font-size:1.1em; margin-bottom:0.5em;">Remaining Balance: <b>₹<?php echo number_format($remaining); ?></b></p>
                        
                        <?php if (count($fees) > 0): ?>
                            <div style="margin-top:1.5em;">
                                <h3 style="color:#3a4a6b; margin-bottom:1em;">Payment History</h3>
                                <?php foreach ($fees as $fee): ?>
                                    <div style="margin-bottom:0.75em; padding-bottom:0.5em; border-bottom: 1px solid #eee;">
                                        <a href="../uploads/<?php echo htmlspecialchars($fee['screenshot']); ?>" target="_blank" style="color:#3a4a6b;text-decoration:underline;">
                                            <?php echo date('d M Y, h:i A', strtotime($fee['created_at'])); ?> - ₹<?php echo number_format($fee['paid_amount']); ?>
                                        </a>
                                       <span style="color:<?php 
                                            echo ($fee['status'] == 'approved') ? '#2ecc71' : 
                                                (($fee['status'] == 'rejected') ? '#e74c3c' : '#e67e22'); ?>; 
                                            font-weight:500; margin-left:1em; float:right;">
                                            <?php echo ucfirst($fee['status']); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($remaining > 0): ?>
                    <?php if (isset($error_message)) echo $error_message; ?>
                    <form method="post" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:1em;">
                        <label style="font-weight:500;">Enter Amount to Pay:</label>
                        <input type="number" name="paid_amount" min="1" max="<?php echo $remaining; ?>" required class="form-input" style="padding:0.7em;">
                        <label style="font-weight:500;">Upload Screenshot:</label>
                        <input type="file" name="screenshot" accept="image/*" required class="form-input" style="padding:0.7em;">
                        <button type="submit" name="submit_fee" class="btn btn-primary" style="margin-top:1em;">Submit Payment</button>
                    </form>
                    <?php else: ?>
                        <div class="alert" style="background:#d4edda; color:#155724; border-color:#c3e6cb; text-align:center;">
                            <i class="fa-solid fa-check-circle"></i> Congratulations! Your fees are fully paid.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
