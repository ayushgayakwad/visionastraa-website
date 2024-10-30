<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Recruitment Drive for 2023/24 Freshers</title>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2PV0BKLV94"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-2PV0BKLV94');
    </script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/crdf2324f3.css">
</head>
<body>
    <main>
        <section class="about-visionastraa">
            <div class="about-visionastraa-learning-outcomes text-center">
                <h2>VisionAstraa Group - EV Startup (Stealth Mode)</h2>
                <p>Welcome! You're About to Begin the Online Screening Test for Open Positions at our EV Startup.</p>
            </div>
        </section>

        <section class="about-company">
            <div class="about-company-box">
                <p> Read the instructions carefully to ensure a smooth and hassle-free experience.</p>
                <ul>
                    <li><b>Test Duration:</b> <br>&emsp;You have 15 minutes to complete the test. The timer will start as soon as you begin the test.</li>
                    <li><b>Number of Questions:</b> <br>&emsp;The test includes 30 multiple-choice questions, each with varying points, and your total score will be out of 100.</li>
                    <li><b>Test Access:</b> <br>&emsp;You are allowed to take the test only once. If you exit the test before completing it, you will not be able to retake it.</li>
                    <li><b>Starting the Test:</b> <br>&emsp;Click on the “Start Test” button to begin. Ensure you are ready to complete the test in one sitting.</li>
                    <li><b>Answering Questions:</b> <br>&emsp;For each question, select the option that you believe is correct. You can change your answer at any time before submitting the test.</li>
                    <li><b>Submitting the Test:</b> <br>&emsp;Once you have answered all the questions, click the “Submit" button to finalize your answers. Submitting the test will end your session.</li>
                    <li><b>Time Management:</b> <br>&emsp;Keep an eye on the timer displayed on the screen. You will be automatically logged out once the time limit is reached.</li>
                    <li><b>Exiting the Test:</b> <br>&emsp;If you exit the test, you will not be able to return or retake it. Ensure you have sufficient time and a stable internet connection before starting.</li>
                    <li><b>Academic Integrity:</b> <br>&emsp;Ensure that you complete the test independently. Collaboration or cheating will not be tolerated.</li>
                </ul>
            </div>
        </section>

        <section class="quiz-instructions">
            <div class="quiz-instructions-box">
                <div class="button-container">
                    <button id="quizInstructions" onclick="goToQuiz()">Start Test</button>
                </div>
            </div>
            <p class="instruction-text">Click "Start Test" to begin the test. <br>Best Wishes - VisionAstraa Recruitment Team</p>
        </section>
    </main>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function goToQuiz() {
            window.location.href = 'http://form-timer.com/start/3536bbbb';
        }
        document.addEventListener("DOMContentLoaded", function () {
            const token = localStorage.getItem('token');
            const tokenExpiry = localStorage.getItem('expires_at');
            if (token && tokenExpiry && new Date().getTime() < tokenExpiry) {
                console.log("Token valid");
            } else {
                console.log("Token expired or missing");
                window.location.href = 'crdf2324f.php';
            }
        });
    </script>    
</body>
</html>
