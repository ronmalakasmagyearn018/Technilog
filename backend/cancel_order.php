<?php
// backend/cancel_order.php — Cancel an order and restore stock
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

$orderId = intval($input['order_id'] ?? 0);

if (!$orderId) {
    echo json_encode(['success'=>false,'message'=>'Order ID required']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get order with items
    $orderStmt = $pdo->prepare("SELECT items_json FROM orders WHERE id = ?");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success'=>false,'message'=>'Order not found']);
        exit;
    }

    // Get order items to get product IDs and quantities
    $itemsStmt = $pdo->prepare("
        SELECT product_id, quantity, unit_price FROM order_items WHERE order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Parse items_json to get variant info
    $itemsData = json_decode($order['items_json'] ?? '[]', true);

    // Restore stock for each item
    foreach ($orderItems as $orderItem) {
        $productId = $orderItem['product_id'];
        $qty = intval($orderItem['quantity']);

        // Find the variant label for this product from items_json
        $variant = 'Standard';
        foreach ($itemsData as $item) {
            if (intval($item['id'] ?? 0) === $productId) {
                $variant = trim($item['variant'] ?? 'Standard');
                break;
            }
        }

        // Get product and update stock
        $productStmt = $pdo->prepare("SELECT prices_json FROM products WHERE id = ?");
        $productStmt->execute([$productId]);
        $result = $productStmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $prices = json_decode($result['prices_json'] ?? '[]', true);
            foreach ($prices as &$p) {
                if (trim($p['label'] ?? 'Standard') === $variant) {
                    $p['stock'] = (int)($p['stock'] ?? 0) + $qty;
                    break;
                }
            }
            unset($p);

            $updateStmt = $pdo->prepare("UPDATE products SET prices_json = ? WHERE id = ?");
            $updateStmt->execute([json_encode($prices), $productId]);
        }
    }

    // Update order status
    $cancelStmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
    $cancelStmt->execute([$orderId]);

    // Log to cancelled_records — permanent record, survives user deletes
    $detailStmt = $pdo->prepare(
        "SELECT id, order_ref, user_id, customer_name, customer_email, customer_phone,
                address, payment_method, service, installation_fee,
                items_json, subtotal, shipping, total
         FROM orders WHERE id = ?"
    );
    $detailStmt->execute([$orderId]);
    $fullOrder = $detailStmt->fetch(PDO::FETCH_ASSOC);

    if ($fullOrder) {
        $insStmt = $pdo->prepare(
            "INSERT IGNORE INTO cancelled_records
             (order_id, order_ref, user_id, customer_name, customer_email, customer_phone,
              address, payment_method, service, installation_fee,
              items_json, subtotal, shipping, total, cancelled_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())"
        );
        $insStmt->execute([
            $fullOrder['id'],
            $fullOrder['order_ref'],
            $fullOrder['user_id'],
            $fullOrder['customer_name'],
            $fullOrder['customer_email'],
            $fullOrder['customer_phone'],
            $fullOrder['address'],
            $fullOrder['payment_method'],
            $fullOrder['service'],
            (float)$fullOrder['installation_fee'],
            $fullOrder['items_json'],
            (float)$fullOrder['subtotal'],
            (float)$fullOrder['shipping'],
            (float)$fullOrder['total'],
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled and stock restored'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
}
?>