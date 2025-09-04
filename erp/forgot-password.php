<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - EV Academy ERP</title>
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
                <h1 class="hero-title" style="text-align:center; color:#3a4a6b;">Forgot Password</h1>
                <p style="text-align:center; margin-bottom: 1.5rem;">Enter your email address and we will send you a link to reset your password.</p>
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert"><?php echo htmlspecialchars($_GET['message']); ?></div>
                <?php endif; ?>
                <form action="send-reset-link.php" method="post" style="display: grid; gap: 1rem;">
                    <div>
                        <label for="email" style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Email</label>
                        <input type="email" id="email" name="email" required class="form-input" placeholder="you@example.com">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Send Reset Link</button>
                    <p style="text-align:center; margin-top: 1em;"><a href="index.php" style="color:#3a4a6b;">Back to Login</a></p>
                </form>
            </section>
        </div>
    </main>
</body>
</html>