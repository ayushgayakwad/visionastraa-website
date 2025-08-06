<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: https://visionastraa.com/erp/index.php');
    exit;
}
$role = $_SESSION['role'];
switch ($role) {
    case 'super_admin':
        header('Location: superadmin/dashboard.php');
        break;
    case 'admin':
        header('Location: admin/dashboard.php');
        break;
    case 'faculty':
        header('Location: faculty/dashboard.php');
        break;
    case 'student':
        header('Location: student/dashboard.php');
        break;
    default:
        header('Location: https://visionastraa.com/erp/index.php');
        break;
}
exit;