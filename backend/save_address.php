<?php
// ════════════════════════════════════════════════════════
//  backend/save_address.php
//  Saves the checkout address for a user to the database.
//  This replaces the old localStorage-only approach so the
//  address is available on any device or browser.
//
//  POST JSON body:
//  {
//    "user_id": 5,
//    "address": {
//      "name": "Juan dela Cruz",
//      "phone": "09XX XXX XXXX",
//      "street": "Block 1 Lot 2, Sampaguita St.",
//      "barangay": "Brgy. Santo Niño",
//      "city": "Morong",
//      "province": "Rizal",
//      "zip": "1960",
//      "notes": ""
//    }
//  }
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data   = json_decode(file_get_contents('php://input'), true);
$userId = intval($data['user_id'] ?? 0);
$addr   = $data['address'] ?? null;

if ($userId <= 0 || !is_array($addr)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']); exit;
}

// Sanitise — only keep known fields
$clean = [
    'name'     => mb_substr(trim((string)($addr['name']     ?? '')), 0, 128),
    'phone'    => mb_substr(trim((string)($addr['phone']    ?? '')), 0, 32),
    'street'   => mb_substr(trim((string)($addr['street']   ?? '')), 0, 255),
    'barangay' => mb_substr(trim((string)($addr['barangay'] ?? '')), 0, 128),
    'city'     => mb_substr(trim((string)($addr['city']     ?? '')), 0, 128),
    'province' => mb_substr(trim((string)($addr['province'] ?? '')), 0, 128),
    'zip'      => mb_substr(trim((string)($addr['zip']      ?? '')), 0, 16),
    'notes'    => mb_substr(trim((string)($addr['notes']    ?? '')), 0, 512),
];

$json = json_encode($clean, JSON_UNESCAPED_UNICODE);

$stmt = $conn->prepare('UPDATE users SET address = ? WHERE id = ?');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]); exit;
}

$stmt->bind_param('si', $json, $userId);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $ok, 'message' => $ok ? 'Address saved.' : 'Save failed.']);