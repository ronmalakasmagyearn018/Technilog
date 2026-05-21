<?php
// backend/get_user_contacts.php
// Returns contact messages + admin replies for the logged-in user
// Auth: user_id passed as GET/POST param (stored in localStorage on frontend)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'config.php';

$user_id = intval($_REQUEST['user_id'] ?? 0);
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, email, subject, message, admin_reply, status, created_at, replied_at,
            user_reply, user_replied_at
     FROM contacts
     WHERE user_id = ?
     ORDER BY created_at DESC"
);$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode(['success' => true, 'messages' => $messages]);
$stmt->close();
$conn->close();
?>