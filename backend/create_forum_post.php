<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = intval($_POST['user_id'] ?? 0);
$title   = trim($_POST['title']   ?? '');
$content = trim($_POST['content'] ?? '');
$tag     = trim($_POST['tag']     ?? 'general');

if (!$user_id)            { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }
if (!$title || !$content) { echo json_encode(['success' => false, 'message' => 'Title and content are required']); exit; }

$allowed = ['general','tips','question','review'];
if (!in_array($tag, $allowed)) $tag = 'general';

$conn->query("ALTER TABLE forum_posts ADD COLUMN IF NOT EXISTS media_path TEXT DEFAULT NULL");
$conn->query("ALTER TABLE forum_posts ADD COLUMN IF NOT EXISTS media_type VARCHAR(20) DEFAULT NULL");

$media_path = null;
$media_type = null;

$imageTypes = ['image/jpeg','image/png','image/gif','image/webp'];
$videoTypes = ['video/mp4','video/webm','video/ogg','video/quicktime','video/x-msvideo','video/x-matroska'];
$MAX_FILES  = 5;
$MAX_SIZE   = 100 * 1024 * 1024;

if (!empty($_FILES['images'])) {
    // dirname(__FILE__) = Technilog/backend
    // going up one level  = Technilog/
    // upload folder       = Technilog/uploads/forum/   <-- matches what frontend expects
    // NEVER use DOCUMENT_ROOT: on XAMPP it points to htdocs root, outside the project.
    $uploadDir = dirname(__FILE__) . '/../uploads/forum/';

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        echo json_encode(['success' => false, 'message' => 'Upload directory not writable: ' . $uploadDir]);
        exit;
    }

    $savedPaths = [];
    $savedType  = null;

    $names    = (array)$_FILES['images']['name'];
    $tmpNames = (array)$_FILES['images']['tmp_name'];
    $sizes    = (array)$_FILES['images']['size'];
    $errors   = (array)$_FILES['images']['error'];

    $fileCount = min(count($names), $MAX_FILES);

    for ($i = 0; $i < $fileCount; $i++) {
        if ($errors[$i] !== UPLOAD_ERR_OK || empty($tmpNames[$i])) continue;

        if ($sizes[$i] > $MAX_SIZE) {
            echo json_encode(['success' => false, 'message' => 'File ' . ($i+1) . ' exceeds 100 MB limit.']);
            exit;
        }

        $mime = mime_content_type($tmpNames[$i]);

        if (in_array($mime, $imageTypes)) {
            $fileType = 'image';
            $ext = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION)) ?: 'jpg';
        } elseif (in_array($mime, $videoTypes)) {
            $fileType = 'video';
            $ext = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION)) ?: 'mp4';
        } else {
            echo json_encode(['success' => false, 'message' => 'File ' . ($i+1) . ' unsupported type: ' . $mime]);
            exit;
        }

        $filename = 'post_' . $user_id . '_' . time() . '_' . $i . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target   = $uploadDir . $filename;

        if (!move_uploaded_file($tmpNames[$i], $target)) {
            echo json_encode(['success' => false, 'message' => 'Could not save file ' . ($i+1) . '. Dir: ' . $uploadDir]);
            exit;
        }

        $savedPaths[] = ['path' => 'uploads/forum/' . $filename, 'type' => $fileType];
        $savedType    = $fileType;
    }

    if (!empty($savedPaths)) {
        // Store as array of {path, type} objects so mixed image+video works
        $media_path = json_encode($savedPaths);
        $media_type = 'mixed'; // new unified type
    }
}

$stmt = $conn->prepare(
    "INSERT INTO forum_posts (user_id, title, content, tag, media_path, media_type) VALUES (?, ?, ?, ?, ?, ?)"
);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param('isssss', $user_id, $title, $content, $tag, $media_path, $media_type);

if ($stmt->execute()) {
    echo json_encode([
        'success'          => true,
        'post_id'          => $stmt->insert_id,
        'debug_media_path' => $media_path,
        'debug_media_type' => $media_type
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $stmt->error]);
}
$stmt->close();
$conn->close();