<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT id FROM erp_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600);

        $stmt = $pdo->prepare("UPDATE erp_users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
        $stmt->execute([$token, $expires, $email]);

        $reset_link = "https://visionastraa.com/erp/reset-password.php?token=$token";
        $subject = "Password Reset Request for VisionAstraa EV Academy ERP";
        $message = "
        <html>
        <head>
          <title>Password Reset Request</title>
        </head>
        <body>
          <p>Hello,</p>
          <p>You are receiving this email because we received a password reset request for your account.</p>
          <p>Click the link below to reset your password:</p>
          <p><a href='$reset_link'>$reset_link</a></p>
          <p>This password reset link is valid for 1 hour. If you did not request a password reset, no further action is required.</p>
          <p>Regards,<br>EV Academy Team</p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <no-reply@visionastraa.com>' . "\r\n";

        mail($email, $subject, $message, $headers);
    }

    header('Location: forgot-password.php?message=' . urlencode('If an account with that email exists, you will receive a password reset link shortly.'));
    exit;
}
?>