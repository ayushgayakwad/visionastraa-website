<?php
$servername = "";
$username = "";
$password = "";
$dbname = "";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, email FROM your_table WHERE emailSent = false LIMIT 100";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $id = $row['id'];

        $subject = "VisionAstraa Online Skill Assessment Test for EV Startup";
        $message = "Hello,\n\nAs part of our ongoing process, we kindly request you to complete a test that will help us evaluate your skills and suitability for the next stage of our program.\n\nPlease use the following link to access the test:\n\nhttps://visionastraa.com/crdf2324f.php\n\nMake sure to complete the test before the timer expires. We advise you to find a quiet environment and ensure you have a stable internet connection before beginning.\n\nIf you face any technical issues, feel free to reach out to us for support.\n\nBest of luck, and we look forward to your results!\n\nBest regards,\nVisionAstraa Team";

        $headers = "From: no-reply@visionastraa.com\r\n";
        
        if (mail($email, $subject, $message, $headers)) {
            $updateSql = "UPDATE your_table SET emailSent = true WHERE id = $id";
            $conn->query($updateSql);
        }
    }
} else {
    echo "No emails to send.";
}

$conn->close();
?>
