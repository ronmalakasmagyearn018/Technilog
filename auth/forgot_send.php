<?php
// Location: Technilog/auth/forgot_send.php
// Called by: auth/forgotpass.html → js/forgotPass.js
require_once __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data  = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($data['email'] ?? ''));

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']); exit;
}

$stmt = mysqli_prepare($conn,
    'SELECT id, username, is_verified FROM users WHERE email = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Always say "sent" — don't reveal if email exists
if (!$user || !$user['is_verified']) {
    echo json_encode(['success' => true, 'message' => 'If that email is registered, a code has been sent.']); exit;
}

$code    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

$stmt = mysqli_prepare($conn,
    'UPDATE users SET reset_code = ?, reset_expires = ? WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'ssi', $code, $expires, $user['id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

sendResetEmail($email, $user['username'], $code);

echo json_encode(['success' => true, 'message' => 'If that email is registered, a code has been sent.']);