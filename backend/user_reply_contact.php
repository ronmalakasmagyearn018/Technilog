<?php
// backend/user_reply_contact.php
// Allows a logged-in user to reply to an admin's reply on their contact message
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'config.php';

$contact_id = intval($_POST['contact_id'] ?? 0);
$user_id    = intval($_POST['user_id']    ?? 0);
$user_reply = trim($_POST['user_reply']   ?? '');

if (!$contact_id || !$user_id || !$user_reply) {
    echo json_encode(['success' => false, 'message' => 'Missing fields.']);
    exit;
}

// Make sure this contact belongs to this user
$check = $conn->prepare("SELECT id FROM contacts WHERE id = ? AND user_id = ?");
$check->bind_param('ii', $contact_id, $user_id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

$stmt = $conn->prepare(
    "UPDATE contacts
     SET user_reply = ?, user_replied_at = NOW(), user_reply_unread = 1
     WHERE id = ? AND user_id = ?"
);
$stmt->bind_param('sii', $user_reply, $contact_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Reply sent!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save reply.']);
}

$stmt->close();
$conn->close();
?>