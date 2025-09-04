<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: https://visionastraa.com/erp/index.php');
    exit;
}

// Check if role was updated and show notification
$role_updated = isset($_SESSION['role_updated']) && $_SESSION['role_updated'];
if ($role_updated) {
    unset($_SESSION['role_updated']); // Clear the flag
}

$role = $_SESSION['role'];
switch ($role) {
    case 'super_admin':
        header('Location: superadmin/dashboard.php' . ($role_updated ? '?role_updated=1' : ''));
        break;
    case 'admin':
        header('Location: admin/dashboard.php' . ($role_updated ? '?role_updated=1' : ''));
        break;
    case 'faculty_admin':
        header('Location: faculty_admin/dashboard.php' . ($role_updated ? '?role_updated=1' : ''));
        break;
    case 'faculty':
        header('Location: faculty/dashboard.php' . ($role_updated ? '?role_updated=1' : ''));
        break;
    case 'student':
        header('Location: student/dashboard.php' . ($role_updated ? '?role_updated=1' : ''));
        break;
    default:
        header('Location: https://visionastraa.com/erp/index.php');
        break;
}
exit;
