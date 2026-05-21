<?php
// ════════════════════════════════════════════════════
//  save_product.php  —  TECHNILOG/backend/
// ════════════════════════════════════════════════════
ini_set('memory_limit', '256M');

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request.']); exit;
}

// ── Read JSON body ─────────────────────────────────────
$data      = json_decode(file_get_contents('php://input'), true);
$name      = trim($data['name']      ?? '');
$desc      = trim($data['desc']      ?? '');
$category  = trim($data['category']  ?? 'Other');
$specs     = trim($data['specs']     ?? '');
$status    = in_array($data['status'] ?? '', ['Available','Out of Stock','Coming Soon','Hidden'])
             ? $data['status'] : 'Available';
$featured  = (int)($data['featured']  ?? 0);
$prices    = $data['prices']          ?? [];
$imageData = $data['imageData']       ?? [];  // array of base64 data URIs

// ── Validate ───────────────────────────────────────────
if (!$name)         { echo json_encode(['success'=>false,'message'=>'Product name is required.']); exit; }
if (!$desc)         { echo json_encode(['success'=>false,'message'=>'Description is required.']); exit; }
if (empty($prices)) { echo json_encode(['success'=>false,'message'=>'At least one price is required.']); exit; }

// ── Save images from base64 ────────────────────────────
$uploadDirRaw = __DIR__ . '/../uploads/products';
if (!is_dir($uploadDirRaw)) {
    mkdir($uploadDirRaw, 0755, true);
}
$uploadDir  = (realpath($uploadDirRaw) ?: $uploadDirRaw) . DIRECTORY_SEPARATOR;
$savedPaths = [];

foreach ($imageData as $dataUri) {
    if (!preg_match('/^data:image\/(\w+);base64,(.+)$/s', $dataUri, $m)) continue;
    $ext = strtolower($m[1]);
    if ($ext === 'jpeg') $ext = 'jpg';
    if (!in_array($ext, ['jpg','png','gif','webp'])) continue;

    $decoded = base64_decode($m[2]);
    if (!$decoded) continue;

    $filename = 'prod_' . uniqid('', true) . '.' . $ext;
    if (file_put_contents($uploadDir . $filename, $decoded) !== false) {
        $savedPaths[] = '../uploads/products/' . $filename;
    }
}

// ── Insert into DB ─────────────────────────────────────
$imagesJson = json_encode($savedPaths);
$pricesJson = json_encode(array_values($prices));

$stmt = mysqli_prepare($conn,
    'INSERT INTO products
       (name, description, category, specifications, status, featured, images_json, prices_json)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)');

mysqli_stmt_bind_param($stmt, 'sssssiis',
    $name, $desc, $category, $specs, $status, $featured, $imagesJson, $pricesJson);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    echo json_encode(['success'=>true,'message'=>'Product saved!','id'=>$newId,'images_saved'=>count($savedPaths)]);
} else {
    $err = mysqli_error($conn);
    mysqli_stmt_close($stmt);
    echo json_encode(['success'=>false,'message'=>'Database error: '.$err]);
}