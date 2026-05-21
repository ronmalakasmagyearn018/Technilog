<?php
// backend/delete_product.php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$id   = (int)($data['id'] ?? 0);

if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }

// Soft delete — just mark as Deleted
$stmt = mysqli_prepare($conn, "UPDATE products SET status = 'Deleted' WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
$ok = mysqli_stmt_execute($stmt);
echo json_encode(['success' => $ok]);
