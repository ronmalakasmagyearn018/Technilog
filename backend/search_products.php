<?php
// backend/search_products.php — Technilog
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/config.php';
ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$q    = trim($_GET['q'] ?? '');
$mode = trim($_GET['mode'] ?? 'suggest');

if ($q === '') {
    echo json_encode(['success' => true, 'data' => [], 'total' => 0]);
    exit;
}

$like = '%' . $q . '%';

if ($mode === 'suggest') {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, category, images_json, prices_json
         FROM products
         WHERE status = 'Available'
           AND (name LIKE ? OR category LIKE ? OR description LIKE ?)
         ORDER BY
           CASE WHEN name LIKE ? THEN 0 ELSE 1 END,
           name ASC
         LIMIT 6"
    );
    mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $like, $like);
} else {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, category, description, images_json, prices_json, status
         FROM products
         WHERE status = 'Available'
           AND (name LIKE ? OR category LIKE ? OR description LIKE ?)
         ORDER BY
           CASE WHEN name LIKE ? THEN 0 ELSE 1 END,
           name ASC"
    );
    mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $like, $like);
}

if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => false, 'message' => mysqli_stmt_error($stmt)]);
    exit;
}

$result = mysqli_stmt_get_result($stmt);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $images = json_decode($row['images_json'] ?? '[]', true);
    $prices = json_decode($row['prices_json'] ?? '[]', true);
    $row['image']     = $images[0] ?? '';
    $row['min_price'] = !empty($prices) ? min(array_column($prices, 'price')) : 0;
    unset($row['images_json'], $row['prices_json']);
    $products[] = $row;
}

mysqli_stmt_close($stmt);
echo json_encode(['success' => true, 'data' => $products, 'total' => count($products), 'query' => $q]);