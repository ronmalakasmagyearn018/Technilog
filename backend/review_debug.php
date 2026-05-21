<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

echo "=== REVIEW DEBUG ===\n\n";

// Check if profile_pic column exists
$r = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_pic'");
echo "1. profile_pic column exists: " . ($r && $r->num_rows > 0 ? "YES" : "NO") . "\n\n";

// Try the exact query review.php uses
echo "2. Testing review query (no rating filter):\n";
$product_id = 16; // from your URL
$stmt = $conn->prepare("
    SELECT r.id, r.order_ref, r.product_name, r.user_name, r.user_id, r.rating, r.comment,
           r.image_path, r.admin_reply, r.replied_at, r.created_at,
           COALESCE(u.profile_pic, '') AS avatar_path
    FROM reviews r
    LEFT JOIN users u ON u.id = r.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");

if (!$stmt) {
    echo "PREPARE FAILED: " . $conn->error . "\n";
} else {
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "Query OK. Rows: " . $result->num_rows . "\n";
}

echo "\n3. Checking reviews table:\n";
$r2 = $conn->query("SHOW TABLES LIKE 'reviews'");
echo "reviews table exists: " . ($r2 && $r2->num_rows > 0 ? "YES" : "NO") . "\n";

if ($r2 && $r2->num_rows > 0) {
    $r3 = $conn->query("SELECT COUNT(*) as c FROM reviews WHERE product_id = 16");
    $row = $r3->fetch_assoc();
    echo "Reviews for product 16: " . $row['c'] . "\n";
}

echo "\n4. Raw JSON from review.php?action=get&product_id=16:\n";
ob_start();
$_GET['action'] = 'get';
$_GET['product_id'] = '16';
include 'review.php';
$out = ob_get_clean();
echo substr($out, 0, 500) . "\n";