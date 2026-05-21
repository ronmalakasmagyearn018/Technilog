<?php
// ════════════════════════════════════════════════════
//  config.php
//  Location: Technilog/backend/config.php
// ════════════════════════════════════════════════════

// ── Database ──────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'technilog');

// ── Gmail App Password (spaces stripped here once) ────
define('MAIL_FROM',     'ronlouiemagsipoc210@gmail.com');
define('MAIL_NAME',     'Technilog');
define('MAIL_PASSWORD', 'eoxbufykfoknckdx');   // no spaces

// ── DB connection ─────────────────────────────────────
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}
mysqli_set_charset($conn, 'utf8mb4');

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}

// ── CORS + JSON headers ───────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// ════════════════════════════════════════════════════
//  Shared SMTP socket helper
//  Returns true on 250 OK, false on any failure
// ════════════════════════════════════════════════════
function _smtpSend(string $toEmail, string $toName, string $subject, string $html): bool {
    $from = MAIL_FROM;
    $pass = MAIL_PASSWORD;

    $sock = @fsockopen('ssl://smtp.gmail.com', 465, $errno, $errstr, 15);
    if (!$sock) return false;

    $read = function () use ($sock) {
        $out = '';
        while (!feof($sock)) {
            $line = fgets($sock, 512);
            $out .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') break;
        }
        return $out;
    };
    $cmd = function (string $c) use ($sock, $read) {
        fputs($sock, $c . "\r\n");
        return $read();
    };

    $read();                           // greeting
    $cmd('EHLO localhost');
    $r = $cmd('AUTH LOGIN');
    if (!str_starts_with($r, '334')) { fclose($sock); return false; }
    $r = $cmd(base64_encode($from));
    if (!str_starts_with($r, '334')) { fclose($sock); return false; }
    $r = $cmd(base64_encode($pass));
    if (!str_starts_with($r, '235')) { fclose($sock); return false; }

    $cmd("MAIL FROM:<{$from}>");
    $r = $cmd("RCPT TO:<{$toEmail}>");
    if (!str_starts_with($r, '250')) { fclose($sock); return false; }

    $cmd('DATA');
    $body  = "From: " . MAIL_NAME . " <{$from}>\r\n";
    $body .= "To: {$toName} <{$toEmail}>\r\n";
    $body .= "Subject: {$subject}\r\n";
    $body .= "MIME-Version: 1.0\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($html)) . "\r\n.";
    $r = $cmd($body);
    fclose($sock);

    return str_starts_with(trim($r), '250');
}

// ── Email template builder ────────────────────────────
function _buildEmail(string $toName, string $heading, string $bodyText, string $code, string $footerNote): string {
    return '<!DOCTYPE html><html><body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;">
    <table width="100%"><tr><td align="center" style="padding:40px 20px;">
    <table width="480" style="background:#1a1a2e;border-radius:16px;">
    <tr><td align="center" style="padding:40px 32px;">
      <h1 style="color:#fff;font-size:24px;letter-spacing:3px;margin:0 0 4px;">TECHNILOG</h1>
      <p style="color:#aaa;font-size:13px;margin:0 0 28px;">' . $heading . '</p>
      <p style="color:#eee;font-size:15px;margin:0 0 20px;">' . $bodyText . '</p>
      <div style="background:#fff;border-radius:12px;padding:18px 32px;display:inline-block;margin-bottom:24px;">
        <span style="font-size:40px;font-weight:bold;letter-spacing:12px;color:#1a1a2e;">' . $code . '</span>
      </div>
      <p style="color:#aaa;font-size:13px;margin:0;">
        Expires in <strong style="color:#fff;">15 minutes</strong>.
      </p>
      <p style="color:#555;font-size:11px;margin:24px 0 0;">' . $footerNote . '</p>
    </td></tr></table></td></tr></table>
    </body></html>';
}

// ════════════════════════════════════════════════════
//  sendVerificationEmail()  — used by signup / resend
// ════════════════════════════════════════════════════
function sendVerificationEmail(string $toEmail, string $toName, string $code): bool {
    $html = _buildEmail(
        $toName,
        'Email Verification',
        'Hi <strong>' . htmlspecialchars($toName) . '</strong>,<br>Use the code below to verify your account:',
        $code,
        'If you did not sign up for Technilog, ignore this email.'
    );
    return _smtpSend($toEmail, $toName, 'Your Technilog Verification Code', $html);
}

// ════════════════════════════════════════════════════
//  sendResetEmail()  — used by forgot password
// ════════════════════════════════════════════════════
function sendResetEmail(string $toEmail, string $toName, string $code): bool {
    $html = _buildEmail(
        $toName,
        'Password Reset',
        'Hi <strong>' . htmlspecialchars($toName) . '</strong>,<br>Use the code below to reset your password:',
        $code,
        'If you did not request a password reset, ignore this email.'
    );
    return _smtpSend($toEmail, $toName, 'Your Technilog Password Reset Code', $html);
}