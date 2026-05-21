<?php
// auth/google_login.php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

set_exception_handler(function ($e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

try {
    require_once '../backend/config.php';

    $body  = json_decode(file_get_contents('php://input'), true);
    $token = trim($body['token'] ?? '');

    if (!$token) {
        echo json_encode(['success' => false, 'message' => 'No token received.']);
        exit;
    }

    // ── Verify token with Google ──────────────────────
    $verifyUrl = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($token);
    $ch = curl_init($verifyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Technilog/1.0');
    $raw     = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($raw === false || !empty($curlErr)) {
        echo json_encode(['success' => false, 'message' => 'Could not verify Google token (network error). Please try again.']);
        exit;
    }

    $info      = json_decode($raw, true);
    $CLIENT_ID = '1019348410923-vs99rj7j6vq2d1jft3q4enfsc3o9c93g.apps.googleusercontent.com';

    if (!$info || empty($info['email']) || ($info['aud'] ?? '') !== $CLIENT_ID) {
        echo json_encode(['success' => false, 'message' => 'Invalid Google token.']);
        exit;
    }

    $email = $info['email'];
    $name  = $info['name'] ?? explode('@', $email)[0];

    // ── Check if user already exists ──────────────────
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Existing user — log them in
        $user = $result->fetch_assoc();
        $stmt->close();
        echo json_encode([
            'success'  => true,
            'id'       => $user['id'],
            'email'    => $email,
            'username' => $user['username'],
            'role'     => $user['role'] ?? 'user',
        ]);
    } else {
        // New user — auto-register
        $stmt->close();
        $fakePassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        // Uses password_hash column (same as signup.php) and is_verified = 1
        $insert = $conn->prepare(
            "INSERT INTO users (username, email, password_hash, role, is_verified) VALUES (?, ?, ?, 'user', 1)"
        );
        $insert->bind_param('sss', $name, $email, $fakePassword);

        if ($insert->execute()) {
            $newId = $conn->insert_id;
            $insert->close();
            echo json_encode([
                'success'  => true,
                'id'       => $newId,
                'email'    => $email,
                'username' => $name,
                'role'     => 'user',
            ]);
        } else {
            $insert->close();
            echo json_encode(['success' => false, 'message' => 'Could not create account. Please try again.']);
        }
    }

    $conn->close();

} catch (Throwable $t) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $t->getMessage()]);
}
?>