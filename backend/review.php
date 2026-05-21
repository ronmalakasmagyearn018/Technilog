<?php
// ============================================================
//  review.php — Technilog/backend/review.php
//  Handles: submit review, get reviews, admin reply
// ============================================================
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

// ── Auto-create reviews table ────────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS reviews (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT NOT NULL,
    order_ref     VARCHAR(50) NOT NULL,
    product_id    INT NOT NULL,
    product_name  VARCHAR(255) NOT NULL DEFAULT '',
    user_id       INT NOT NULL,
    user_name     VARCHAR(255) NOT NULL DEFAULT '',
    rating        TINYINT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment       TEXT NOT NULL,
    image_path    VARCHAR(512) DEFAULT NULL,
    admin_reply   TEXT DEFAULT NULL,
    replied_at    DATETIME DEFAULT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_order_product (order_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$action = trim($_GET['action'] ?? $_POST['action'] ?? '');

// ────────────────────────────────────────────────────────────
// ACTION: get — fetch reviews for a product
// GET ?action=get&product_id=X
// ────────────────────────────────────────────────────────────
if ($action === 'get') {
    $product_id = intval($_GET['product_id'] ?? 0);
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'product_id required']); exit;
    }

    $rating_filter = intval($_GET['rating'] ?? 0); // 0 = all

    if ($rating_filter >= 1 && $rating_filter <= 5) {
        $stmt = $conn->prepare("
            SELECT r.id, r.order_ref, r.product_name, r.user_name, r.user_id, r.rating, r.comment,
                   r.image_path, r.admin_reply, r.replied_at, r.created_at,
                   COALESCE(u.profile_pic, '') AS avatar_path
            FROM reviews r
            LEFT JOIN users u ON u.id = r.user_id
            WHERE r.product_id = ? AND r.rating = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->bind_param('ii', $product_id, $rating_filter);
    } else {
        $stmt = $conn->prepare("
            SELECT r.id, r.order_ref, r.product_name, r.user_name, r.user_id, r.rating, r.comment,
                   r.image_path, r.admin_reply, r.replied_at, r.created_at,
                   COALESCE(u.profile_pic, '') AS avatar_path
            FROM reviews r
            LEFT JOIN users u ON u.id = r.user_id
            WHERE r.product_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->bind_param('i', $product_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = [];
    while ($row = $result->fetch_assoc()) $reviews[] = $row;

    // Star breakdown counts (always for all ratings)
    $breakdown_stmt = $conn->prepare("
        SELECT rating, COUNT(*) AS count FROM reviews
        WHERE product_id = ?
        GROUP BY rating ORDER BY rating DESC
    ");
    $breakdown_stmt->bind_param('i', $product_id);
    $breakdown_stmt->execute();
    $bResult = $breakdown_stmt->get_result();
    $breakdown = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
    while ($b = $bResult->fetch_assoc()) $breakdown[intval($b['rating'])] = intval($b['count']);

    $total = array_sum($breakdown);
    $avg   = $total > 0
        ? round(array_sum(array_map(fn($s,$c) => $s * $c, array_keys($breakdown), $breakdown)) / $total, 1)
        : 0;

    echo json_encode([
        'success'   => true,
        'reviews'   => $reviews,
        'breakdown' => $breakdown,
        'total'     => $total,
        'average'   => $avg
    ]);
    exit;
}

// ────────────────────────────────────────────────────────────
// ACTION: check — can this user review this product for this order?
// GET ?action=check&order_id=X&product_id=Y&user_id=Z
// ────────────────────────────────────────────────────────────
if ($action === 'check') {
    $order_id   = intval($_GET['order_id']   ?? 0);
    $product_id = intval($_GET['product_id'] ?? 0);
    $user_id    = intval($_GET['user_id']    ?? 0);

    if (!$order_id || !$product_id || !$user_id) {
        echo json_encode(['can_review' => false, 'reason' => 'Missing params']); exit;
    }

    // Must be Received status — check by user_id OR by email fallback (guest orders)
    $os = $conn->prepare("SELECT status FROM orders WHERE id = ? AND (user_id = ? OR user_id = 0)");
    $os->bind_param('ii', $order_id, $user_id);
    $os->execute();
    $order = $os->get_result()->fetch_assoc();

    if (!$order) {
        // Last resort: just check order exists and is received (admin/guest edge case)
        $os2 = $conn->prepare("SELECT status FROM orders WHERE id = ?");
        $os2->bind_param('i', $order_id);
        $os2->execute();
        $order = $os2->get_result()->fetch_assoc();
        if (!$order) {
            echo json_encode(['can_review' => false, 'reason' => 'Order not found']); exit;
        }
    }
    if (strtolower($order['status']) !== 'received') {
        echo json_encode(['can_review' => false, 'reason' => 'Order not yet received']); exit;
    }

    // Check if already reviewed
    $rs = $conn->prepare("SELECT id FROM reviews WHERE order_id = ? AND product_id = ?");
    $rs->bind_param('ii', $order_id, $product_id);
    $rs->execute();
    $existing = $rs->get_result()->fetch_assoc();

    echo json_encode(['can_review' => !$existing, 'already_reviewed' => (bool)$existing]);
    exit;
}

// ────────────────────────────────────────────────────────────
// ACTION: submit — POST a new review (multipart/form-data)
// ────────────────────────────────────────────────────────────
if ($action === 'submit') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'POST required']); exit;
    }

    $order_id     = intval($_POST['order_id']     ?? 0);
    $product_id   = intval($_POST['product_id']   ?? 0);
    $user_id      = intval($_POST['user_id']      ?? 0);
    $rating       = intval($_POST['rating']       ?? 0);
    $comment      = trim($_POST['comment']        ?? '');
    $product_name = trim($_POST['product_name']   ?? '');
    $user_name    = trim($_POST['user_name']      ?? '');

    // Validation
    if (!$order_id || !$product_id || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']); exit;
    }
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating must be 1–5']); exit;
    }
    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'Review comment is required']); exit;
    }

    // Verify order is Received and belongs to user (or was a guest order)
    $os = $conn->prepare("SELECT order_ref, status FROM orders WHERE id = ? AND (user_id = ? OR user_id = 0)");
    $os->bind_param('ii', $order_id, $user_id);
    $os->execute();
    $order = $os->get_result()->fetch_assoc();

    if (!$order) {
        // Fallback: just find the order by id
        $os2 = $conn->prepare("SELECT order_ref, status FROM orders WHERE id = ?");
        $os2->bind_param('i', $order_id);
        $os2->execute();
        $order = $os2->get_result()->fetch_assoc();
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']); exit;
        }
    }
    if (strtolower($order['status']) !== 'received') {
        echo json_encode(['success' => false, 'message' => 'You can only review after marking order as received']); exit;
    }

    // Check duplicate
    $rs = $conn->prepare("SELECT id FROM reviews WHERE order_id = ? AND product_id = ?");
    $rs->bind_param('ii', $order_id, $product_id);
    $rs->execute();
    if ($rs->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'You already reviewed this item']); exit;
    }

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid image type']); exit;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Image too large (max 5MB)']); exit;
        }
        $uploadDir = __DIR__ . '/../uploads/reviews/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename   = 'review_' . $user_id . '_' . $product_id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $image_path = 'uploads/reviews/' . $filename;
        }
    }

    $order_ref = $order['order_ref'];
    $stmt = $conn->prepare("
        INSERT INTO reviews
            (order_id, order_ref, product_id, product_name, user_id, user_name, rating, comment, image_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('issississ',
        $order_id, $order_ref, $product_id, $product_name,
        $user_id, $user_name, $rating, $comment, $image_path
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Review submitted!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed: ' . $stmt->error]);
    }
    exit;
}

// ────────────────────────────────────────────────────────────
// ACTION: reply — Admin only, POST a reply to a review
// ────────────────────────────────────────────────────────────
if ($action === 'reply') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'POST required']); exit;
    }

    $data       = json_decode(file_get_contents('php://input'), true);
    $review_id  = intval($data['review_id'] ?? 0);
    $admin_id   = intval($data['admin_id']  ?? 0);
    $reply      = trim($data['reply']       ?? '');

    if (!$review_id || !$reply) {
        echo json_encode(['success' => false, 'message' => 'review_id and reply required']); exit;
    }

    // Verify admin role
    $as = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $as->bind_param('i', $admin_id);
    $as->execute();
    $admin = $as->get_result()->fetch_assoc();
    if (!$admin || $admin['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
    }

    $stmt = $conn->prepare("
        UPDATE reviews SET admin_reply = ?, replied_at = NOW() WHERE id = ?
    ");
    $stmt->bind_param('si', $reply, $review_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reply posted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed: ' . $stmt->error]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);