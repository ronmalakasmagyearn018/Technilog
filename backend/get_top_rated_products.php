<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

$limit = intval($_GET['limit'] ?? 10);
if ($limit < 1 || $limit > 50) $limit = 10;

// Check reviews table exists
$check = $conn->query("SHOW TABLES LIKE 'reviews'");
if (!$check || $check->num_rows === 0) {
    echo json_encode(['success' => true, 'products' => []]);
    exit;
}

$stmt = $conn->prepare("
    SELECT
        r.product_id,
        COALESCE(NULLIF(NULLIF(TRIM(r.product_name), ''), '0'), p.name, 'Unknown Product') AS product_name,
        COUNT(r.id)             AS review_count,
        ROUND(AVG(r.rating), 1) AS avg_rating,
        SUM(r.rating = 5)       AS five_star,
        SUM(r.rating = 4)       AS four_star,
        SUM(r.rating = 3)       AS three_star,
        SUM(r.rating = 2)       AS two_star,
        SUM(r.rating = 1)       AS one_star,
        p.images_json           AS images
    FROM reviews r
    LEFT JOIN products p ON p.id = r.product_id
    GROUP BY r.product_id, r.product_name, p.name, p.images_json
    ORDER BY avg_rating DESC, review_count DESC
    LIMIT ?
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$stmt->bind_param('i', $limit);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    // Parse images_json into array (same as get_products.php)
    $row['images'] = json_decode($row['images'] ?? '[]', true) ?: [];
    unset($row['images_json']);
    $products[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'products' => $products]);