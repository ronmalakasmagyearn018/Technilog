<?php
// ============================================================
//  get_user_devices.php  — Technilog/backend/get_user_devices.php
// ============================================================

require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Methods: GET, OPTIONS');

$sql = "
    SELECT
        id,
        order_ref,
        model_no,
        customer_name,
        customer_email,
        device_type,
        device_model,
        quantity,
        received_date,
        created_at
    FROM user_devices
    ORDER BY received_date DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    exit;
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

echo json_encode(['success' => true, 'total' => count($rows), 'data' => $rows]);