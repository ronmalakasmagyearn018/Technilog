<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

$user_id = intval($_GET['user_id'] ?? 0);
if (!$user_id) {
    echo json_encode(['success' => true, 'liked_post_ids' => []]);
    exit;
}

$stmt = $conn->prepare("SELECT post_id FROM forum_post_likes WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$ids = [];
while ($row = $result->fetch_assoc()) {
    $ids[] = (string)$row['post_id'];
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'liked_post_ids' => $ids]);