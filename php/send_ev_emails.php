<?php
$servername = "localhost";
$username = "u707137586_EV_Reg_T1_24";
$password = "DMKL0IYoP&4";
$dbname = "u707137586_EV_Reg_2024_T1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, email FROM test WHERE emailSent = false";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $name = $row['name'];
    $email = $row['email'];

    $subject = "Accelerate Your Career in EV Tech - Join VisionAstraa EV Academy's Next Batch!";

    $message = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>VisionAstraa EV Academy Progress Update</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            line-height: 1.6;
                            margin: 0;
                            padding: 0;
                            background-color: #f4f4f4;
                            color: #333;
                        }
                        .container {
                            width: 80%;
                            margin: 0 auto;
                            background-color: #ffffff;
                            padding: 20px;
                        }
                        h1, h2, h3 {
                            color: #4CAF50;
                        }
                        a {
                            color: #444;
                            font-weight: bold;
                            text-decoration: underline;
                        }
                        a:hover {
                            color: #333;
                        }
                        .section {
                            margin-bottom: 20px;
                        }
                        .highlight {
                            color: #ff5722;
                            font-weight: bold;
                        }
                        .cta-button {
                            background-color: #4CAF50;
                            color: #fff;
                            padding: 10px 20px;
                            text-align: center;
                            border-radius: 5px;
                            display: inline-block;
                            text-decoration: none;
                        }
                        .cta-button:hover {
                            background-color: #45a049;
                        }
                        .cta-container {
                            text-align: center;
                            margin: 20px 0;
                        }
                        .footer {
                            text-align: center;
                            font-size: 0.8em;
                            color: #777;
                            margin-top: 40px;
                        }
                        .image-container {
                            display: flex;
                            flex-wrap: wrap; 
                            gap: 20px;
                            justify-content: space-between; 
                            margin-top: 20px;
                        }

                        .responsive-image {
                            flex: 1 1 48%; 
                            max-width: 48%; 
                            height: auto;
                            display: block;
                            margin: 0 auto;
                        }
                        @media screen and (max-width: 768px) {
                            .responsive-image {
                                flex: 1 1 100%;
                                max-width: 100%;
                                margin-bottom: 20px;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>Dear EV Aspirant,</h1>

                        <p>We are delighted to share the progress of VisionAstraa EV Academy since our inaugural batch commenced on <strong>November 25, 2024</strong>.</p>

                        <p>We have welcomed 19 talented students from ECE, EEE, and Mechanical backgrounds from Karnataka, Kerala, and Telangana, holding both B.Tech and M.Tech degrees, into our <strong>4-month EV PowerTrain Mastery Program</strong>.</p>

                        <h2>VisionAstraa EV Academy Inauguration on Nov 25, 2024</h2>
                        <div class="image-container">
                            <img src="https://www.visionastraa.com/image/41971bbe.jpeg" alt="Inauguration Image 1" class="responsive-image">
                        </div>
                        <div class="image-container">
                            <img src="https://www.visionastraa.com/image/8e1be70b.jpeg" alt="Inauguration Image 2" class="responsive-image">
                        </div>
                        <p>With <b>Nagresh Basavanhalli,</b> <b>Non-Executive Vice Chairman;</b> <a href="https://www.linkedin.com/company/greaves-cotton-limited/">Greaves Cotton Limited;</a> <a href="https://www.linkedin.com/in/rajrajappan/">RAJ Rajappan Gounder</a>, <b>Co-Founder & CTO, NSure Marine Solutions;</b> <a href="https://www.linkedin.com/in/zohra-khan-889a8a14a/">Zohra Khan</a>, <b>CEO,</b> <a href="https://www.linkedin.com/company/ipec-india-private-limited/">IPEC India;</a> and <a href="https://www.linkedin.com/in/tobias-a-nowak/">Tobias A. Nowak</a>, <b>Head of Engineering,</b> <a href="https://www.linkedin.com/company/segautomotive/">SEG Automotive</a> as Chief Guests for the event.</p>
                        <div class="image-container">
                            <img src="https://www.visionastraa.com/image/e4c35d3a.jpeg" class="responsive-image">
                        </div>
                        <p>EV Academy Faculty & Students with <a href="https://www.linkedin.com/in/omkumar95184/">Om Kumar</a>, Head Advanced Engineering, <a href="https://www.linkedin.com/company/switchmobilityev/">Switch Mobility</a>, <a href="https://www.linkedin.com/in/shreyas-krishna-seethapathy-9834983/">Shreyas Krishna Seethapathy</a>, Chief of Staff, Engineering <a href="https://www.linkedin.com/company/ather-energy/">Ather Energy</a>, <a href="https://www.linkedin.com/in/pramodbn/"> Pramod Nanjundaswamy</a>, Vice President, <a href="https://www.linkedin.com/company/cyient/">Cyient</a>; <b>Rahul Plavullathil</b>, Head of Driveline, <b>FPT Industrial (Italy).</b></p>
                        <div class="image-container">
                            <img src="https://www.visionastraa.com/image/34ba1532.jpeg" class="responsive-image">
                        </div>
                        <p>EV Academy students with <b>Mr. Punit Chadha</b>, Senior Vice President, <b>Maruti Suzuki India Limited</b></p>

                        <p>These students are currently engaged in rigorous training on <b>EV PowerTrain technologies</b>, encompassing EV System Architecture, Battery & Battery Management Systems, Power Electronics, Embedded Systems for EV, E-Axle design, E-Motor & controller, and more. Our program also incorporates practical lab sessions to provide hands-on experience.</p>

                        <p>We have been fortunate to host several company officials <b>(Ather Energy, FPT industrial (Italy), Switch Mobility, SEG Automotive (Germany), IPEC India Pvt Ltd, Maruti, TVS Motors, Cyient, etc.)</b> at EV Academy, who have interacted with our students and shared valuable industry insights.</p>

                        <p>Additionally, our students have had the opportunity to participate in industrial visits to companies such as <b>IPEC India Pvt. Ltd, Ather Energy, SEG Automotive and Mecwin Motors,</b> gaining exposure to real-world applications of EV technology.</p>
                        <div class="image-container">
                            <img src="https://www.visionastraa.com/image/ac32dfab.jpeg" class="responsive-image">
                        </div>
                        <div class="image-container">
                            <img src="https://www.visionastraa.com/image/8b5fad7d.jpeg" class="responsive-image">
                        </div>
                        <h3>Placement Opportunities</h3>
                        <p>We are pleased to announce that several companies are hiring our EV Academy students upon completion of the program: </p>
                        <ol>
                            <li>Sarala Aviation</li>
                            <li>JBM Group</li>
                            <li>MecWin Motors</li>
                            <li>IPEC India Pvt. Ltd.</li>
                            <li>Montra Electric</li>
                            <li>Ather Energy</li>
                            <li>Auto-Lek</li>
                            <li>Switch Mobility</li>
                            <li>Godawari Electric Motor Private Ltd</li>
                        </ol>
                        
                        <p>Dont miss this <span class="highlight">GOLDEN opportunity</span> to join our upcoming batch starting in mid-April 2025 and secure a job in just 4 months!</p>
                        <div class="image-container">
                            <img src="https://www.visionastraa.com/image/133ddeb8.jpeg" class="responsive-image">
                        </div>

                        <hr>

                        <h3>If you are still looking for a job in EV Technologies, what are you waiting for?</h3>
                        <div class="cta-container">
                            <a href="https://www.visionastraa.com/ev-application.html" class="cta-button">APPLY NOW</a>
                        </div>

                        <div class="image-container">
                            <img src="https://www.visionastraa.com/image/ev-apply-email.png" alt="Upskill in EV Technologies" style="width:100%; max-width:600px; margin-top:8px" class="responsive-image">
                        </div>

                        <h3>Why Choose VisionAstraa EV Academy?</h3>
                        <ul>
                            <li><b>100% placement in the EV industry</b></li>
                            <li><b>ASDC, ESSCI & Skill India Certifications</b></li>
                            <li><b>Hands-On Practical Oriented Training</b></li>
                            <li><b>Industry Expert Seminars from top companies like Mercedes Benz, Fiat PowerTrain, etc.</b></li>
                            <li><b>Industrial Visits to Leading EV Companies</b></li>
                            <li><b>Global Mentor support with an average of 20+ years in the automotive industry</b></li>
                        </ul>

                        <h3> Join Our Whatsapp Group for More Details</h3>
                        <p>Get updates on the program, potential job opportunities, webinars on EV-related topics, and more! Join Now -</p>
                        <div class="cta-container">
                            <a href="https://chat.whatsapp.com/EhvWb9kldqI7Np2MbfCW3u" class="cta-button">EV Academy Whatsapp Group</a>
                        </div>

                        <div class="footer">
                            <p>Thanks, <br><strong><a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a></strong>,<br>Founder & CEO, <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>+91-8197355166</p>
                        </div>
                    </div>
                </body>
                </html>';

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: VisionAstraa Group <recruitment@visionastraa.com>" . "\r\n";

    if (mail($email, $subject, $message, $headers)) {
        echo "Email sent to: $name ($email)\n";

        $updateSql = "UPDATE webinar_batch_7 SET emailSent = true WHERE email = '$email'";
        $conn->query($updateSql);
    } else {
        echo "Failed to send email to: $name ($email)\n";
    }
}

$conn->close();
?>
