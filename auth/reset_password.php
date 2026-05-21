<?php
// Location: Technilog/auth/reset_password.php
// Called by: auth/resetpass.html → js/resetPass.js
require_once __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data            = json_decode(file_get_contents('php://input'), true);
$email           = strtolower(trim($data['email']           ?? ''));
$code            = trim($data['code']                       ?? '');
$newPassword     = $data['new_password']                    ?? '';
$confirmPassword = $data['confirm_password']                ?? '';

if (!$email || !$code || !$newPassword || !$confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']); exit;
}
if (strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']); exit;
}
if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']); exit;
}

// Re-validate code (security: prevent bypassing verify step)
$stmt = mysqli_prepare($conn,
    'SELECT id, reset_code, reset_expires FROM users WHERE email = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user || !$user['reset_code']) {
    echo json_encode(['success' => false, 'message' => 'No reset request found. Please start again.']); exit;
}
if ($user['reset_code'] !== $code) {
    echo json_encode(['success' => false, 'message' => 'Invalid code. Please start again.']); exit;
}
if (strtotime($user['reset_expires']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Code expired. Please start again.']); exit;
}

$hash = password_hash($newPassword, PASSWORD_BCRYPT);
$stmt = mysqli_prepare($conn,
    'UPDATE users SET password_hash = ?, reset_code = NULL, reset_expires = NULL WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'si', $hash, $user['id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Log the reset
$stmt = mysqli_prepare($conn,
    'INSERT INTO password_reset_log (user_id, reset_at) VALUES (?, NOW())');
mysqli_stmt_bind_param($stmt, 'i', $user['id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode(['success' => true, 'message' => 'Password reset successfully! You can now log in.']);