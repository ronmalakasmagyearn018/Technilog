<?php
// ════════════════════════════════════════════
//  signup.php  —  place in:  Technilog/auth/
//  Called by signup.html via js/signup.js
// ════════════════════════════════════════════
require_once __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data            = json_decode(file_get_contents('php://input'), true);
$username        = trim($data['username']         ?? '');
$email           = strtolower(trim($data['email'] ?? ''));
$password        = $data['password']              ?? '';
$confirmPassword = $data['confirm_password']      ?? '';

// ── Validate ─────────────────────────────────
if (!$username || !$email || !$password || !$confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']); exit;
}
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']); exit;
}
if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']); exit;
}

// ── Check duplicate ───────────────────────────
$stmt = mysqli_prepare($conn, 'SELECT id, is_verified FROM users WHERE email = ? OR username = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'ss', $email, $username);
mysqli_stmt_execute($stmt);
$existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($existing) {
    if ($existing['is_verified']) {
        echo json_encode(['success' => false, 'message' => 'Email or username already taken.']); exit;
    }
    // Unverified old record — remove so they can retry
    $del = mysqli_prepare($conn, 'DELETE FROM users WHERE email = ?');
    mysqli_stmt_bind_param($del, 's', $email);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);
}

// ── Insert user ───────────────────────────────
$hash    = password_hash($password, PASSWORD_BCRYPT);
$code    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

$stmt = mysqli_prepare($conn,
    'INSERT INTO users (username, email, password_hash, is_verified, verify_code, verify_expires)
     VALUES (?, ?, ?, 0, ?, ?)');
mysqli_stmt_bind_param($stmt, 'sssss', $username, $email, $hash, $code, $expires);
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']); exit;
}
mysqli_stmt_close($stmt);

// ── Send verification email ───────────────────
if (sendVerificationEmail($email, $username, $code)) {
    echo json_encode(['success' => true, 'message' => 'Verification code sent to your email.']);
} else {
    // Roll back so they can retry
    $del = mysqli_prepare($conn, 'DELETE FROM users WHERE email = ?');
    mysqli_stmt_bind_param($del, 's', $email);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);
    echo json_encode(['success' => false, 'message' => 'Could not send verification email. Please try again.']);
}