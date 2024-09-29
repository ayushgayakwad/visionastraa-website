<?php
$servername = "localhost";
$username = "u707137586_EV_Reg_T1_24";
$password = "DMKL0IYoP&4";
$dbname = "u707137586_EV_Reg_2024_T1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, email FROM type_3_db WHERE emailSent = false";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $name = $row['name'];
        $recipient_email = $row['email'];

        $message = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>EV Startup Test Invitation</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.5;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: white;
                    padding: 20px;
                    border-radius: 5px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                p {
                    margin-top: 0px;
                }
                h2 {
                    color: #333;
                    margin-bottom: 0px;
                }
                .instructions {
                    margin: 20px 0;
                    background-color: #e9ecef;
                    padding: 15px;
                    border-left: 5px solid #007bff;
                }
                .link {
                    color: #0062ca;
                    text-decoration: underline;
                }
                .link:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>Hello ' . htmlspecialchars($name) . ',</h2>
                <p>Congratulations! You have been selected for the online skill assessment test for our EV Startup.</p>

                <div class="instructions">
                    <p><strong>PLEASE READ THE INSTRUCTIONS CAREFULLY:</strong></p>
                    <ul>
                        <li>The <b>test timer will automatically start once</b> you click the below test link & submit your information.</li>
                        <li><b>The registration information can only be submitted ONE time.</b></li>
                        <li>Complete the test in one sitting. <b>DO NOT START THE TEST UNTIL YOU ARE READY TO COMPLETE THE TEST (30 minute duration).</b></li>
                        <li>DO NOT REFRESH your browser.</li>
                        <li>DO NOT click the back button.</li>
                        <li>DO NOT start the test if you dont have a stable internet connection.</li>
                        <li>DO NOT START THE TEST by submitting your information on mobile & then try to connect to a laptop. You must finish it on the same device you started your test with.</li>
                        <li><b>You have 48 hours to complete the test.</b></li>
                    </ul>
                </div>

                <h3><b>Please use the following link to start the test:</b></h3>
                <h1><a href="https://visionastraa.com/crdf2324f.php" class="link">EV Startup Online Test</a></h1>

                <p>Make sure to complete the test before the timer expires. We advise you to find a quiet environment and <b>ensure you have a stable internet connection before beginning.</b></p>

                <p><b>If you face any technical issues, feel free to reach out to us for support on LinkedIn - <a href="https://www.linkedin.com/company/visionastraa" class="link">VisionAstraa Group</a></b></p>

                <p>Best of luck, and we look forward to your results!</p>

                <p>--<br>Thanks,<br>Recruitment Team<br><a href="https://www.linkedin.com/company/visionastraa" class="link">VisionAstraa Group</a></p>
                <img src="https://www.visionastraa.com/image/va_narrow.png" alt="Upskill in EV Technologies" style="width:40%; max-width:600px;">
            </div>
        </body>
        </html>';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: VisionAstraa Group <recruitment@visionastraa.com>" . "\r\n";

        if (mail($recipient_email, "VisionAstraa Online Skill Assessment Test for EV Startup", $message, $headers)) {
            $update_sql = "UPDATE type_3_db SET emailSent = true WHERE email = '$recipient_email'";
            $conn->query($update_sql);
            echo "Email sent to " . htmlspecialchars($recipient_email) . "<br>";
        } else {
            echo "Failed to send email to " . htmlspecialchars($recipient_email) . "<br>";
        }
    }
} else {
    echo "No eligible recipients found.";
}

$conn->close();
?>
