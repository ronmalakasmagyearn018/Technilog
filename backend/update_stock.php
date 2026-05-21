<?php
// backend/update_stock.php — updates prices_json (stock) for a product
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id     = (int)($data['id']     ?? 0);
$prices = $data['prices']       ?? null;

if (!$id || !is_array($prices)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']); exit;
}

// Sanitise stock values
foreach ($prices as &$p) {
    $p['stock'] = max(0, (int)($p['stock'] ?? 0));
}
unset($p);

$pricesJson = json_encode(array_values($prices));

$stmt = mysqli_prepare($conn, 'UPDATE products SET prices_json = ? WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'si', $pricesJson, $id);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode(['success' => $ok, 'message' => $ok ? 'Stock updated.' : mysqli_error($conn)]);
