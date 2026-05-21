<?php
// Location: Technilog/auth/reset_verify.php
// Called by: auth/resetpass.html → js/resetPass.js
require_once __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data  = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($data['email'] ?? ''));
$code  = trim($data['code'] ?? '');

if (!$email || !$code) {
    echo json_encode(['success' => false, 'message' => 'Email and code are required.']); exit;
}

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
    echo json_encode(['success' => false, 'message' => 'Incorrect code. Please try again.']); exit;
}
if (strtotime($user['reset_expires']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Code has expired. Please request a new one.']); exit;
}

echo json_encode(['success' => true, 'message' => 'Code verified.']);