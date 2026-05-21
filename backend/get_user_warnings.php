<?php
ob_start();
require_once 'config.php';
ob_clean();

$user_id = intval($_REQUEST['user_id'] ?? 0);
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS user_warnings (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT NOT NULL,
        report_id   INT DEFAULT NULL,
        message     TEXT NOT NULL,
        is_read     TINYINT(1) NOT NULL DEFAULT 0,
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$stmt = mysqli_prepare($conn, "SELECT id, message, is_read, created_at FROM user_warnings WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$warnings = [];
$unread = 0;
while ($row = mysqli_fetch_assoc($result)) {
    if (!$row['is_read']) $unread++;
    $warnings[] = $row;
}
mysqli_stmt_close($stmt);

echo json_encode(['success' => true, 'warnings' => $warnings, 'unread' => $unread]);
mysqli_close($conn);