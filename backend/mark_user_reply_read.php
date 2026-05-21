<?php
// backend/mark_user_reply_read.php
// Called when admin opens/views a message that has an unread user reply
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'config.php';

$contact_id = intval($_POST['contact_id'] ?? 0);

if (!$contact_id) {
    echo json_encode(['success' => false, 'message' => 'Missing contact_id.']);
    exit;
}

$stmt = $conn->prepare("UPDATE contacts SET user_reply_unread = 0 WHERE id = ?");
$stmt->bind_param('i', $contact_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$conn->close();
?>