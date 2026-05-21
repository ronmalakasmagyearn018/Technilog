<?php
// ════════════════════════════════════════════
//  login.php  —  place in:  Technilog/auth/
//  Called by login.html via js/workLogin.js
// ════════════════════════════════════════════
require_once __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$email    = strtolower(trim($data['email']    ?? ''));
$password = $data['password']                 ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']); exit;
}

$stmt = mysqli_prepare($conn,
    'SELECT id, username, password_hash, is_verified, is_banned, role FROM users WHERE email = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Vague on purpose — don't reveal if email exists
if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']); exit;
}
if (!$user['is_verified']) {
    echo json_encode(['success' => false, 'message' => 'Please verify your email before logging in.']); exit;
}
if ($user['is_banned']) {
    echo json_encode(['success' => false, 'message' => 'Your account has been banned. Please contact support.']); exit;
}

echo json_encode([
    'success'  => true,
    'message'  => 'Login successful.',
    'username' => $user['username'],
    'email'    => $email,
    'role'     => $user['role'] ?? 'user',
    'id'       => $user['id'],
]);