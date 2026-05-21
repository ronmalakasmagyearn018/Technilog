<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

$conn->query("CREATE TABLE IF NOT EXISTS forum_posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL, content TEXT NOT NULL, tag VARCHAR(50) DEFAULT 'general',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS forum_comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY, post_id INT NOT NULL, user_id INT NOT NULL,
    content TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS forum_post_likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY, post_id INT NOT NULL, user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY unique_like (post_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
// Add profile_pic column if it doesn't exist yet
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(512) DEFAULT NULL");
$conn->query("ALTER TABLE forum_posts ADD COLUMN IF NOT EXISTS media_path TEXT DEFAULT NULL");
$conn->query("ALTER TABLE forum_posts ADD COLUMN IF NOT EXISTS media_type VARCHAR(20) DEFAULT NULL");

$sql = "
    SELECT
        fp.post_id, fp.title, fp.content, fp.tag, fp.media_path, fp.media_type, fp.created_at, fp.updated_at,
        u.id       AS user_id,
        u.username,
        u.username AS fullname,
        u.email,
        u.role,
        COALESCE(u.profile_pic, '') AS profile_pic,
        (SELECT COUNT(*) FROM forum_comments fc WHERE fc.post_id = fp.post_id) AS comment_count,
        (SELECT COUNT(*) FROM forum_post_likes fl WHERE fl.post_id = fp.post_id) AS like_count
    FROM forum_posts fp
    JOIN users u ON u.id = fp.user_id
    ORDER BY fp.created_at DESC
";

$result = $conn->query($sql);
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    exit;
}

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
echo json_encode(['success' => true, 'posts' => $posts]);
$conn->close();