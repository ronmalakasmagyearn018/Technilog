<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

$user_id = intval($_POST['user_id'] ?? 0);
if ($user_id <= 0) { echo json_encode(['success' => false]); exit; }

$conn->query("UPDATE forum_notifications SET is_read = 1 WHERE owner_id = $user_id");
echo json_encode(['success' => true]);
$conn->close();