<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$post_id           = intval($_POST['post_id']           ?? 0);
$user_id           = intval($_POST['user_id']           ?? 0);
$content           = trim($_POST['content']             ?? '');
$parent_comment_id = intval($_POST['parent_comment_id'] ?? 0) ?: null;

if (!$post_id || !$user_id || !$content) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Ensure parent_comment_id column exists
$conn->query("ALTER TABLE forum_comments ADD COLUMN IF NOT EXISTS parent_comment_id INT DEFAULT NULL");

$stmt = $conn->prepare("INSERT INTO forum_comments (post_id, user_id, content, parent_comment_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iisi', $post_id, $user_id, $content, $parent_comment_id);

if ($stmt->execute()) {
    $new_id = $stmt->insert_id;
    // Fetch the new comment with user info to return
    $stmt2 = $conn->prepare("
        SELECT fc.comment_id, fc.post_id, fc.content, fc.created_at, fc.parent_comment_id,
               u.id AS user_id, u.username, u.username AS fullname, u.role,
               COALESCE(u.profile_pic,'') AS profile_pic,
               0 AS like_count
        FROM forum_comments fc JOIN users u ON u.id = fc.user_id
        WHERE fc.comment_id = ?
    ");
    $stmt2->bind_param('i', $new_id);
    $stmt2->execute();
    $row = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();
    echo json_encode(['success' => true, 'comment_id' => $new_id, 'comment' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed: ' . $stmt->error]);
}
$stmt->close();
$conn->close();