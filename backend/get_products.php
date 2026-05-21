<?php
// backend/get_products.php
require_once __DIR__ . '/config.php';

$category = trim($_GET['category'] ?? '');

if ($category) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM products WHERE status != "Deleted" AND category = ? ORDER BY created_at DESC');
    mysqli_stmt_bind_param($stmt, 's', $category);
} else {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM products WHERE status != "Deleted" ORDER BY created_at DESC');
}

mysqli_stmt_execute($stmt);
$result   = mysqli_stmt_get_result($stmt);
$products = [];

while ($row = mysqli_fetch_assoc($result)) {
    $row['images'] = json_decode($row['images_json']  ?? '[]', true);
    $row['prices'] = json_decode($row['prices_json']  ?? '[]', true);
    unset($row['images_json'], $row['prices_json']);
    $products[] = $row;
}

echo json_encode($products);