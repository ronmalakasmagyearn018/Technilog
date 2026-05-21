<?php
// update_delivery.php — TECHNILOG/backend/
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/config.php';
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request.']); exit;
}

$data   = json_decode(file_get_contents('php://input'), true);
$id     = (int)($data['id']    ?? 0);
$status = trim($data['status'] ?? '');

$allowed = ['Pending','Processing','Shipped','Delivered','Received','Cancelled'];
if (!$id || !in_array($status, $allowed)) {
    echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit;
}

// ── 1. Update order status ───────────────────────────────────
$stmt = mysqli_prepare($conn, 'UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?');
if (!$stmt) $stmt = mysqli_prepare($conn, 'UPDATE orders SET status = ? WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'si', $status, $id);
if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(['success'=>false,'message'=>'Update failed: '.mysqli_stmt_error($stmt)]); exit;
}
mysqli_stmt_close($stmt);

// ── 2. Fetch order details ───────────────────────────────────
$sel = mysqli_prepare($conn,
    'SELECT id, order_ref, user_id, customer_name, customer_email, customer_phone,
            address, payment_method, service, installation_fee,
            items_json, subtotal, shipping, total FROM orders WHERE id = ?');
mysqli_stmt_bind_param($sel, 'i', $id);
mysqli_stmt_execute($sel);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($sel));
mysqli_stmt_close($sel);

if ($order) {

    // ── 3. CANCELLED: restore stock + log to cancelled_records ─
    if ($status === 'Cancelled') {

        // Restore stock
        $items = json_decode($order['items_json'] ?? '[]', true);
        if (is_array($items)) {
            foreach ($items as $item) {
                $product_id = intval($item['id'] ?? $item['product_id'] ?? 0);
                $qty        = intval($item['qty'] ?? $item['quantity'] ?? 1);
                if ($product_id > 0 && $qty > 0) {
                    $upd = mysqli_prepare($conn, 'UPDATE products SET stock = stock + ? WHERE id = ?');
                    if (!$upd) $upd = mysqli_prepare($conn, 'UPDATE products SET quantity = quantity + ? WHERE id = ?');
                    if ($upd) {
                        mysqli_stmt_bind_param($upd, 'ii', $qty, $product_id);
                        mysqli_stmt_execute($upd);
                        mysqli_stmt_close($upd);
                    }
                }
            }
        }

        // Log to cancelled_records — permanent, survives user delete
        $ins = mysqli_prepare($conn,
            'INSERT IGNORE INTO cancelled_records
             (order_id, order_ref, user_id, customer_name, customer_email, customer_phone,
              address, payment_method, service, installation_fee,
              items_json, subtotal, shipping, total, cancelled_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
        if ($ins) {
            mysqli_stmt_bind_param($ins, 'iisssssssdsddd',
                $order['id'],
                $order['order_ref'],
                $order['user_id'],
                $order['customer_name'],
                $order['customer_email'],
                $order['customer_phone'],
                $order['address'],
                $order['payment_method'],
                $order['service'],
                (float)$order['installation_fee'],
                $order['items_json'],
                (float)$order['subtotal'],
                (float)$order['shipping'],
                (float)$order['total']
            );
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);
        }
    }

    // ── 4. RECEIVED: log to received_records ─────────────────
    if ($status === 'Received') {
        $ins = mysqli_prepare($conn,
            'INSERT IGNORE INTO received_records
             (order_id, order_ref, user_id, customer_name, customer_email, customer_phone,
              address, payment_method, service, installation_fee,
              items_json, subtotal, shipping, total, received_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
        if ($ins) {
            mysqli_stmt_bind_param($ins, 'iisssssssdsddd',
                $order['id'],
                $order['order_ref'],
                $order['user_id'],
                $order['customer_name'],
                $order['customer_email'],
                $order['customer_phone'],
                $order['address'],
                $order['payment_method'],
                $order['service'],
                (float)$order['installation_fee'],
                $order['items_json'],
                (float)$order['subtotal'],
                (float)$order['shipping'],
                (float)$order['total']
            );
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);
        }

        // ── 5. RECEIVED: insert each item into user_devices ─────────
        $items = json_decode($order['items_json'] ?? '[]', true);
        if (is_array($items)) {
            foreach ($items as $item) {
                $device_model = trim($item['name']     ?? 'Unknown Device');
                $device_type  = trim($item['category'] ?? 'Device');
                $qty          = intval($item['qty']    ?? $item['quantity'] ?? 1);
                // Generate a model number: TL- + first 3 letters of category + product id + random
                $prefix   = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $device_type), 0, 3)) ?: 'DEV';
                $model_no = 'TL-' . $prefix . '-' . str_pad(intval($item['id'] ?? 0), 4, '0', STR_PAD_LEFT);

                $dev = mysqli_prepare($conn,
                    'INSERT INTO user_devices
                     (order_ref, model_no, customer_name, customer_email, device_type, device_model, quantity, received_date)
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
                if ($dev) {
                    mysqli_stmt_bind_param($dev, 'ssssssi',
                        $order['order_ref'],
                        $model_no,
                        $order['customer_name'],
                        $order['customer_email'],
                        $device_type,
                        $device_model,
                        $qty
                    );
                    mysqli_stmt_execute($dev);
                    mysqli_stmt_close($dev);
                }
            }
        }
    }
}

echo json_encode(['success' => true, 'message' => 'Status updated.']);