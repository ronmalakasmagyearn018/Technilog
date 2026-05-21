<?php
// backend/get_contacts.php
// Returns all contact messages for admin panel
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'config.php';

$sql = "
    SELECT
        c.id,
        c.user_id,
        c.email,
        c.subject,
        c.message,
        c.admin_reply,
        c.status,
        c.created_at,
        c.replied_at,
        c.user_reply,
        c.user_replied_at,
        c.user_reply_unread,
        COALESCE(u.username, 'Unknown') AS user_name
    FROM contacts c
    LEFT JOIN users u ON u.id = c.user_id
    ORDER BY
        CASE WHEN c.user_reply_unread = 1 THEN 0 ELSE 1 END ASC,
        CASE WHEN c.status = 'Pending' THEN 0 ELSE 1 END ASC,
        c.created_at DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . mysqli_error($conn)]);
    exit;
}

$contacts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $contacts[] = $row;
}

echo json_encode(['success' => true, 'contacts' => $contacts]);
mysqli_close($conn);
?>