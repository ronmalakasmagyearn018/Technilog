<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

$owner_id   = intval($_POST['owner_id']   ?? 0);
$actor_id   = intval($_POST['actor_id']   ?? 0);
$post_id    = intval($_POST['post_id']    ?? 0);
$type       = $_POST['type']       ?? '';
$post_title = $_POST['post_title'] ?? '';

if (!$owner_id || !$actor_id || !$post_id || !in_array($type, ['like','comment','reply','comment_like']) || $owner_id === $actor_id) {
    echo json_encode(['success' => false]); exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS forum_notifications (
    notif_id   INT AUTO_INCREMENT PRIMARY KEY,
    owner_id   INT NOT NULL,
    actor_id   INT NOT NULL,
    post_id    INT NOT NULL,
    type       ENUM('like','comment','reply','comment_like') NOT NULL,
    post_title VARCHAR(255) NOT NULL DEFAULT '',
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_like (owner_id, actor_id, post_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $conn->prepare("INSERT IGNORE INTO forum_notifications (owner_id, actor_id, post_id, type, post_title) VALUES (?,?,?,?,?)");
$stmt->bind_param('iiiss', $owner_id, $actor_id, $post_id, $type, $post_title);
$stmt->execute();
$stmt->close();
echo json_encode(['success' => true]);
$conn->close();