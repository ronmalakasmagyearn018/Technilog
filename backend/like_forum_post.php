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

$post_id = intval($_POST['post_id'] ?? 0);
$user_id = intval($_POST['user_id'] ?? 0);

if (!$post_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post or user']);
    exit;
}

$check = $conn->prepare("SELECT like_id FROM forum_post_likes WHERE post_id = ? AND user_id = ?");
$check->bind_param('ii', $post_id, $user_id);
$check->execute();
$check->store_result();
$alreadyLiked = $check->num_rows > 0;
$check->close();

if ($alreadyLiked) {
    $del = $conn->prepare("DELETE FROM forum_post_likes WHERE post_id = ? AND user_id = ?");
    $del->bind_param('ii', $post_id, $user_id);
    $del->execute();
    $del->close();
    $liked = false;
} else {
    $ins = $conn->prepare("INSERT INTO forum_post_likes (post_id, user_id) VALUES (?, ?)");
    $ins->bind_param('ii', $post_id, $user_id);
    $ins->execute();
    $ins->close();
    $liked = true;
}

$cnt = $conn->prepare("SELECT COUNT(*) AS total FROM forum_post_likes WHERE post_id = ?");
$cnt->bind_param('i', $post_id);
$cnt->execute();
$row = $cnt->get_result()->fetch_assoc();
$cnt->close();
$conn->close();

echo json_encode(['success' => true, 'liked' => $liked, 'like_count' => intval($row['total'])]);