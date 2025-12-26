<?php
header('Content-Type: application/json');

// --- DATABASE CONFIGURATION ---
$db_host = 'localhost';
$db_name = 'u707137586_EV_Internships';
$db_user = 'u707137586_EV_Internships';
$db_pass = '+ZkB>V>l;E1';

// --- SMTP CREDENTIALS ---
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

// --- HELPER: DB CONNECTION ---
function getDbConnection() {
    global $db_host, $db_name, $db_user, $db_pass;
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

// --- HELPER: ENSURE TABLE STRUCTURE ---
function ensureTableStructure($pdo) {
    // 1. Create table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS issued_certificates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usn VARCHAR(50),
        name VARCHAR(100),
        role VARCHAR(150),
        certificate_id VARCHAR(50),
        term VARCHAR(50),
        issued_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Check if 'role' column exists (Migration for existing tables)
    try {
        $pdo->query("SELECT role FROM issued_certificates LIMIT 1");
    } catch (Exception $e) {
        // Column likely doesn't exist, add it
        try {
            $pdo->exec("ALTER TABLE issued_certificates ADD COLUMN role VARCHAR(150) AFTER name");
        } catch (Exception $ex) {
            // Ignore if error persists (e.g. table locked), but usually this fixes it
        }
    }
}

$action = $_POST['action'] ?? '';

// --------------------------------------------------------------------------
// ACTION 1: SEARCH INTERN
// --------------------------------------------------------------------------
if ($action === 'search') {
    $usn = $_POST['usn'] ?? '';
    if (empty($usn)) { echo json_encode(['success' => false, 'message' => 'USN is required']); exit; }

    $pdo = getDbConnection();
    if (!$pdo) { echo json_encode(['success' => false, 'message' => 'Database connection failed']); exit; }

    try {
        $stmt = $pdo->prepare("SELECT * FROM internship_payments WHERE usn = ? LIMIT 1");
        $stmt->execute([$usn]);
        $intern = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($intern) {
            echo json_encode(['success' => true, 'data' => $intern]);
        } else {
            // Explicit message for frontend to trigger manual flow
            echo json_encode(['success' => false, 'message' => 'Intern not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
    exit;
}

// --------------------------------------------------------------------------
// ACTION 2: FETCH HISTORY
// --------------------------------------------------------------------------
if ($action === 'fetch_history') {
    $pdo = getDbConnection();
    if (!$pdo) { echo json_encode(['success' => false, 'message' => 'Database connection failed']); exit; }

    try {
        ensureTableStructure($pdo);

        $stmt = $pdo->query("SELECT * FROM issued_certificates ORDER BY issued_at DESC LIMIT 50");
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $history]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
    exit;
}

// --------------------------------------------------------------------------
// ACTION 3: GENERATE AND SEND
// --------------------------------------------------------------------------
if ($action === 'generate_and_send') {
    $usn = $_POST['usn'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $term = $_POST['term'] ?? '';
    $certId = $_POST['cert_id'] ?? '';
    $pdfBase64 = $_POST['pdf_data'] ?? '';
    $accountId = intval($_POST['account_id'] ?? 1);

    if (empty($email) || empty($pdfBase64)) {
        echo json_encode(['success' => false, 'message' => 'Missing email or PDF data.']);
        exit;
    }

    // Select Account
    if (!isset($credentials[$accountId])) {
        echo json_encode(['success' => false, 'message' => 'Invalid Sender Account ID.']);
        exit;
    }
    $creds = $credentials[$accountId];

    $pdfContent = base64_decode($pdfBase64);
    $filename = "Internship_Certificate_$usn.pdf";

    // 1. Send Email
    $subject = "Internship Completion Certificate - VisionAstraa EV Academy";
    $body = "
    <html>
    <head><style>body { font-family: Arial, sans-serif; color: #333; }</style></head>
    <body>
        Dear $name,
        <br><br>
        Greetings from VisionAstraa EV Academy!
        <br><br>
        Congratulations on successfully completing your internship in <strong>$role</strong>.
        <br><br>
        Please find your Internship Completion Certificate attached to this email.
        <br>
        <strong>Certificate ID:</strong> $certId
        <br><br>
        We appreciate your contribution and wish you the very best in your future endeavors.
        <br><br>
        Best Regards,<br>
        <strong>VisionAstraa EV Academy</strong><br>
        <a href='https://visionastraa.com'>www.visionastraa.com</a>
    </body>
    </html>
    ";

    $mailResult = sendSmtpEmail($creds, $email, $subject, $body, $pdfContent, $filename);

    if ($mailResult !== true) {
        echo json_encode(['success' => false, 'message' => "Email Error: $mailResult"]);
        exit;
    }

    // 2. Save to Database (issued_certificates ONLY)
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            ensureTableStructure($pdo);

            $stmt = $pdo->prepare("INSERT INTO issued_certificates (usn, name, role, certificate_id, term) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$usn, $name, $role, $certId, $term]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Email sent, but DB save failed: " . $e->getMessage()]);
            exit;
        }
    }

    echo json_encode(['success' => true, 'message' => 'Certificate sent and recorded successfully.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid Action']);

// --- SMTP FUNCTION ---
function sendSmtpEmail($creds, $to, $subject, $htmlBody, $attachmentData, $attachmentName) {
    $smtpHost = "ssl://" . $creds['HOST']; 
    $smtpPort = $creds['PORT'];
    $username = $creds['EMAIL'];
    $password = $creds['PASSWORD'];
    
    $boundary = "mixed-" . md5(time());
    $eol = "\r\n";

    $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
    if (!$socket) return "Connection failed: $errstr ($errno)";

    read_smtp($socket); 
    fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . $eol);
    read_smtp($socket);
    fputs($socket, "AUTH LOGIN" . $eol);
    read_smtp($socket);
    fputs($socket, base64_encode($username) . $eol);
    read_smtp($socket);
    fputs($socket, base64_encode($password) . $eol);
    $response = read_smtp($socket);
    if (strpos($response, '235') === false) return "Auth failed: $response";

    fputs($socket, "MAIL FROM: <$username>" . $eol);
    read_smtp($socket);
    fputs($socket, "RCPT TO: <$to>" . $eol);
    read_smtp($socket);
    fputs($socket, "DATA" . $eol);
    read_smtp($socket);

    $headers = "Date: " . date('r') . $eol;
    $headers .= "To: <$to>" . $eol;
    $headers .= "From: VisionAstraa EV Academy <$username>" . $eol;
    $headers .= "Subject: $subject" . $eol;
    $headers .= "MIME-Version: 1.0" . $eol;
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"" . $eol;
    
    $message = "$headers$eol--$boundary$eol";
    $message .= "Content-Type: text/html; charset=UTF-8$eol";
    $message .= "Content-Transfer-Encoding: 7bit$eol$eol";
    $message .= "$htmlBody$eol$eol";
    
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

    return (strpos($result, '250') !== false) ? true : "Send failed: $result";
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