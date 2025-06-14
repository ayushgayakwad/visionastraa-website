<?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.html");
        exit();
    }

    $quiz_submitted = isset($_GET['quiz_submitted']) && $_GET['quiz_submitted'] == 1;

    $host = 'localhost';
    $db = 'u707137586_UserData';
    $user = 'u707137586_UserData';
    $pass = '7eJ@>/K#vLLm';
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
    
    $firstLetter = strtoupper(substr($user['name'], 0, 1));
    
    $currentDate = date("F j, Y");
    ?>

    <?php if (isset($_GET['permission_denied'])): ?>
        <script>
            document.getElementById("permission-denied-popup").style.display = "flex";
        </script>
    <?php endif; ?>

    <?php if ($quiz_submitted): ?>
        <div class="popup">
            <div class="popup-content">
                <div class="popup-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="popup-title">Assessment Submitted Successfully</h2>
                <p class="popup-text">Your assessment has been submitted and recorded. Thank you for your participation!</p>
                <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
            </div>
        </div>
    <?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2PV0BKLV94"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-2PV0BKLV94');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VisionAstraa Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #0ea5e9;
            --accent: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-tertiary: #6b7280;
            --text-light: #9ca3af;
            
            --bg-white: #ffffff;
            --bg-light: #f9fafb;
            --bg-lighter: #f3f4f6;
            --bg-lightest: #f1f5f9;
            
            --border-light: #e5e7eb;
            --border-medium: #d1d5db;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-lightest);
            color: var(--text-primary);
            line-height: 1.5;
            min-height: 100vh;
            font-size: 15px;
        }

        .dashboard {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .dashboard-main {
            display: flex;
            flex: 1;
        }

        .profile-sidebar {
            width: 320px;
            background: linear-gradient(to bottom, #a8a9fb 2%, #ffffff 98%);
            border-right: 1px solid var(--border-light);
            padding: 1rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .logo {
            width: 35%;
            height: auto;
            align-self: center;
            padding-bottom: 1rem;
        }

        .content-area {
            flex: 1;
            padding: 2rem;
            margin-left: 320px;
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
            box-shadow: var(--shadow-lg);
        }

        .profile-avatar-text {
            font-size: 2.5rem;
            color: white;
            font-weight: 600;
        }

        .profile-status {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: var(--success);
            border: 3px solid white;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .profile-role {
            font-size: 0.95rem;
            color: var(--text-tertiary);
            margin-bottom: 1rem;
        }

        .profile-contact {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 0;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 8px;
            background-color: var(--bg-lighter);
            transition: all 0.2s ease;
            border: 1px solid black;
        }

        .contact-item:hover {
            background-color: var(--bg-light);
        }

        .contact-icon {
            width: 18px;
            color: var(--primary);
            text-align: center;
        }

        .contact-text {
            font-size: 0.9rem;
            color: var(--text-secondary);
            word-break: break-word;
        }

        .sidebar-divider {
            width: 100%;
            height: 1px;
            background-color: var(--border-light);
            margin: 1.5rem 0;
        }

        .sidebar-section-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .sidebar-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 0;
            padding-top: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background-color: var(--bg-white);
            color: var(--text-primary);
            border: 1px solid var(--border-medium);
        }

        .btn-secondary:hover {
            background-color: var(--bg-light);
        }

        .btn-icon {
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .welcome-section {
            max-width: 600px;
        }

        .welcome-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: var(--text-tertiary);
            font-size: 1rem;
        }

        .date-display {
            font-size: 0.95rem;
            color: var(--text-tertiary);
            background-color: var(--bg-white);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-light);
        }

        .tab-navigation {
            display: flex;
            border-bottom: 1px solid var(--border-light);
            margin-bottom: 2rem;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .tab-navigation::-webkit-scrollbar {
            display: none;
        }

        .tab-item {
            padding: 1rem 1.5rem;
            font-weight: 500;
            color: var(--text-tertiary);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .tab-item.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-item:hover:not(.active) {
            color: var(--text-secondary);
            border-bottom-color: var(--border-medium);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .info-section {
            background-color: var(--bg-white);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .info-section-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-section-icon {
            color: var(--primary);
        }

        .info-section-content {
            padding: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            font-size: 0.85rem;
            color: var(--text-tertiary);
            font-weight: 500;
        }

        .info-value {
            font-size: 1rem;
            color: var(--text-primary);
            font-weight: 500;
            word-break: break-word;
        }

        .info-value a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .info-value a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .education-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .education-timeline::before {
            content: "";
            position: absolute;
            top: 0;
            left: 8px;
            height: 100%;
            width: 2px;
            background-color: var(--border-medium);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -2rem;
            top: 0;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background-color: var(--primary);
            border: 3px solid white;
            box-shadow: var(--shadow-sm);
        }

        .timeline-content {
            background-color: var(--bg-lighter);
            border-radius: 8px;
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
        }

        .timeline-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .timeline-subtitle {
            font-size: 0.95rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .timeline-date {
            font-size: 0.85rem;
            color: var(--text-tertiary);
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: var(--bg-white);
            border-radius: 20px;
            margin-top: 0.5rem;
        }

        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(8px);
        }

        .popup-content {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow-xl);
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes popIn {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .popup-icon {
            font-size: 3rem;
            color: var(--success);
            margin-bottom: 1.5rem;
        }

        .popup-title {
            color: var(--text-primary);
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .popup-text {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        @media (max-width: 1024px) {
            .profile-sidebar {
                width: 280px;
            }
            
            .content-area {
                margin-left: 280px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-main {
                flex-direction: column;
            }
            
            .profile-sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid var(--border-light);
                padding: 1.5rem;
            }
            
            .content-area {
                margin-left: 0;
                padding: 1.5rem;
            }
            
            .profile-header {
                flex-direction: row;
                align-items: center;
                gap: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
                min-width: 80px;
                min-height: 80px;
                aspect-ratio: 1 / 1;
                flex-shrink: 0;
            }
        
            .profile-avatar-text {
                font-size: 1.75rem;
            }
            
            .profile-name-role {
                display: flex;
                flex-direction: column;
            }
            
            .sidebar-actions {
                flex-direction: row;
                padding-top: 0;
            }
            
            .btn {
                flex: 1;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-main">
            <aside class="profile-sidebar">
                <img src="./navbarcomp-VA/1000010772-removebg-preview.png" alt="VisionAstraa Logo" class="logo">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <span class="profile-avatar-text"><?php echo $firstLetter; ?></span>
                        <div class="profile-status"></div>
                    </div>
                    <div class="profile-name-role">
                        <h2 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h2>
                    </div>
                </div>
                
                <div class="profile-contact">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text"><?php echo htmlspecialchars($user['phone']); ?></div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text"><?php echo htmlspecialchars($user['state']); ?></div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fab fa-linkedin"></i>
                        </div>
                        <div class="contact-text">
                            <a href="<?php echo htmlspecialchars($user['linkedin_profile_link']); ?>" target="_blank">LinkedIn Profile</a>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-divider"></div>
                
                <h3 class="sidebar-section-title">Quick Actions</h3>
                
                <div class="sidebar-actions">
                    <?php
                        try {
                            $stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE user_id = ?");
                            $stmt->execute([$user_id]);
                            $quiz_result = $stmt->fetch();
                        } catch (PDOException $e) {
                            die("Error checking quiz submission: " . $e->getMessage());
                        }
                    ?>
                    <?php if ($quiz_result): ?>
                        <button class="btn btn-primary" disabled style="cursor: not-allowed; opacity: 0.6;">
                            <i class="fas fa-clipboard-check btn-icon"></i> Assessment Already Taken
                        </button>
                    <?php else: ?>
                        <a href="#" class="btn btn-primary" onclick="showQuizPopup(); return false;">
                            <i class="fas fa-clipboard-list btn-icon"></i> Start Online Assessment
                        </a>
                    <?php endif; ?>
                    
                    <a href="php/logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt btn-icon"></i>
                        Logout
                    </a>
                </div>
            </aside>
            
            <main class="content-area">
                <div class="dashboard-header">
                    <div class="welcome-section">
                        <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>!</h1>
                        <p class="welcome-subtitle">Here's an overview of your VisionAstraa profile and activities.</p>
                    </div>
                    <div class="date-display">
                        <i class="far fa-calendar-alt"></i> <?php echo $currentDate; ?>
                    </div>
                </div>
                
                <div class="tab-navigation">
                    <div class="tab-item active" data-tab="assessment">
                        <i class="fas fa-clipboard-list"></i> Assessment
                    </div>
                    <div class="tab-item" data-tab="profile">
                        <i class="fas fa-user"></i> Profile
                    </div>
                    <div class="tab-item" data-tab="education">
                        <i class="fas fa-graduation-cap"></i> Education
                    </div>
                </div>
                
                <div id="assessment-tab" class="tab-content active">
                <div class="info-section">
                    <div class="info-section-header">
                        <h3 class="info-section-title">
                            <i class="fas fa-clipboard-list info-section-icon"></i>
                            Take the Assessment
                        </h3>
                    </div>
                    <div class="info-section-content">
                        <p>Start the online assessment to test your knowledge and skills. Please ensure you are prepared to complete it in one sitting.</p>
                        <br>
                        <?php if ($quiz_result): ?>
                            <button class="btn btn-primary" disabled style="cursor: not-allowed; opacity: 0.6;">
                                <i class="fas fa-clipboard-check btn-icon"></i> Assessment Already Taken
                            </button>
                            <p style="margin-top: 10px; color: var(--text-secondary);">
                                <strong>Your Score:</strong> <?php echo htmlspecialchars($quiz_result['total_marks']);?> out of 200<br>
                            </p>
                        <?php else: ?>
                            <a href="#" class="btn btn-primary" onclick="showQuizPopup(); return false;">
                                <i class="fas fa-clipboard-list btn-icon"></i> Start Online Assessment
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
                
                <div id="profile-tab" class="tab-content">
                    <div class="info-section">
                        <div class="info-section-header">
                            <h3 class="info-section-title">
                                <i class="fas fa-user-circle info-section-icon"></i>
                                Personal Information
                            </h3>
                        </div>
                        <div class="info-section-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">Full Name</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Email Address</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Phone Number</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Location</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['state']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <div class="info-section-header">
                            <h3 class="info-section-title">
                                <i class="fas fa-briefcase info-section-icon"></i>
                                Professional Details
                            </h3>
                        </div>
                        <div class="info-section-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">LinkedIn</div>
                                    <div class="info-value">
                                        <a href="<?php echo htmlspecialchars($user['linkedin_profile_link']); ?>" target="_blank">
                                            <i class="fab fa-linkedin"></i> View Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="education-tab" class="tab-content">
                    <div class="info-section">
                        <div class="info-section-header">
                            <h3 class="info-section-title">
                                <i class="fas fa-graduation-cap info-section-icon"></i>
                                Education History
                            </h3>
                        </div>
                        <div class="info-section-content">
                            <div class="education-timeline">
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <h4 class="timeline-title"><?php echo htmlspecialchars($user['degree']); ?> in <?php echo htmlspecialchars($user['specialization']); ?></h4>
                                        <p class="timeline-subtitle"><?php echo htmlspecialchars($user['college']); ?></p>
                                        <span class="timeline-date">Graduated: <?php echo htmlspecialchars($user['graduation']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <div id="instructions-popup" class="popup" style="display: none;">
        <div class="popup-content" style="text-align: left; max-height: 80vh; overflow-y: auto;">
            <h2 class="popup-title">Assessment Instructions</h2>
            <div class="popup-text">
                <ul style="padding-left: 1.5rem;">
                    <li><b>Academic Integrity:</b> <br>Ensure that you complete the assessment independently. Collaboration or cheating will lead to disqualification.</li>
                    <li><b>Tab Switching and Copying:</b> <br>Switching/Minimizing tabs or copying the questions are not allowed. The quiz will automatically close after the first warning if you try to switch tabs.</li>
                    <li><b>Back Button:</b> <br>Pressing the back button during the assessment is not allowed and may cause the quiz to close.</li>
                    <li><b>Requirements for Starting the Quiz:</b> <br>Ensure you have a stable internet connection and 20 minutes of uninterrupted time before starting the assessment.</li>
                    <li><b>Assessment Duration:</b> <br>You have 20 minutes to complete the assessment. The timer will start as soon as you begin the assessment.</li>
                    <li><b>Number of Questions:</b> <br>The assessment includes 15 questions.</li>
                    <li><b>Assessment Access:</b> <br>You are allowed to take the assessment only once and you will not be able to retake it.</li>
                    <li><b>Starting the Assessment:</b> <br>Click on the “Start Online Assessment” button to begin. Ensure you are ready to complete the assessment in one sitting.</li>
                    <li><b>Answering Questions:</b> <br>For each question, select the option that you believe is correct. You can change your answer at any time before submitting the assessment.</li>
                    <li><b>Submitting the Assessment:</b> <br>Once you have answered all the questions, click the “Submit" button to finalize your answers.</li>
                    <li><b>Time Management:</b> <br>Keep an eye on the timer displayed on the screen. The assessment will get submitted automatically once the time limit is reached.</li>
                </ul>
            </div>
            <div style="text-align: center; margin-top: 1.5rem;">
                <button class="btn btn-primary" onclick="continueToQuizPopup()">Continue</button>
                <button class="btn btn-secondary" onclick="closeInstructionsPopup()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabItems = document.querySelectorAll('.tab-item');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabItems.forEach(item => {
                item.addEventListener('click', function() {
                    tabItems.forEach(tab => tab.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    this.classList.add('active');
                    
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
        });
    </script>
    <div id="quiz-popup" class="popup" style="display: none;">
        <div class="popup-content">
            <div class="popup-icon"><i class="fas fa-exclamation-circle" style="color: #f59e0b;"></i></div>
            <h2 class="popup-title">Ready to Begin?</h2>
            <p class="popup-text">
                <?php
                    $branchMessage = "Questionnaire for Student Entrepreneurs";
                    echo "You are about to start the <strong>$branchMessage</strong>.";
                ?>
            </p>
            <a href="assessment.php" class="btn btn-primary">Start Online Assessment</a>
            <br><br>
            <button class="btn btn-secondary" onclick="document.getElementById('quiz-popup').style.display='none';">Cancel</button>
        </div>
    </div>
    
    <div id="already-taken-popup" class="popup" style="display: none;">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-info-circle" style="color: var(--warning);"></i>
            </div>
            <h2 class="popup-title">Assessment Already Taken</h2>
            <p class="popup-text">You have already taken the assessment. You cannot retake it.</p>
            <button class="btn btn-secondary" onclick="document.getElementById('already-taken-popup').style.display='none';">Close</button>
        </div>
    </div>

    <div id="permission-popup" class="popup" style="display: none;">
        <div class="popup-content">
            <div class="popup-icon"><i class="fas fa-video" style="color: #3b82f6;"></i></div>
            <h2 class="popup-title">Enable Camera & Microphone</h2>
            <p class="popup-text">
                We need access to your camera and microphone to ensure the integrity of the assessment process.
            </p>
            <button class="btn btn-primary" onclick="requestMediaPermissions()">Allow Access</button>
            <br><br>
            <button class="btn btn-secondary" onclick="closePermissionPopup()">Cancel</button>
        </div>
    </div>

    <div id="permission-denied-popup" class="popup" style="display: none;">
        <div class="popup-content">
            <div class="popup-icon"><i class="fas fa-times-circle" style="color: #ef4444;"></i></div>
            <h2 class="popup-title">Permission Denied</h2>
            <p class="popup-text">
                You must allow camera and microphone access to proceed with the assessment.
            </p>
            <button class="btn btn-secondary" onclick="closePermissionDeniedPopup()">Close</button>
        </div>
    </div>
    
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('permission_denied') === '1') {
                document.getElementById("permission-denied-popup").style.display = "flex";

                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        });
        function showQuizPopup() {
            document.getElementById('instructions-popup').style.display = 'flex';
        }
    
        function continueToQuizPopup() {
            document.getElementById('instructions-popup').style.display = 'none';
            document.getElementById('permission-popup').style.display = 'flex';
        }
    
        function closeInstructionsPopup() {
            document.getElementById('instructions-popup').style.display = 'none';
        }
    
        function showAlreadyTakenPopup() {
            document.getElementById('already-taken-popup').style.display = 'flex';
        }
        function showAlreadyTakenPopup() {
            document.getElementById('already-taken-popup').style.display = 'flex';
        }

        function requestMediaPermissions() {
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(function (stream) {
                    stream.getTracks().forEach(track => track.stop());

                    document.getElementById('permission-popup').style.display = 'none';
                    document.getElementById('quiz-popup').style.display = 'flex';
                })
                .catch(function (error) {
                    document.getElementById('permission-popup').style.display = 'none';
                    document.getElementById('permission-denied-popup').style.display = 'flex';
                });
        }

        function closePermissionPopup() {
            document.getElementById('permission-popup').style.display = 'none';
        }

        function closePermissionDeniedPopup() {
            document.getElementById('permission-denied-popup').style.display = 'none';
        }
    </script>
</body>
</html>