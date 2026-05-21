<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }

$comment_id = intval($_POST['comment_id'] ?? 0);
$user_id    = intval($_POST['user_id']    ?? 0);
$owner_id   = intval($_POST['owner_id']   ?? 0);  // comment author
$post_id    = intval($_POST['post_id']    ?? 0);
$post_title = trim($_POST['post_title']   ?? '');

if (!$comment_id || !$user_id) { echo json_encode(['success'=>false,'message'=>'Missing fields']); exit; }

$conn->query("CREATE TABLE IF NOT EXISTS forum_comment_likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_comment_like (comment_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Check if already liked
$stmt = $conn->prepare("SELECT like_id FROM forum_comment_likes WHERE comment_id=? AND user_id=?");
$stmt->bind_param('ii', $comment_id, $user_id);
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($exists) {
    $stmt = $conn->prepare("DELETE FROM forum_comment_likes WHERE comment_id=? AND user_id=?");
    $stmt->bind_param('ii', $comment_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $liked = false;
} else {
    $stmt = $conn->prepare("INSERT IGNORE INTO forum_comment_likes (comment_id, user_id) VALUES (?,?)");
    $stmt->bind_param('ii', $comment_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $liked = true;

    // Create notification if liking someone else's comment
    if ($owner_id && $owner_id !== $user_id && $post_id) {
        $conn->query("CREATE TABLE IF NOT EXISTS forum_notifications (
            notif_id   INT AUTO_INCREMENT PRIMARY KEY,
            owner_id   INT NOT NULL, actor_id INT NOT NULL, post_id INT NOT NULL,
            type       ENUM('like','comment','reply','comment_like') NOT NULL,
            post_title VARCHAR(255) NOT NULL DEFAULT '',
            is_read    TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_notif (owner_id, actor_id, post_id, type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $type = 'comment_like';
        $stmt2 = $conn->prepare("INSERT IGNORE INTO forum_notifications (owner_id, actor_id, post_id, type, post_title) VALUES (?,?,?,?,?)");
        $stmt2->bind_param('iiiss', $owner_id, $user_id, $post_id, $type, $post_title);
        $stmt2->execute();
        $stmt2->close();
    }
}

// Return new count
$cnt = $conn->query("SELECT COUNT(*) AS c FROM forum_comment_likes WHERE comment_id=$comment_id")->fetch_assoc()['c'];
echo json_encode(['success'=>true,'liked'=>$liked,'like_count'=>(int)$cnt]);
$conn->close();