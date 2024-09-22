<?php
$servername = "localhost";
$username = "u707137586_EV_Reg_T1_24";
$password = "DMKL0IYoP&4";
$dbname = "u707137586_EV_Reg_2024_T1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, email, score, percentage FROM test WHERE percentage > 40 AND percentage < 96 AND emailSent = false";
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
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                table, th, td {
                    border: 1px solid #ddd;
                }
                th, td {
                    padding: 10px;
                    text-align: left;
                }
                .highlight {
                    color: #000000;
                    font-weight: bold;
                }
                .cta {
                    margin-top: 20px;
                    padding: 10px;
                    background-color: #4CAF50;
                    color: white;
                    text-align: center;
                    border-radius: 5px;
                    text-decoration: none;
                    display: inline-block;
                }
                .whatsapp-link {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px;
                    background-color: #25D366;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <p>Hello ' . htmlspecialchars($name) . ',</p>
                <p><b>Thank you very much for your interest in applying for our open positions for EV Startup.</b></p>
                <p>Your Score from the recently conducted <b>Online Skill Assessment Test for VisionAstraa EV Startup</b> is:</p>
                <h2 class="highlight">' . htmlspecialchars($score) . '</h2>
                <p>At this point, you havent cleared the cut-off required to be called for the next round of technical interviews for our EV Startup.</p>
                <h3>Criteria</h3>
                <table>
                    <tr>
                        <th>Marks</th>
                        <th>Status</th>
                    </tr>
                    <tr>
                        <td>96-100</td>
                        <td>Qualified for Next Round</td>
                    </tr>
                    <tr>
                        <td>71-95</td>
                        <td>Good, but failed to make cut. Can be considered after upskilling</td>
                    </tr>
                    <tr>
                        <td>41-70</td>
                        <td>Pass, but upskilling required</td>
                    </tr>
                    <tr>
                        <td>0-40</td>
                        <td>Failed</td>
                    </tr>
                </table>
                <p>************************************************************************</p>
                <p><b>Have you considered upskilling yourself for a career in EV Technologies?</b></p>
                <img src="https://www.visionastraa.com/image/ev-apply-email.png" alt="Upskill in EV Technologies" style="width:100%; max-width:600px;">
                <p>If interested to learn & upskill yourself in EV Technologies, <br>you can apply to <a href="https://visionastraa.com/ev-home.html">EV Academy</a> to be considered for our next batch starting in mid-October - Apply Now (after clearing Technical Interview).</p>
                <p><b>Well be absorbing top students with 90%+ marks who get trained from the EV Academy into our EV Startup and also offering 100% placement to all students.</b></p>
                <a href="https://www.visionastraa.com/ev-application.html" class="cta">Apply Now</a>
                <p><b>👉 Join Our Whatsapp Group for More Details</b></p>
                <p>Get updates on the program, potential job opportunities, webinars on EV-related topics and more!</p>
                <a href="https://chat.whatsapp.com/EhvWb9kldqI7Np2MbfCW3u" class="whatsapp-link">Join Now - EV Academy Whatsapp Group</a>
                <br><br>
                <p>--</p>
                <p>Thanks,<br>Recruitment Team<br><a href="https://www.linkedin.com/company/visionastraa/">VisionAstraa Group</a></p>
            </div>
        </body>
        </html>';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: recruitment@visionastraa.com" . "\r\n";

        if (mail($recipient_email, "Your EV Startup Assessment Result", $message, $headers)) {
            $update_sql = "UPDATE test SET emailSent = true WHERE email = '$recipient_email'";
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
