<?php
// backend/place_order.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success'=>false,'message'=>'Invalid request data']);
    exit;
}

$userId        = intval($input['user_id']        ?? 0);
$customerName  = trim($input['customer_name']    ?? '');
$customerPhone = trim($input['customer_phone']   ?? '');
$customerEmail = trim($input['customer_email']   ?? '');
$address       = trim($input['address']          ?? '');
$notes         = trim($input['notes']            ?? '');
$paymentMethod = trim($input['payment_method']   ?? 'Cash on Delivery');
$service       = trim($input['service']          ?? 'no_installation'); // 'with_installation' | 'no_installation'
$items         = $input['items']                 ?? [];
$subtotal      = floatval($input['subtotal']     ?? 0);
$shipping      = floatval($input['shipping']     ?? 0);
$installFee    = floatval($input['installation_fee'] ?? $input['install_fee'] ?? 0);
$total         = floatval($input['total']        ?? 0);

// Validation
if (!$customerName || !$customerEmail || !$address) {
    echo json_encode(['success'=>false,'message'=>'Missing required fields']);
    exit;
}
if (empty($items)) {
    echo json_encode(['success'=>false,'message'=>'No items in order']);
    exit;
}

// Generate unique order ref
$orderRef = 'TL-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

// Build items_json — include installation service note if applicable
$itemsJson = json_encode($items);

try {
    $pdo->beginTransaction();

    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders
            (order_ref, customer_name, customer_phone, customer_email,
             address, notes, payment_method, service,
             items_json, subtotal, shipping, total, status, created_at)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([
        $orderRef, $customerName, $customerPhone, $customerEmail,
        $address, $notes, $paymentMethod, $service,
        $itemsJson, $subtotal, $shipping, $total
    ]);
    $orderId = $pdo->lastInsertId();

    // Insert order_items and decrease stock
    foreach ($items as $item) {
        $productId = intval($item['id']  ?? 0);
        $qty       = intval($item['qty'] ?? 1);
        $price     = floatval($item['price'] ?? 0);
        $variant   = trim($item['variant'] ?? 'Standard');

        if ($productId && $qty > 0) {
            // Insert order item
            $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                VALUES (?, ?, ?, ?)
            ")->execute([$orderId, $productId, $qty, $price]);

            // Decrease stock
            $productStmt = $pdo->prepare("SELECT prices_json FROM products WHERE id = ?");
            $productStmt->execute([$productId]);
            $result = $productStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $prices = json_decode($result['prices_json'] ?? '[]', true);
                foreach ($prices as &$p) {
                    if (trim($p['label'] ?? 'Standard') === $variant) {
                        $p['stock'] = max(0, (int)($p['stock'] ?? 0) - $qty);
                        break;
                    }
                }
                unset($p);
                
                $updateStmt = $pdo->prepare("UPDATE products SET prices_json = ? WHERE id = ?");
                $updateStmt->execute([json_encode($prices), $productId]);
            }
        }
    }

    // Auto-create delivery record
    $pdo->prepare("
        INSERT IGNORE INTO deliveries (order_id, status) VALUES (?, 'processing')
    ")->execute([$orderId]);

    $pdo->commit();

    echo json_encode([
        'success'   => true,
        'message'   => 'Order placed successfully',
        'order_id'  => $orderId,
        'order_ref' => $orderRef,
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
}