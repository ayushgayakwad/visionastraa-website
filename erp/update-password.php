<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($password) < 6) {
        header('Location: reset-password.php?token=' . urlencode($token) . '&message=' . urlencode('Password must be at least 6 characters.'));
        exit;
    }
    
    if ($password !== $confirm_password) {
        header('Location: reset-password.php?token=' . urlencode($token) . '&message=' . urlencode('Passwords do not match.'));
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, reset_token_expires FROM erp_users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $now = new DateTime();
        $expires = new DateTime($user['reset_token_expires']);
        if ($now > $expires) {
            header('Location: forgot-password.php?message=' . urlencode('Your password reset token has expired. Please try again.'));
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt_update = $pdo->prepare("UPDATE erp_users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt_update->execute([$hash, $user['id']]);
        
        header('Location: index.php?message=' . urlencode('Your password has been updated successfully. Please login.'));
        exit;

    } else {
        header('Location: forgot-password.php?message=' . urlencode('Invalid reset token.'));
        exit;
    }
}