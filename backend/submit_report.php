<?php
ob_start();
require_once 'config.php';
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Auto-create forum_reports table if it doesn't exist
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS forum_reports (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        reporter_id      INT NOT NULL,
        reported_user_id INT NOT NULL,
        post_id          INT NOT NULL,
        reason           VARCHAR(100) NOT NULL,
        status           ENUM('Pending','Reviewed','Dismissed') NOT NULL DEFAULT 'Pending',
        admin_note       TEXT DEFAULT NULL,
        reviewed_at      DATETIME DEFAULT NULL,
        created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_post_id (post_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$reporter_id      = intval($_POST['reporter_id']      ?? 0);
$reported_user_id = intval($_POST['reported_user_id'] ?? 0);
$post_id          = intval($_POST['post_id']          ?? 0);
$reason           = trim($_POST['reason']             ?? '');

if (!$reporter_id || !$reported_user_id || !$post_id || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

if ($reporter_id === $reported_user_id) {
    echo json_encode(['success' => false, 'message' => 'You cannot report yourself.']);
    exit;
}

// Prevent reporting an admin
$stmt = mysqli_prepare($conn, "SELECT role FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $reported_user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$checkResult = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$checkResult) {
    echo json_encode(['success' => false, 'message' => 'Reported user not found.']);
    exit;
}
if ($checkResult['role'] === 'admin') {
    echo json_encode(['success' => false, 'message' => 'You cannot report an admin.']);
    exit;
}

// Prevent duplicate pending report
$stmt2 = mysqli_prepare($conn, "SELECT id FROM forum_reports WHERE reporter_id=? AND post_id=? AND status='Pending' LIMIT 1");
mysqli_stmt_bind_param($stmt2, 'ii', $reporter_id, $post_id);
mysqli_stmt_execute($stmt2);
$res2 = mysqli_stmt_get_result($stmt2);
$dupRow = mysqli_fetch_assoc($res2);
mysqli_stmt_close($stmt2);

if ($dupRow) {
    echo json_encode(['success' => false, 'message' => 'You already reported this post.']);
    exit;
}

$allowed_reasons = ['Spam','Harassment or Bullying','Hate Speech','Misinformation','Inappropriate Content','Scam or Fraud','Violence or Threats','Other'];
if (!in_array($reason, $allowed_reasons)) {
    echo json_encode(['success' => false, 'message' => 'Invalid report reason.']);
    exit;
}

$stmt3 = mysqli_prepare($conn, "INSERT INTO forum_reports (reporter_id, reported_user_id, post_id, reason) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt3, 'iiis', $reporter_id, $reported_user_id, $post_id, $reason);

if (mysqli_stmt_execute($stmt3)) {
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($conn)]);
}
mysqli_stmt_close($stmt3);
mysqli_close($conn);