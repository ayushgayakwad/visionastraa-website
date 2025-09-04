<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: https://visionastraa.com/erp/index.php');
    exit;
}

// Check if user's role has been updated in the database and refresh session
require_once 'db.php';
$stmt = $pdo->prepare('SELECT role, approved FROM erp_users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // User doesn't exist in database, destroy session
    session_destroy();
    header('Location: https://visionastraa.com/erp/index.php');
    exit;
}

// Check if user is still approved
if (!$user['approved']) {
    session_destroy();
    header('Location: https://visionastraa.com/erp/index.php?error=account_disabled');
    exit;
}

// Update session role if it has changed in the database
if ($_SESSION['role'] !== $user['role']) {
    $_SESSION['role'] = $user['role'];
    $_SESSION['role_updated'] = true; // Flag to show notification
}

if (isset($required_role) && $_SESSION['role'] !== $required_role) {
    header('Location: https://visionastraa.com/erp/index.php');
    exit;
}