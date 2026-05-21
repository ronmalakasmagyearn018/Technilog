<?php
ob_start();
require_once 'config.php';
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id    = intval($_POST['user_id']    ?? 0);
$report_id  = intval($_POST['report_id']  ?? 0);
$warn_msg   = trim($_POST['warn_message'] ?? '');

if (!$user_id || !$warn_msg) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
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

$stmt = mysqli_prepare($conn, "INSERT INTO user_warnings (user_id, report_id, message) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'iis', $user_id, $report_id, $warn_msg);

if (mysqli_stmt_execute($stmt)) {
    if ($report_id) {
        $note = 'Warning sent to user.';
        mysqli_query($conn, "UPDATE forum_reports SET status='Reviewed', reviewed_at=NOW() WHERE id=$report_id");
    }
    echo json_encode(['success' => true, 'message' => 'Warning sent to user.']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($conn)]);
}
mysqli_stmt_close($stmt);
mysqli_close($conn);