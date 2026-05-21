<?php
// ════════════════════════════════════════════
//  verify.php  —  place in:  Technilog/auth/
//  Called by verify.html via js/verify.js
// ════════════════════════════════════════════
require_once __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data  = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($data['email'] ?? ''));
$code  = trim($data['code']             ?? '');

if (!$email || !$code) {
    echo json_encode(['success' => false, 'message' => 'Email and code are required.']); exit;
}

$stmt = mysqli_prepare($conn,
    'SELECT id, username, verify_code, verify_expires, is_verified FROM users WHERE email = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Account not found.']); exit;
}
if ($user['is_verified']) {
    echo json_encode(['success' => false, 'message' => 'Already verified. Please log in.']); exit;
}
if ($user['verify_code'] !== $code) {
    echo json_encode(['success' => false, 'message' => 'Incorrect code. Please try again.']); exit;
}
if (strtotime($user['verify_expires']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Code expired. Please sign up again.']); exit;
}

$stmt = mysqli_prepare($conn,
    'UPDATE users SET is_verified = 1, verify_code = NULL, verify_expires = NULL WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user['id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode([
    'success'  => true,
    'message'  => 'Email verified! You can now log in.',
    'username' => $user['username'],
]);