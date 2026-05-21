<?php
// ════════════════════════════════════════════════════
//  get_cancelled_orders.php
//  Location: Technilog/backend/
//  Reads from cancelled_records — permanent log,
//  never affected by user deletes from orders table.
// ════════════════════════════════════════════════════
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Methods: GET, OPTIONS');

$sql = "
    SELECT
        cr.id,
        cr.order_id,
        cr.order_ref,
        cr.user_id,
        cr.customer_name,
        cr.customer_email,
        cr.customer_phone,
        cr.address,
        cr.payment_method,
        cr.service,
        cr.installation_fee,
        cr.items_json,
        cr.subtotal,
        cr.shipping,
        cr.total,
        cr.cancelled_at,
        u.username
    FROM cancelled_records cr
    LEFT JOIN users u ON u.id = cr.user_id
    ORDER BY cr.cancelled_at DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]); exit;
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['items'] = json_decode($row['items_json'] ?? '[]', true);
    unset($row['items_json']);
    if (empty($row['order_ref'])) {
        $row['order_ref'] = 'TL-' . str_pad($row['order_id'], 5, '0', STR_PAD_LEFT);
    }
    $rows[] = $row;
}

echo json_encode(['success' => true, 'data' => $rows]);