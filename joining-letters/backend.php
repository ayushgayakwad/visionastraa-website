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
    // 1. Create table if it doesn't exist (Specific for Joining Letters)
    $pdo->exec("CREATE TABLE IF NOT EXISTS issued_joining_letters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usn VARCHAR(50),
        name VARCHAR(100),
        role VARCHAR(150),
        letter_id VARCHAR(50),
        term VARCHAR(50),
        issued_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

$action = $_POST['action'] ?? '';

// --------------------------------------------------------------------------
// ACTION: GENERATE UNIQUE ID
// --------------------------------------------------------------------------
if ($action === 'generate_unique_id') {
    $yearCode = $_POST['year_code'] ?? '26';
    $suffix = $_POST['suffix'] ?? 'A';
    
    $pdo = getDbConnection();
    if (!$pdo) { echo json_encode(['success' => false, 'message' => 'Database connection failed']); exit; }
    
    try {
        ensureTableStructure($pdo);
        
        $uniqueId = '';
        $maxRetries = 20;
        $found = false;

        for ($i = 0; $i < $maxRetries; $i++) {
            $rand = rand(100000, 999999);
            // Changed Prefix to VA-JL (VisionAstraa Joining Letter)
            $candidate = "VA-JL" . $yearCode . $rand . $suffix;
            
            // Check if exists
            $stmt = $pdo->prepare("SELECT 1 FROM issued_joining_letters WHERE letter_id = ?");
            $stmt->execute([$candidate]);
            
            if (!$stmt->fetch()) {
                $uniqueId = $candidate;
                $found = true;
                break;
            }
        }

        if ($found) {
            echo json_encode(['success' => true, 'id' => $uniqueId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not generate unique ID. Please try again.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
    exit;
}

// --------------------------------------------------------------------------
// ACTION 1: SEARCH INTERN (Same logic, querying payments)
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
            echo json_encode(['success' => false, 'message' => 'Intern not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
    exit;
}

// --------------------------------------------------------------------------
// ACTION 2: FETCH HISTORY (From joining letters table)
// --------------------------------------------------------------------------
if ($action === 'fetch_history') {
    $pdo = getDbConnection();
    if (!$pdo) { echo json_encode(['success' => false, 'message' => 'Database connection failed']); exit; }

    try {
        ensureTableStructure($pdo);

        $stmt = $pdo->query("SELECT * FROM issued_joining_letters ORDER BY issued_at DESC LIMIT 50");
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
    $letterId = $_POST['letter_id'] ?? '';
    $pdfBase64 = $_POST['pdf_data'] ?? '';
    $accountId = intval($_POST['account_id'] ?? 1);

    if (empty($email) || empty($pdfBase64) || empty($letterId)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data.']);
        exit;
    }

    $pdo = getDbConnection();
    if (!$pdo) { echo json_encode(['success' => false, 'message' => 'Database connection failed']); exit; }

    // --- SAFETY CHECK ---
    try {
        ensureTableStructure($pdo);
        $check = $pdo->prepare("SELECT 1 FROM issued_joining_letters WHERE letter_id = ?");
        $check->execute([$letterId]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Error: This Letter ID was just taken. Please Regenerate.']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'DB Validation Error: ' . $e->getMessage()]);
        exit;
    }

    // Select Account
    if (!isset($credentials[$accountId])) {
        echo json_encode(['success' => false, 'message' => 'Invalid Sender Account ID.']);
        exit;
    }
    $creds = $credentials[$accountId];

    $pdfContent = base64_decode($pdfBase64);
    $filename = "Joining_Letter_$usn.pdf";

    // 1. Send Email (Modified content for Joining Letter)
    $subject = "Internship Joining Confirmation - VisionAstraa EV Academy";
    $body = "
    <html>
    <head><style>body { font-family: Arial, sans-serif; color: #333; }</style></head>
    <body>
        Dear $name,
        <br><br>
        Congratulations! We are pleased to confirm your selection as an intern at VisionAstraa EV Academy.
        <br><br>
        <strong>Role:</strong> $role<br>
        <strong>Reference ID:</strong> $letterId
        <br><br>
        Please find your <strong>Joining Confirmation Letter</strong> attached to this email. It contains details regarding your internship duration and guidelines.
        <br><br>
        We look forward to a productive learning journey with you.
        <br><br>
        Warm Regards,<br>
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

    // 2. Save to Database
    try {
        $stmt = $pdo->prepare("INSERT INTO issued_joining_letters (usn, name, role, letter_id, term) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$usn, $name, $role, $letterId, $term]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Email sent, but DB save failed: " . $e->getMessage()]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Joining Letter sent and recorded successfully.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid Action']);

// --- SMTP FUNCTION (Same as before) ---
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