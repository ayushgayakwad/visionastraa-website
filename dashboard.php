<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1c1c1c;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #333;
            border-radius: 10px;
        }
        h1 {
            text-align: center;
        }
        .user-details {
            margin-top: 20px;
            padding: 20px;
            background-color: #444;
            border-radius: 10px;
        }
        .user-details p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <?php
    // Start session
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login.html if not logged in
        header("Location: login.html");
        exit();
    }

    // Database connection
    $host = 'localhost';
    $db = 'u707137586_UserAccounts';
    $user = 'u707137586_UserAccounts';
    $pass = 'egtA*XgA+J>2';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

    // Fetch user data
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE uid = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            // If no user is found, destroy session and redirect to login
            session_destroy();
            header("Location: login.html");
            exit();
        }
    } catch (PDOException $e) {
        die("Error fetching user data: " . $e->getMessage());
    }
    ?>
    <header id="navbar-placeholder"></header>
    <div class="container">
        <h1>Welcome to Your VisionAstraa Dashboard</h1>
        <div class="user-details">
            <h2>User Details</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
            <p><strong>State:</strong> <?php echo htmlspecialchars($user['state']); ?></p>
            <p><strong>College:</strong> <?php echo htmlspecialchars($user['college']); ?></p>
            <p><strong>Degree:</strong> <?php echo htmlspecialchars($user['degree']); ?></p>
            <p><strong>Specialization:</strong> <?php echo htmlspecialchars($user['specialization']); ?></p>
            <p><strong>Graduation Year:</strong> <?php echo htmlspecialchars($user['graduation']); ?></p>
            <p><strong>LinkedIn Profile:</strong> <a href="<?php echo htmlspecialchars($user['linkedin_profile_link']); ?>" target="_blank">View Profile</a></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($user['description']); ?></p>
        </div>
        <p><a href="quiz.php" style="color: #56a3ff;">Start Quiz</a></p>
        <p><a href="php/logout.php" style="color: #56a3ff;">Logout</a></p>
    </div>
</body>
<script>
    fetch('navbarcomp-VA/navbar.html')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('navbar-placeholder').innerHTML = data;

                    const hamburger = document.getElementById("hamburger");
                    const navLinks = document.getElementById("nav-links");

                    if (hamburger && navLinks) {
                        hamburger.onclick = function () {
                            navLinks.classList.toggle("active");
                        };
                    }
                })
                .catch(error => console.error('Error loading navbar:', error));
</script>
</html>
