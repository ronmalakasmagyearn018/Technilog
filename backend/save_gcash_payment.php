<?php
// save_gcash_payment.php — saves GCash payment record to online_payment table
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$user_id   = intval($data['user_id']   ?? 0);
$order_id  = intval($data['order_id']  ?? 0);
$order_ref = trim($data['order_ref']   ?? '');
$gcash_ref = trim($data['gcash_ref']   ?? '');
$amount    = floatval($data['amount']  ?? 0);

if (!$user_id || !$order_id || !$gcash_ref) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO online_payment
     (user_id, order_id, order_ref, gcash_ref, amount, status, created_at)
     VALUES (?, ?, ?, ?, ?, 'pending', NOW())"
);
$stmt->bind_param('iissd', $user_id, $order_id, $order_ref, $gcash_ref, $amount);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'payment_id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();