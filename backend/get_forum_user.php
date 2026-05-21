<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

$target_user_id = intval($_GET['user_id'] ?? 0);
if ($target_user_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid user ID']); exit; }

$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(512) DEFAULT NULL");

$stmt = $conn->prepare("
    SELECT id AS user_id, username, username AS fullname, email, role,
        address, COALESCE(profile_pic, '') AS profile_pic, '' AS phone
    FROM users WHERE id = ? LIMIT 1
");
if (!$stmt) { echo json_encode(['success' => false, 'message' => $conn->error]); exit; }
$stmt->bind_param('i', $target_user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { echo json_encode(['success' => false, 'message' => 'User not found']); exit; }
$user = $result->fetch_assoc();
echo json_encode(['success' => true, 'user' => $user]);
$stmt->close();
$conn->close();