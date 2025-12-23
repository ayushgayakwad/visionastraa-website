<?php
header('Content-Type: application/json');

// --- CONFIGURATION ---
// Credentials mapped to ID from the frontend dropdown
$credentials = [
    1 => [
        "EMAIL" => "visionastraa@evinternships.com",
        "PASSWORD" => "a[kE?V6lm7G=",
        "HOST" => "smtp.hostinger.com",
        "PORT" => 465
    ],
    2 => [
        "EMAIL" => "visionastraa@evinternships.in",
        "PASSWORD" => "]9jw>Upu//Y",
        "HOST" => "smtp.hostinger.com",
        "PORT" => 465
    ]
];

// --- MAIN LOGIC ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
    exit;
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$role = $_POST['role'] ?? '';
$accountId = intval($_POST['account_id'] ?? 1);
$pdfBase64 = $_POST['pdf_data'] ?? '';

if (empty($name) || empty($email) || empty($pdfBase64) || !isset($credentials[$accountId])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields or invalid account.']);
    exit;
}

$creds = $credentials[$accountId];
$pdfContent = base64_decode($pdfBase64);

// Construct Email Body (HTML)
$subject = "Internship OFFER LETTER from VisionAstraa EV Academy";
$body = "
<html>
<head><style>body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }</style></head>
<body>
    <p>Hello <strong>$name</strong>,</p>
    <p><strong>CONGRATULATIONS</strong> for getting selected for Internship in <strong>$role</strong> and your starting date is <strong>January, 2026</strong>.</p>
    
    <p><strong>Kindly accept the offer in the VTU Portal by paying the internship acceptance fees and follow the steps below:</strong></p>
    <ul>
        <li>Enter the URL (<a href='https://vtu.internyet.in'>https://vtu.internyet.in</a>)</li>
        <li>Login using your username and password</li>
        <li>Navigate to `Applied Internships` section in the dashboard</li>
        <li>Click on the `Accept` button to accept <strong>VisionAstraa EV Academy's offer</strong>.</li>
    </ul>

    <p><strong>Please note: Kindly accept this offer within 5 days from the date of this email. Offers not accepted within this period will be considered expired.</strong></p>

    <p>We also request you to fill out the following application form at your earliest convenience:</p>
    <p><strong>Application Form Link:</strong> <a href='https://visionastraa.com/ev-internship-application.html'>https://visionastraa.com/ev-internship-application.html</a></p>
    <p>In the form, please select <strong>January 2026</strong> as commencement date and confirm your VTU portal acceptance.</p>

    <p>To ensure a smooth onboarding process, please join the WhatsApp group:<br>
    <strong>January 2026:</strong> <a href='https://chat.whatsapp.com/JJc51uchsDpHKPdRRA091Q?mode=hqrt3'>Join Group</a></p>

    <p>For any queries, reach out to us on LinkedIn: <a href='https://in.linkedin.com/company/va-ev-academy'>VisionAstraa EV Academy</a><br>
    Or call us on: <a href='tel:+918762246518'>+91 87622 46518</a></p>

    <p>Find your offer letter attached below.</p>
    <p>Looking forward to having you onboard!<br>Happy Interning!</p>
</body>
</html>
";

// --- SMTP SENDING ---
try {
    $result = sendSmtpEmail($creds, $email, $subject, $body, $pdfContent, "Offer_Letter.pdf");
    if ($result === true) {
        echo json_encode(['success' => true, 'message' => "Email sent successfully to $email"]);
    } else {
        echo json_encode(['success' => false, 'message' => "SMTP Error: $result"]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Exception: " . $e->getMessage()]);
}


// --- LIGHTWEIGHT SMTP FUNCTION (No Dependencies) ---
function sendSmtpEmail($creds, $to, $subject, $htmlBody, $attachmentData, $attachmentName) {
    $smtpHost = "ssl://" . $creds['HOST']; 
    $smtpPort = $creds['PORT'];
    $username = $creds['EMAIL'];
    $password = $creds['PASSWORD'];
    
    $boundary = "mixed-" . md5(time());
    $eol = "\r\n";

    // Socket Connection
    $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
    if (!$socket) return "Connection failed: $errstr ($errno)";

    $log = "";
    $log .= read_smtp($socket); // Banner

    fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . $eol);
    $log .= read_smtp($socket);

    fputs($socket, "AUTH LOGIN" . $eol);
    $log .= read_smtp($socket);

    fputs($socket, base64_encode($username) . $eol);
    $log .= read_smtp($socket);

    fputs($socket, base64_encode($password) . $eol);
    $response = read_smtp($socket);
    if (strpos($response, '235') === false) return "Auth failed: $response";

    fputs($socket, "MAIL FROM: <$username>" . $eol);
    read_smtp($socket);

    fputs($socket, "RCPT TO: <$to>" . $eol);
    read_smtp($socket);

    fputs($socket, "DATA" . $eol);
    read_smtp($socket);

    // Headers
    $headers = "Date: " . date('r') . $eol;
    $headers .= "To: <$to>" . $eol;
    $headers .= "From: VisionAstraa EV Academy <$username>" . $eol;
    $headers .= "Subject: $subject" . $eol;
    $headers .= "MIME-Version: 1.0" . $eol;
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"" . $eol;
    
    $message = "$headers$eol--$boundary$eol";
    
    // HTML Body
    $message .= "Content-Type: text/html; charset=UTF-8$eol";
    $message .= "Content-Transfer-Encoding: 7bit$eol$eol";
    $message .= "$htmlBody$eol$eol";
    
    // Attachment
    $message .= "--$boundary$eol";
    $message .= "Content-Type: application/pdf; name=\"$attachmentName\"$eol";
    $message .= "Content-Transfer-Encoding: base64$eol";
    $message .= "Content-Disposition: attachment; filename=\"$attachmentName\"$eol$eol";
    $message .= chunk_split(base64_encode($attachmentData)) . $eol;
    $message .= "--$boundary--$eol";
    $message .= "." . $eol;

    fputs($socket, $message);
    $result = read_smtp($socket);

    fputs($socket, "QUIT" . $eol);
    fclose($socket);

    if (strpos($result, '250') !== false) {
        return true;
    } else {
        return "Send failed: $result";
    }
}

function read_smtp($socket) {
    $response = "";
    while ($str = fgets($socket, 515)) {
        $response .= $str;
        if (substr($str, 3, 1) == " ") break;
    }
    return $response;
}
?>