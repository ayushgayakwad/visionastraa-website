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
                <h2>About VisionAstraa Group</h2>
                <p>VisionAstraa Group has multiple companies under it. We work with multiple startups from different industry verticals to build and scale their companies. We also help train, mold engineering students from second year onwards to build their startups.</p>
                <div class="quote-container">
                    <img src="image/QF.png" alt="quote" class="quote">
                </div>
                <h3>Our Portfolio Companies:</h3>
                <div class="container">
                    <div class="portfolio-container">
                        <div class="row">
                            <div class="col-md-4">
                                <ul class="visionastraa-list">
                                    <li>VisionAstraa Startup Academy</li>
                                    <li>Milk Magic Pvt. Ltd.</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="visionastraa-list">
                                    <li>EV Startup (Stealth Mode)</li>
                                    <li>VisionAstraa EV Academy <br>(launching soon)</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="visionastraa-list">
                                    <li>VisionAstraa Events (Stealth Mode)</li>
                                    <li>Vet Pharma Company <br>(Incorporation Stage)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section class="about-company">
            <div class="about-company-box">
                <h2>Gallery</h2>
                <div class="row gallery">
                    <div class="col-md-4">
                        <a href="image/ic4.png" target="_blank"><img src="image/ic4.jpg" alt="Image 1"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic3.jpg" target="_blank"><img src="image/ic3.jpg" alt="Image 2"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic2.jpg" target="_blank"><img src="image/ic2.jpg" alt="Image 3"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic12.jpg" target="_blank"><img src="image/ic12.jpg" alt="Image 4"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic14.jpg" target="_blank"><img src="image/ic14.jpg" alt="Image 5"></a>
                    </div>
                    <div class="col-md-4">
                        <a href="image/ic8.jpg" target="_blank"><img src="image/ic8.jpg" alt="Image 6"></a>
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
                    <button id="quizInstructions" onclick="goToQuizInstructions()">View Test Instructions</button>
                </div>
            </div>
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
