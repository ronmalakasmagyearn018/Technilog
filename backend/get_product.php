<?php
// backend/get_product.php
require_once __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['error'=>'No ID']); exit; }

$stmt = mysqli_prepare($conn, 'SELECT * FROM products WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row    = mysqli_fetch_assoc($result);

if (!$row) { echo json_encode(['error'=>'Not found']); exit; }

$row['images'] = json_decode($row['images_json'] ?? '[]', true);
$row['prices'] = json_decode($row['prices_json'] ?? '[]', true);
unset($row['images_json'], $row['prices_json']);

echo json_encode($row);
