<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

$post_id = intval($_GET['post_id'] ?? 0);
if ($post_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid post ID']); exit; }

$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(512) DEFAULT NULL");

// Add parent_comment_id column for threaded replies
$conn->query("ALTER TABLE forum_comments ADD COLUMN IF NOT EXISTS parent_comment_id INT DEFAULT NULL");

// Comment likes table
$conn->query("CREATE TABLE IF NOT EXISTS forum_comment_likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_comment_like (comment_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $conn->prepare("
    SELECT fc.comment_id, fc.post_id, fc.content, fc.created_at,
        fc.parent_comment_id,
        u.id AS user_id, u.username, u.username AS fullname, u.role,
        COALESCE(u.profile_pic, '') AS profile_pic,
        (SELECT COUNT(*) FROM forum_comment_likes fcl WHERE fcl.comment_id = fc.comment_id) AS like_count
    FROM forum_comments fc
    JOIN users u ON u.id = fc.user_id
    WHERE fc.post_id = ? ORDER BY fc.created_at ASC
");
if (!$stmt) { echo json_encode(['success' => false, 'message' => $conn->error]); exit; }
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = [];
while ($row = $result->fetch_assoc()) $comments[] = $row;
echo json_encode(['success' => true, 'comments' => $comments]);
$stmt->close();
$conn->close();