<?php
$servername = "localhost";
$username = "u707137586_EV_Reg_T1_24";
$password = "DMKL0IYoP&4";
$dbname = "u707137586_EV_Reg_2024_T1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, email, score, percentage FROM quiz_data_4 WHERE percentage < 41 AND emailSent = false";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $name = $row['name'];
        $recipient_email = $row['email'];
        $score = $row['score'];
        $percentage = $row['percentage'];

        $message = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f9f9f9;
                    color: #333;
                    padding: 20px;
                }
                .container {
                    background-color: #fff;
                    max-width: 600px;
                    margin: 20px auto;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                h2 {
                    color: #4CAF50;
                    margin: 0px;
                }
                h4 {
                    margin-bottom: 8px;
                }
                p{
                    margin-bottom: 0px;
                    margin-top: 8px;
                }
                table {
                    width: 60%;
                    border-collapse: collapse;
                }
                table, th, td {
                    border: 1px solid #ddd;
                }
                th, td {
                    padding: 2px;
                    text-align: left;
                }
                .highlight {
                    color: #000000;
                    font-weight: bold;
                }
                .cta {
                    padding: 10px;
                    background-color: #4CAF50;
                    color: white;
                    text-align: center;
                    border-radius: 5px;
                    width: 200px;
                    text-decoration: none;
                    display: inline-block;
                    font-weight: bold;
                }
                .linkedin-link {
                    display: inline-block;
                    padding: 10px;
                    background-color: #0077B5;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                }
                .visionastraa-list {
                    padding-left: 20px;
                    text-align: left;
                    margin-left: 16px;
                }

                .visionastraa-list li {
                    margin-bottom: 10px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <p>Hello ' . htmlspecialchars($name) . ',</p>
                <p><b>Thank you very much for your interest in applying for our open positions for VisionAstraa EV Startup.</b></p>
                <p>Your Score from the <b>Online Skill Assessment Test for VisionAstraa EV Startup</b> is:</p>
                <h2 class="highlight">' . htmlspecialchars($score) . '</h2>
                <p>Even though your score is Low, we want to give you a second chance especially if you have a CGPA >7.0 in your B.Tech/M.Tech and still very interested to build a career in EV Industry.</p>
                <br>
                <p>************************************************************************</p>
                <!-- 
                <img src="https://www.visionastraa.com/image/quiz-graph.jpg" alt="Upskill in EV Technologies" style="width:70%; max-width:600px; margin-top:18px">
                <h4><u>Criteria</u></h4>
                <table>
                    <tr>
                        <th>Marks</th>
                        <th>Status</th>
                    </tr>
                    <tr>
                        <td>96-100</td>
                        <td style="background-color: #2dc26b;">Qualified for Next Round</td>
                    </tr>
                    <tr>
                        <td>71-95</td>
                        <td style="background-color: #f1c40f;">Good, but failed to make cut. Can be considered after upskilling</td>
                    </tr>
                    <tr>
                        <td>41-70</td>
                        <td style="background-color: #e67e23;">Pass, but upskilling required</td>
                    </tr>
                    <tr>
                        <td>0-40</td>
                        <td style="background-color: #e03e2d;">Failed</td>
                    </tr>
                </table>
            -->
                <h3><b>We will offer you a job in our company if you can graduate from our EV Academy in Bengaluru!</b></h3>
                <p><b><u>Placements</u></b></p>
                <p>100% Placement from 2 ways:</p>
                <ol class="visionastraa-list">
                    <li>Top students from EV Academy would be absorbed in VisionAstraa EV Startup ("Charge")!</li>
                    <li>Top EV companies from India and abroad will be hiring our upskilled students from EV Academy. (Why? - Because EV is a brand new booming industry & there is lack of skilled engineers both freshers & even experienced people).</li>
                </ol>
                <img src="https://www.visionastraa.com/image/ev-apply-email.png" alt="Upskill in EV Technologies" style="width:100%; max-width:600px; margin-top:8px">
                <p>For more details on the program, modules, mentors & potential recruiters, you can check our <a href="https://drive.google.com/file/d/1HJKflv-SE8R8_P4vkXeWw8N-cjSMfW_n/view?usp=sharing">Brochure</a> & <a href="https://www.visionastraa.com/ev-academy.html">Website</a></p>
                <p><b>Hurry Now! Limited Opportunity!</b></p>
                <p><b>We have a small intake of only 25, with limited seats for the thousands of applications we receive nationwide.</b></p>
                <p>Our first batch starts mid-November:</p>
                <a href="https://www.visionastraa.com/ev-application.html" style="padding: 10px; background-color: #4CAF50; color: white; text-align: center; border-radius: 5px; width: 200px; text-decoration: none; display: inline-block; font-weight: bold; margin-top:8px;">Reserve Your Spot!</a>
                <p>For any questions & further info, connect with us on LinkedIn:</p>
                <a href="https://www.linkedin.com/company/va-ev-academy" style="display: inline-block; padding: 10px; background-color: #0077B5; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top:8px;">Follow us on LinkedIn</a>
                <p>--</p>
                <p>Thanks,<br>Recruitment Team<br><a href="https://www.linkedin.com/company/visionastraa/">VisionAstraa Group</a></p>
            </div>
        </body>
        </html>';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: VisionAstraa Group <recruitment@visionastraa.com>" . "\r\n";

        if (mail($recipient_email, "Your EV Startup Assessment Result", $message, $headers)) {
            $update_sql = "UPDATE quiz_data_4 SET emailSent = true WHERE email = '$recipient_email'";
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
