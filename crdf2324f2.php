<?php
session_start();
if (!isset($_SESSION['access_granted'])) {
    header('Location: crdf2324f.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Recruitment Drive for 2023/24 Freshers</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/crdf2324f2.css">
    <style>
        .gallery img {
            width: 100%;
            height: auto;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .gallery img:hover {
            transform: scale(1.05);
        }
        .gallery .col-md-4 {
            padding: 10px;
        }
    </style>
</head>
<body>
    <main>
        <section class="about-visionastraa">
            <div class="logo-container">
                <img src="image/transpatent_vsa.png" alt="Logo" class="logo">
            </div>
            <div class="about-visionastraa-learning-outcomes text-center">
                <h2>About VisionAstraa</h2>
                <p>VisionAstraa Group has multiple companies under it. We work with multiple startups from different industry verticals to build and scale their companies. We also help train, mold engineering students from second year onwards to build their startups.</p>
            </div>
        </section>

        <section class="about-company">
            <div class="about-company-box">
                <h2>Gallery</h2>
                <div class="row gallery">
                    <div class="col-md-4">
                        <a href="image/ic4.jpg" target="_blank"><img src="image/ic4.jpg" alt="Image 1"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic2.jpg" target="_blank"><img src="image/ic2.jpg" alt="Image 2"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic3.jpg" target="_blank"><img src="image/ic3.jpg" alt="Image 3"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic9.jpg" target="_blank"><img src="image/ic9.jpg" alt="Image 4"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic5.jpg" target="_blank"><img src="image/ic5.jpg" alt="Image 5"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic6.jpg" target="_blank"><img src="image/ic6.jpg" alt="Image 6"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic7.jpg" target="_blank"><img src="image/ic7.jpg" alt="Image 7"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic8.jpg" target="_blank"><img src="image/ic8.jpg" alt="Image 8"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic10.jpg" target="_blank"><img src="image/ic10.jpg" alt="Image 9"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic1.jpg" target="_blank"><img src="image/ic1.jpg" alt="Image 10"></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="social-links">
            <div class="social-links-box">
                <h2>Connect With Us</h2>
                <p>Stay in touch for more job opportunities from VisionAstraa group of companies.</p>
                <div class="button-container">
                    <button id="visitLinkedIn" onclick="visitLinkedIn()">Follow Us on LinkedIn</button>
                </div>
            </div>
        </section>

        <section class="quiz-instructions">
            <div class="quiz-instructions-box">
                <h2>Test Instructions</h2>
                <div class="button-container">
                    <button id="quizInstructions" disabled onclick="goToQuizInstructions()">View Test Instructions</button>
                </div>
            </div>
            <p class="instruction-text">Follow Us on LinkedIn to enable "Test Instructions".</p>
        </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function visitLinkedIn() {
            window.open('https://www.linkedin.com/company/visionastraa', '_blank');
            document.getElementById('quizInstructions').disabled = false;
        }

        function goToQuizInstructions() {
            window.location.href = 'crdf2324f3.php';
        }
    </script>    
</body>
</html>
