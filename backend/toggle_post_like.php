<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }

$post_id = intval($_POST['post_id'] ?? 0);
$user_id = intval($_POST['user_id'] ?? 0);
if (!$post_id || !$user_id) { echo json_encode(['success'=>false,'message'=>'Missing fields']); exit; }

$conn->query("CREATE TABLE IF NOT EXISTS forum_post_likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL, user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $conn->prepare("SELECT like_id FROM forum_post_likes WHERE post_id=? AND user_id=?");
$stmt->bind_param('ii', $post_id, $user_id);
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($exists) {
    $stmt = $conn->prepare("DELETE FROM forum_post_likes WHERE post_id=? AND user_id=?");
    $stmt->bind_param('ii', $post_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $liked = false;
} else {
    $stmt = $conn->prepare("INSERT IGNORE INTO forum_post_likes (post_id, user_id) VALUES (?,?)");
    $stmt->bind_param('ii', $post_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $liked = true;
}

$cnt = $conn->query("SELECT COUNT(*) AS c FROM forum_post_likes WHERE post_id=$post_id")->fetch_assoc()['c'];
echo json_encode(['success'=>true,'liked'=>$liked,'like_count'=>(int)$cnt]);
$conn->close();