<?php
$servername = "localhost";
$username = "u707137586_EV_Reg_T1_24";
$password = "DMKL0IYoP&4";
$dbname = "u707137586_EV_Reg_2024_T1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, email FROM webinar_batch_7 WHERE emailSent = false";
$result = $conn->query($sql);

$meetingDate = "03-NOV-2024";
$meetingTime = "01:00 PM IST";
$meetingLink = "https://meet.google.com/kuw-dzhf-adp";

$icalContent = "BEGIN:VCALENDAR\r\n" .
               "VERSION:2.0\r\n" .
               "BEGIN:VEVENT\r\n" .
               "SUMMARY:Webinar on Career Opportunities in EV Industry\r\n" .
               "DTSTART;TZID=Asia/Kolkata:20241103T130000\r\n" .
               "DTEND;TZID=Asia/Kolkata:20241103T133000\r\n" .
               "LOCATION:$meetingLink\r\n" .
               "DESCRIPTION: Join us for an online webinar on Career Opportunities in EV Industry.\r\n" .
               "URL:$meetingLink\r\n" .
               "ORGANIZER;CN=VisionAstraa Group:mailto:recruitment@visionastraa.com\r\n" .
               "BEGIN:VALARM\r\n" .
               "TRIGGER:-PT15M\r\n" . 
               "DESCRIPTION:Reminder: Webinar on Career Opportunities in EV Industry\r\n" .
               "ACTION:DISPLAY\r\n" .
               "END:VALARM\r\n" .
               "END:VEVENT\r\n" .
               "END:VCALENDAR";

while ($row = $result->fetch_assoc()) {
    $name = $row['name'];
    $email = $row['email'];

    $subject = "Invitation to Webinar on Career Opportunities in EV Industry";

    $body = "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Webinar Invitation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        h3 {
            color: #333;
        }
        p {
            color: #555;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h2>Hello <span style='color: #007BFF;'>$name</span>,</h2>
        <h3>Thankyou for applying for VisionAstraa EV Academy.</h3>
        <p>We are inviting you to a 30-min Online Webinar on \"<strong>Career Opportunities in EV Industry in India & Abroad</strong>\" with <strong>Mr. Rahul Sagar</strong>, Head of Driveline, Fiat PowerTrain (FPT) Industrial, Italy on <span style='color: #007BFF;'>$meetingDate</span>.</p>
        <ul>
            <li><strong>Main Panelist:</strong> Mr. <a href='https://www.linkedin.com/in/rahul-p-1588b915/' class='link'>Rahul Sagar Plavullathil</a>, Head of Driveline, Fiat PowerTrain (FPT) Industrial, Italy (17+ yrs of Automotive industry experience across USA, Germany & Italy)</li>
            <li><strong>Moderator:</strong> Mr. <a href='https://www.linkedin.com/in/yedu-jathavedan/' class='link'>Yedu Jathavedan</a>, Senior Engineering Manager, Intel Corporation, Bengaluru (17+ years of industry experience in USA & India)</li>
        </ul>
        
        <p><strong>Join Google Meet</strong> - <a href='$meetingLink'>$meetingLink</a><br>
        <strong>When</strong>: <span style='color: #007BFF;'>$meetingDate</span><br>
        <strong>Time</strong>: <span style='color: #007BFF;'>$meetingTime</span></p>

        <p>In this session, you would learn about the importance of the Automotive Industry, future growth opportunities, career prospects, Job Profiles in VisionAstraa EV Startup & roles in EV Industry!</p>

        <p>Don't miss this highly informative session.</p>

        <div>
            <p>--<br>
            Thanks,<br>
            Recruitment Team<br>
            <a href='https://www.linkedin.com/company/visionastraa' class='link'>VisionAstraa Group</a>
        </div>
    </div>
</body>
</html>";

    $boundary = md5(time());
    $headers = "From: VisionAstraa Group <recruitment@visionastraa.com>\r\n" .
               "Reply-To: recruitment@visionastraa.com\r\n" .
               "MIME-Version: 1.0\r\n" .
               "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    $message = "--$boundary\r\n" .
               "Content-Type: text/html; charset=UTF-8\r\n" .
               "Content-Transfer-Encoding: 8bit\r\n\r\n" .
               $body . "\r\n\r\n" .
               "--$boundary\r\n" .
               "Content-Type: text/calendar; method=REQUEST; charset=UTF-8\r\n" .
               "Content-Transfer-Encoding: 8bit\r\n" .
               "Content-Disposition: attachment; filename=\"invite.ics\"\r\n\r\n" .
               $icalContent . "\r\n\r\n" .
               "--$boundary--";

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
