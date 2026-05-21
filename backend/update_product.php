<?php
// backend/update_product.php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }

$isMultipart = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart') !== false;

if ($isMultipart) {
    $id       = (int)($_POST['id']       ?? 0);
    $name     = trim($_POST['name']      ?? '');
    $desc     = trim($_POST['desc']      ?? '');
    $specs    = trim($_POST['specs']     ?? '');
    $status   = $_POST['status']         ?? 'Available';
    $category = $_POST['category']       ?? 'Other';
    $featured = (int)($_POST['featured'] ?? 0);
    $prices   = json_decode($_POST['prices'] ?? '[]', true);
} else {
    $data     = json_decode(file_get_contents('php://input'), true);
    $id       = (int)($data['id']       ?? 0);
    $name     = trim($data['name']      ?? '');
    $desc     = trim($data['desc']      ?? '');
    $specs    = trim($data['specs']     ?? '');
    $status   = $data['status']         ?? 'Available';
    $category = $data['category']       ?? 'Other';
    $featured = (int)($data['featured'] ?? 0);
    $prices   = $data['prices']         ?? [];
}

if (!$id || !$name) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }

// ── Fetch existing product (for audit log) ─────────────
$stmt = mysqli_prepare($conn, 'SELECT * FROM products WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$existingImages = json_decode($old['images_json'] ?? '[]', true);

// ── Handle new image uploads ──────────────────────────
$savedPaths = $existingImages;

if ($isMultipart && !empty($_FILES['images']['name'][0])) {
    $uploadDirRaw = __DIR__ . '/../uploads/products';
    if (!is_dir($uploadDirRaw)) mkdir($uploadDirRaw, 0755, true);
    $uploadDir = (realpath($uploadDirRaw) ?: $uploadDirRaw) . DIRECTORY_SEPARATOR;

    if (is_writable($uploadDir)) {
        $files    = $_FILES['images'];
        $count    = count($files['name']);
        $newPaths = [];
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            if (!is_uploaded_file($files['tmp_name'][$i])) continue;
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) continue;
            $filename = 'prod_' . uniqid('', true) . '.' . $ext;
            if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $filename)) {
                $newPaths[] = '../uploads/products/' . $filename;
            }
        }
        if (!empty($newPaths)) $savedPaths = $newPaths;
    }
}

$imagesJson = json_encode($savedPaths);
$pricesJson = json_encode(array_values($prices ?: []));

// ── Update product ─────────────────────────────────────
$stmt = mysqli_prepare($conn,
    'UPDATE products SET name=?, description=?, specifications=?, status=?, category=?, featured=?, prices_json=?, images_json=? WHERE id=?');
mysqli_stmt_bind_param($stmt, 'sssssissi',
    $name, $desc, $specs, $status, $category, $featured, $pricesJson, $imagesJson, $id);
$ok = mysqli_stmt_execute($stmt);

// ── Write audit log ────────────────────────────────────
if ($ok && $old) {
    $changedFields = [];
    $oldValues     = [];
    $newValues     = [];

    $checks = [
        'name'          => [$old['name'],          $name],
        'description'   => [$old['description'],   $desc],
        'specifications'=> [$old['specifications'], $specs],
        'status'        => [$old['status'],         $status],
        'category'      => [$old['category'],       $category],
        'featured'      => [$old['featured'],       $featured],
        'prices_json'   => [$old['prices_json'],    $pricesJson],
        'images_json'   => [$old['images_json'],    $imagesJson],
    ];

    foreach ($checks as $field => [$oldVal, $newVal]) {
        if ((string)$oldVal !== (string)$newVal) {
            $changedFields[] = $field;
            $oldValues[$field] = $oldVal;
            $newValues[$field] = $newVal;
        }
    }

    $changedStr = implode(', ', $changedFields);
    $oldStr     = json_encode($oldValues);
    $newStr     = json_encode($newValues);
    $prodName   = $name;

    $log = mysqli_prepare($conn,
        'INSERT INTO product_edit_logs (product_id, product_name, changed_fields, old_values, new_values)
         VALUES (?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($log, 'issss', $id, $prodName, $changedStr, $oldStr, $newStr);
    mysqli_stmt_execute($log);
}

echo json_encode(['success' => $ok, 'message' => $ok ? 'Updated.' : mysqli_error($conn)]);