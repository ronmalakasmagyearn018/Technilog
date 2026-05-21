<?php
ob_start();
require_once 'config.php';
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]); exit;
}

$user_id = intval($_POST['user_id'] ?? 0);
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']); exit;
}

mysqli_query($conn, "UPDATE user_warnings SET is_read=1 WHERE user_id=$user_id AND is_read=0");
echo json_encode(['success' => true]);
mysqli_close($conn);