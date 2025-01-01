<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #1c1c1c;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background-color: #262626;
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.6);
        }
        h1 {
            text-align: center;
            font-size: 2.5rem;
            color:rgb(255, 255, 255);
            margin-bottom: 30px;
        }
        .user-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }
        .user-card {
            flex: 1 1 calc(45% - 20px);
            background-color: #333;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.5);
        }
        .user-card h2 {
            font-size: 1.2rem;
            color: #f4b400;
            border-bottom: 2px solid #444;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }
        .user-card p {
            margin: 8px 0;
            line-height: 1.5;
        }
        .user-card strong {
            color: #f4b400;
        }
        .user-card a {
            color: #add8e6;
            text-decoration: none;
        }
        .user-card a:hover {
            text-decoration: underline;
        }
        .links {
            margin-top: 30px;
            text-align: center;
        }
        .links a {
            display: inline-block;
            margin: 10px;
            padding: 12px 25px;
            background-color: #f4b400;
            color: #1c1c1c;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .links a:hover {
            background-color: #e09400;
            transform: translateY(-2px);
        }
        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .popup-content {
            background: #2b2b2b;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            color: #ffffff;
        }
        .popup button {
            background-color: #56a3ff;
            padding: 12px 24px;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        .popup button:hover {
            background-color: #1c77cc;
        }
        @media (max-width: 768px) {
            .user-card {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.html");
        exit();
    }

    $quiz_submitted = isset($_GET['quiz_submitted']) && $_GET['quiz_submitted'] == 1;

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

    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE uid = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            session_destroy();
            header("Location: login.html");
            exit();
        }
    } catch (PDOException $e) {
        die("Error fetching user data: " . $e->getMessage());
    }
    ?>

    <?php if ($quiz_submitted): ?>
            <div class="popup">
                <div class="popup-content">
                    <h2>Quiz has been submitted successfully ✅</h2>
                    <a href="dashboard.php" class="btn" style="color: #ffffff;">View Dashboard</a>
                </div>
            </div>
        <?php endif; ?>
    
    <header id="navbar-placeholder"></header>
    
    <div class="container">
        <h1>Welcome to Your VisionAstra Dashboard</h1>
        
        <div class="user-details">
            <div class="user-card">
                <h2>Personal Info</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            </div>
            <div class="user-card">
                <h2>Professional Details</h2>
                <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                <p><strong>State:</strong> <?php echo htmlspecialchars($user['state']); ?></p>
            </div>
            <div class="user-card">
                <h2>Education</h2>
                <p><strong>College:</strong> <?php echo htmlspecialchars($user['college']); ?></p>
                <p><strong>Degree:</strong> <?php echo htmlspecialchars($user['degree']); ?></p>
                <p><strong>Specialization:</strong> <?php echo htmlspecialchars($user['specialization']); ?></p>
                <p><strong>Graduation Year:</strong> <?php echo htmlspecialchars($user['graduation']); ?></p>
            </div>
            <div class="user-card">
                <h2>About</h2>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($user['description']); ?></p>
                <p><strong>LinkedIn:</strong> <a href="<?php echo htmlspecialchars($user['linkedin_profile_link']); ?>" target="_blank">View Profile</a></p>
            </div>
        </div>

        <div class="links">
            <a href="quiz.php">Start Quiz</a>
            <a href="php/logout.php">Logout</a>
        </div>
    </div>

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
</body>
</html>
