<?php
// ════════════════════════════════════════════════════════
//  backend/get_address.php
//  Returns the saved address for a user from the database.
//
//  GET params:
//    user_id  — the logged-in user's ID
//
//  Response JSON:
//    { "success": true,  "address": { name, phone, street, ... } }
//    { "success": false, "address": null }
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$userId = intval($_GET['user_id'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['success' => false, 'address' => null, 'message' => 'Invalid user.']);
    exit;
}

$stmt = $conn->prepare('SELECT address FROM users WHERE id = ? LIMIT 1');
if (!$stmt) {
    echo json_encode(['success' => false, 'address' => null, 'message' => 'DB error.']);
    exit;
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($raw);
$stmt->fetch();
$stmt->close();
$conn->close();

if (!$raw) {
    echo json_encode(['success' => false, 'address' => null]);
    exit;
}

// Address is stored as JSON object in the address column
$addr = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($addr)) {
    // Fallback: stored as plain text string (legacy)
    echo json_encode(['success' => true, 'address' => ['text' => $raw]]);
    exit;
}

echo json_encode(['success' => true, 'address' => $addr]);