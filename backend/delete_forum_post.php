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
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$check = $conn->prepare("SELECT user_id FROM forum_posts WHERE post_id = ?");
$check->bind_param('i', $post_id);
$check->execute();
$post = $check->get_result()->fetch_assoc();
$check->close();

if (!$post) { echo json_encode(['success' => false, 'message' => 'Post not found']); exit; }

$ucheck = $conn->prepare("SELECT role FROM users WHERE id = ?");
$ucheck->bind_param('i', $user_id);
$ucheck->execute();
$user = $ucheck->get_result()->fetch_assoc();
$ucheck->close();

if ($post['user_id'] != $user_id && ($user['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$dc = $conn->prepare("DELETE FROM forum_comments WHERE post_id = ?");
if ($dc) { $dc->bind_param('i', $post_id); $dc->execute(); $dc->close(); }

$dl = $conn->prepare("DELETE FROM forum_post_likes WHERE post_id = ?");
if ($dl) { $dl->bind_param('i', $post_id); $dl->execute(); $dl->close(); }

$dp = $conn->prepare("DELETE FROM forum_posts WHERE post_id = ?");
$dp->bind_param('i', $post_id);

if ($dp->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $dp->error]);
}
$dp->close();
$conn->close();