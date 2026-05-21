<?php
// ════════════════════════════════════════════
//  resend_code.php  —  place in:  Technilog/auth/
//  Called by verify.html via js/verify.js
// ════════════════════════════════════════════
require_once __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data  = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($data['email'] ?? ''));

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']); exit;
}

$stmt = mysqli_prepare($conn,
    'SELECT id, username, is_verified FROM users WHERE email = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Account not found.']); exit;
}
if ($user['is_verified']) {
    echo json_encode(['success' => false, 'message' => 'Account already verified.']); exit;
}

$code    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

$stmt = mysqli_prepare($conn,
    'UPDATE users SET verify_code = ?, verify_expires = ? WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'ssi', $code, $expires, $user['id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (sendVerificationEmail($email, $user['username'], $code)) {
    echo json_encode(['success' => true, 'message' => 'A new code has been sent to your email.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again.']);
}