<?php
// ============================================================
// get_orders.php  — Technilog/backend/get_orders.php
// FIXED: rewrote $pdo → mysqli, fixed JOIN to match actual schema
// ============================================================
require_once __DIR__ . '/config.php';

// Allow GET
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

$sql = "
    SELECT
        o.id,
        o.order_ref,
        o.customer_name,
        o.customer_email,
        o.customer_phone,
        o.address,
        o.payment_method,
        o.service,
        o.installation_fee,
        o.items_json,
        o.subtotal,
        o.shipping,
        o.total,
        o.status,
        o.created_at,
        o.updated_at
    FROM orders o
    ORDER BY o.created_at DESC
    LIMIT 100
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . mysqli_error($conn)]); exit;
}

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['items'] = json_decode($row['items_json'] ?? '[]', true);
    unset($row['items_json']);
    if (empty($row['order_ref'])) {
        $row['order_ref'] = 'TL-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT);
    }
    $orders[] = $row;
}

echo json_encode(['success' => true, 'data' => $orders]);