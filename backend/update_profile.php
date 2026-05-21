<?php
// ════════════════════════════════════════════════════════
//  backend/update_profile.php
//  Saves address for the logged-in user.
//
//  POST JSON body (send only the fields you want to change):
//  {
//    "user_id": 5,
//    "address": "123 Main St, Barangay X, Angono, Rizal, 1930"
//  }
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$userId  = intval($data['user_id'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user.']); exit;
}

// Build SET clause dynamically — only update fields that were sent
$fields = [];
$types  = '';
$values = [];

if (array_key_exists('address', $data)) {
    $fields[] = 'address = ?';
    $types   .= 's';
    $values[] = $data['address'] !== null ? mb_substr(trim((string)$data['address']), 0, 512) : null;
}

if (empty($fields)) {
    echo json_encode(['success' => false, 'message' => 'No fields to update.']); exit;
}

$sql    = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
$types .= 'i';
$values[] = $userId;

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]); exit;
}

$stmt->bind_param($types, ...$values);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $ok, 'message' => $ok ? 'Profile updated.' : 'Update failed.']);