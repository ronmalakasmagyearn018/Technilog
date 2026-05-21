<?php
// ============================================================
//  device_history.php  — Technilog/backend/
//
//  POST  → log a device action (turned_on / turned_off)
//  GET   → fetch history for a user
// ============================================================
require_once __DIR__ . '/config.php';

// ── POST: log an action ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $user_id      = intval($body['user_id']      ?? 0);
    $model_no     = trim($body['model_no']        ?? '');
    $device_model = trim($body['device_model']    ?? '');
    $category     = trim($body['category']        ?? '');
    $order_ref    = trim($body['order_ref']        ?? '');
    $action       = trim($body['action']           ?? '');
    $note         = trim($body['note']             ?? '');

    if (!$user_id || !$model_no || !in_array($action, ['turned_off', 'turned_on', 'alert_fire', 'alert_burglar'])) {
        echo json_encode(['success' => false, 'message' => 'Missing or invalid fields.']);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO device_history (user_id, model_no, device_model, category, order_ref, action, note)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('issssss', $user_id, $model_no, $device_model, $category, $order_ref, $action, $note);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Action logged.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    exit;
}

// ── GET: fetch history for a user ───────────────────────────
$user_id  = intval($_GET['user_id']  ?? 0);
$model_no = trim($_GET['model_no']   ?? '');

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'user_id required.']);
    exit;
}

$where  = 'WHERE user_id = ?';
$params = [$user_id];
$types  = 'i';

if ($model_no) {
    $where   .= ' AND model_no = ?';
    $params[] = $model_no;
    $types   .= 's';
}

$stmt = $conn->prepare(
    "SELECT id, model_no, device_model, category, order_ref, action, actioned_at, note
     FROM device_history
     $where
     ORDER BY actioned_at DESC
     LIMIT 200"
);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;

echo json_encode(['success' => true, 'total' => count($rows), 'data' => $rows]);