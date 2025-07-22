<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: https://visionastraa.com/manpower/index.php');
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
    case 'user':
        header('Location: user/dashboard.php');
        break;
    default:
        header('Location: https://visionastraa.com/manpower/index.php');
        break;
}
exit; 