<?php
// get_deliveries.php — TECHNILOG/backend/
require_once __DIR__ . '/config.php';

$user_id = intval($_GET['user_id'] ?? 0);
$email   = trim($_GET['email'] ?? '');

if ($user_id && $email) {
    // Secure: match BOTH user_id OR (user_id=0 AND email matches) — owns old orders too
    $stmt = mysqli_prepare($conn,
        "SELECT id, order_ref, customer_name, customer_phone, customer_email,
                address, notes, payment_method, service, installation_fee,
                items_json AS items, subtotal, shipping, total, status, created_at
         FROM orders
         WHERE user_id = ? OR (user_id = 0 AND customer_email = ?)
         ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} elseif ($user_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, order_ref, customer_name, customer_phone, customer_email,
                address, notes, payment_method, service, installation_fee,
                items_json AS items, subtotal, shipping, total, status, created_at
         FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    // No user — admin use only (returns all)
    $result = mysqli_query($conn,
        "SELECT id, order_ref, customer_name, customer_phone, customer_email,
                address, notes, payment_method, service, installation_fee,
                items_json AS items, subtotal, shipping, total, status, created_at
         FROM orders ORDER BY created_at DESC");
}

if (!$result) {
    echo json_encode(['error' => mysqli_error($conn)]); exit;
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
echo json_encode($rows);