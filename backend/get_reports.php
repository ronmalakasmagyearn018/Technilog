<?php
ob_start();
require_once 'config.php';
ob_clean();

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

$sql = "
    SELECT
        r.id,
        r.reason,
        r.status,
        r.created_at,
        r.admin_note,
        r.reviewed_at,
        r.post_id,
        fp.title            AS post_title,
        fp.content          AS post_content,
        reporter.id         AS reporter_id,
        reporter.username   AS reporter_name,
        reporter.email      AS reporter_email,
        reported.id         AS reported_id,
        reported.username   AS reported_name,
        reported.email      AS reported_email
    FROM forum_reports r
    LEFT JOIN forum_posts fp    ON fp.post_id  = r.post_id
    LEFT JOIN users reporter    ON reporter.id = r.reporter_id
    LEFT JOIN users reported    ON reported.id = r.reported_user_id
    ORDER BY
        FIELD(r.status,'Pending','Reviewed','Dismissed'),
        r.created_at DESC
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . mysqli_error($conn)]);
    exit;
}

$reports = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reports[] = $row;
}

$pending = 0;
foreach ($reports as $r) {
    if ($r['status'] === 'Pending') $pending++;
}

echo json_encode([
    'success' => true,
    'reports' => $reports,
    'total'   => count($reports),
    'pending' => $pending
]);
mysqli_close($conn);