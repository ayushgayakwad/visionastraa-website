<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    echo '<p style="color:red">Super admin session not found. Please login again.</p>';
    exit;
}
// Tab selection logic
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Fees - EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .tab-btns { display: flex; gap: 1em; margin-bottom: 2em; flex-wrap: wrap; }
        .tab-btn { padding: 0.7em 2em; border-radius: 8px; background: #e3eafc; color: #3a4a6b; font-weight: 500; border: none; cursor: pointer; text-align:center; }
        .tab-btn.active { background: #3a4a6b; color: #fff; }
        .filter-form { display: flex; gap: 1em; margin-bottom: 1em; flex-wrap: wrap; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 0.7em 0.5em; }
        @media (max-width: 800px) {
            .hero-content.card { padding: 1em; }
            .tab-btns { flex-direction: column; gap: 0.5em; }
            .filter-form { flex-direction: column; gap: 0.5em; }
            .table th, .table td { padding: 0.5em 0.2em; font-size: 0.95em; }
            .table thead { display: none; }
            .table tr { display: block; margin-bottom: 1em; border: 1px solid #e3eafc; border-radius: 8px; }
            .table td { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e3eafc; }
            .table td:before { content: attr(data-label); font-weight: 600; color: #3a4a6b; margin-right: 1em; }
        }
    </style>
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
                    <a href="manage_admins.php" class="nav-link">Admins</a>
                    <a href="manage_faculty.php" class="nav-link">Faculty</a>
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="manage_fees.php" class="nav-link">Fees</a>
                    <a href="manage_classes.php" class="nav-link">Classes</a>
                    <a href="view_attendance.php" class="nav-link">Attendance</a>
                    <a href="view_faculty_work.php" class="nav-link active">Faculty Work</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content card" style="max-width: 1000px; margin: 2em auto; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.07); border-radius: 12px; padding: 2em;">
                    <h2 class="hero-title" style="text-align:center; color:#3a4a6b; margin-bottom:1em;"><i class="fa-solid fa-indian-rupee-sign"></i> Manage Student Fees</h2>
                    <div class="tab-btns">
                        <a href="manage_fees.php?tab=all" class="tab-btn<?php echo ($tab=='all')?' active':''; ?>">All Fees</a>
                        <a href="manage_fees.php?tab=approve" class="tab-btn<?php echo ($tab=='approve')?' active':''; ?>">Approve/Reject Fees</a>
                    </div>
                    <?php if ($tab == 'all'): ?>
                        <form method="get" class="filter-form">
                            <input type="hidden" name="tab" value="all">
                            <input type="text" name="search" placeholder="Search by student name or email" class="form-input" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <select name="status" class="form-input">
                                <option value="">All Status</option>
                                <option value="approved"<?php if(isset($_GET['status']) && $_GET['status']=='approved') echo ' selected'; ?>>Approved</option>
                                <option value="pending"<?php if(isset($_GET['status']) && $_GET['status']=='pending') echo ' selected'; ?>>Pending</option>
                                <option value="rejected"<?php if(isset($_GET['status']) && $_GET['status']=='rejected') echo ' selected'; ?>>Rejected</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>
                        <?php
                        // Build query with filters
                        $query = "SELECT sf.*, eu.name, eu.email FROM student_fees sf JOIN erp_users eu ON sf.student_id = eu.id WHERE 1";
                        $params = [];
                        if (!empty($_GET['search'])) {
                            $query .= " AND (eu.name LIKE ? OR eu.email LIKE ?)";
                            $params[] = "%".$_GET['search']."%";
                            $params[] = "%".$_GET['search']."%";
                        }
                        if (!empty($_GET['status'])) {
                            $query .= " AND sf.status = ?";
                            $params[] = $_GET['status'];
                        }
                        $query .= " ORDER BY sf.created_at DESC";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute($params);
                        $fees = $stmt->fetchAll();
                        ?>
                        <table class="table" style="width:100%;margin-top:1em;">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Paid Amount</th>
                                    <th>Screenshot</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fees as $fee): ?>
                                <tr>
                                    <td data-label="Student Name"><?php echo htmlspecialchars($fee['name']); ?></td>
                                    <td data-label="Email"><?php echo htmlspecialchars($fee['email']); ?></td>
                                    <td data-label="Paid Amount">₹<?php echo number_format($fee['paid_amount']); ?></td>
                                    <td data-label="Screenshot"><a href="../uploads/<?php echo htmlspecialchars($fee['screenshot']); ?>" target="_blank">View</a></td>
                                    <td data-label="Status" style="color:<?php echo ($fee['status'] == 'approved') ? '#2ecc71' : ($fee['status'] == 'rejected' ? '#e74c3c' : '#e67e22'); ?>; font-weight:500;">
                                        <?php echo ucfirst($fee['status']); ?>
                                    </td>
                                    <td data-label="Submitted At"><?php echo date('d M Y, h:i A', strtotime($fee['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (empty($fees)): ?>
                            <p style="text-align:center; color:#3a4a6b;">No fee records found.</p>
                        <?php endif; ?>
                    <?php elseif ($tab == 'approve'): ?>
                        <?php
                        // Handle approve/reject actions
                        if (isset($_POST['action']) && isset($_POST['fee_id'])) {
                            $fee_id = intval($_POST['fee_id']);
                            $action = $_POST['action'];
                            if ($action === 'approve') {
                                $stmt = $pdo->prepare("UPDATE student_fees SET status = 'approved' WHERE id = ?");
                                $stmt->execute([$fee_id]);
                            } elseif ($action === 'reject') {
                                $stmt = $pdo->prepare("UPDATE student_fees SET status = 'rejected' WHERE id = ?");
                                $stmt->execute([$fee_id]);
                            }
                            echo '<div style="background:#e3eafc;color:#2ecc71;padding:1em;border-radius:8px;margin-bottom:1em;text-align:center;font-weight:500;">Status updated successfully!</div>';
                        }
                        // Fetch all pending/rejected fees
                        $stmt = $pdo->query("SELECT sf.*, eu.name, eu.email FROM student_fees sf JOIN erp_users eu ON sf.student_id = eu.id WHERE sf.status IN ('pending','rejected') ORDER BY sf.created_at DESC");
                        $fees = $stmt->fetchAll();
                        ?>
                        <table class="table" style="width:100%;margin-top:1em;">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Paid Amount</th>
                                    <th>Screenshot</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fees as $fee): ?>
                                <tr>
                                    <td data-label="Student Name"><?php echo htmlspecialchars($fee['name']); ?></td>
                                    <td data-label="Email"><?php echo htmlspecialchars($fee['email']); ?></td>
                                    <td data-label="Paid Amount">₹<?php echo number_format($fee['paid_amount']); ?></td>
                                    <td data-label="Screenshot"><a href="../uploads/<?php echo htmlspecialchars($fee['screenshot']); ?>" target="_blank">View</a></td>
                                    <td data-label="Status" style="color:<?php echo ($fee['status'] == 'approved') ? '#2ecc71' : ($fee['status'] == 'rejected' ? '#e74c3c' : '#e67e22'); ?>; font-weight:500;">
                                        <?php echo ucfirst($fee['status']); ?>
                                    </td>
                                    <td data-label="Submitted At"><?php echo date('d M Y, h:i A', strtotime($fee['created_at'])); ?></td>
                                    <td data-label="Action">
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="fee_id" value="<?php echo $fee['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-primary" style="margin-right:0.5em;">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (empty($fees)): ?>
                            <p style="text-align:center; color:#3a4a6b;">No pending or rejected fee submissions.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
