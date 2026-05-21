<?php
// backend/reply_contact.php
header('Content-Type: application/json');
require_once 'config.php';

$contact_id  = intval($_POST['contact_id'] ?? 0);
$admin_reply = trim($_POST['admin_reply'] ?? '');

if (!$contact_id || !$admin_reply) {
    echo json_encode(['success' => false, 'message' => 'Missing fields.']);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE contacts
     SET admin_reply = ?, status = 'Replied', replied_at = NOW()
     WHERE id = ?"
);
$stmt->bind_param('si', $admin_reply, $contact_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Reply sent successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save reply.']);
}

$stmt->close();
$conn->close();
?>