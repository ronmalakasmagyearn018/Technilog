<?php
/**
 * setup.php — Run this ONCE to create all tables and columns.
 * Visit: http://localhost/PHP%20proj/Technilog/backend/setup.php
 * After running, you do NOT need to run it again.
 */
require_once 'config.php';
header('Content-Type: text/plain');

$queries = [
    "CREATE TABLE IF NOT EXISTS forum_posts (
        post_id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL, content TEXT NOT NULL, tag VARCHAR(50) DEFAULT 'general',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS forum_comments (
        comment_id INT AUTO_INCREMENT PRIMARY KEY, post_id INT NOT NULL, user_id INT NOT NULL,
        content TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS forum_post_likes (
        like_id INT AUTO_INCREMENT PRIMARY KEY, post_id INT NOT NULL, user_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY unique_like (post_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS forum_comment_likes (
        like_id INT AUTO_INCREMENT PRIMARY KEY,
        comment_id INT NOT NULL, user_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_comment_like (comment_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS forum_notifications (
        notif_id   INT AUTO_INCREMENT PRIMARY KEY,
        owner_id   INT NOT NULL, actor_id INT NOT NULL, post_id INT NOT NULL,
        type       ENUM('like','comment','reply','comment_like') NOT NULL,
        post_title VARCHAR(255) NOT NULL DEFAULT '',
        is_read    TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_like (owner_id, actor_id, post_id, type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(512) DEFAULT NULL",
    "ALTER TABLE forum_posts ADD COLUMN IF NOT EXISTS media_path TEXT DEFAULT NULL",
    "ALTER TABLE forum_posts ADD COLUMN IF NOT EXISTS media_type VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE forum_comments ADD COLUMN IF NOT EXISTS parent_comment_id INT DEFAULT NULL",
];

foreach ($queries as $sql) {
    if ($conn->query($sql)) {
        echo "OK: " . substr($sql, 0, 60) . "...\n";
    } else {
        echo "ERR: " . $conn->error . "\n";
    }
}

echo "\nSetup complete!";
$conn->close();