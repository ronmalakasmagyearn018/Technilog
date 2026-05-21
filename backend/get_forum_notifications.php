<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

$user_id = intval($_GET['user_id'] ?? 0);
if ($user_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid user ID']); exit; }

// DDL moved to one-time setup — skipped here for performance

// Fetch all notifications for this user
$stmt = $conn->prepare("
    SELECT n.notif_id, n.type, n.post_id, n.post_title, n.is_read, n.created_at,
           u.id AS actor_id, u.username AS actor_name,
           COALESCE(u.profile_pic, '') AS actor_pic
    FROM forum_notifications n
    JOIN users u ON u.id = n.actor_id
    WHERE n.owner_id = ?
    ORDER BY n.created_at DESC
    LIMIT 50
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifs = [];
while ($row = $result->fetch_assoc()) $notifs[] = $row;
$stmt->close();

$unread = count(array_filter($notifs, function($n){ return !$n['is_read']; }));

echo json_encode(['success' => true, 'notifications' => $notifs, 'unread' => $unread]);
$conn->close();