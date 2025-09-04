<?php
require_once 'db.php';

$token = $_GET['token'] ?? '';
$message = '';
$show_form = false;

if (empty($token)) {
    $message = 'Invalid or missing reset token.';
} else {
    $stmt = $pdo->prepare("SELECT id, reset_token_expires FROM erp_users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $now = new DateTime();
        $expires = new DateTime($user['reset_token_expires']);
        if ($now > $expires) {
            $message = 'Your password reset token has expired.';
        } else {
            $show_form = true;
        }
    } else {
        $message = 'Invalid reset token.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - EV Academy ERP</title>
    <link rel="stylesheet" href="erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <img src="logo.png" alt="Logo" style="height: 80px;">
                </a>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card" style="max-width: 480px; margin: 2em auto;">
                <h1 class="hero-title" style="text-align:center; color:#3a4a6b;">Reset Your Password</h1>
                
                <?php if ($message): ?>
                    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($show_form): ?>
                <form action="update-password.php" method="post" style="display: grid; gap: 1rem;">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div>
                        <label for="password" style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">New Password</label>
                        <input type="password" id="password" name="password" required class="form-input" minlength="6">
                    </div>
                     <div>
                        <label for="confirm_password" style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required class="form-input" minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Update Password</button>
                </form>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>