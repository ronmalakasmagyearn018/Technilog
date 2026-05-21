<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']); exit;
}

$user_id = intval($_POST['user_id'] ?? 0);
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

// Handle base64 avatar (sent from account.js)
$base64 = $_POST['avatar_base64'] ?? '';
if ($base64) {
    // Strip data URL prefix
    if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
        $ext      = strtolower($matches[1]);
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) $ext = 'jpg';
        $imgData  = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
        $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filepath = $uploadDir . $filename;
        if (file_put_contents($filepath, $imgData)) {
            $dbPath = 'uploads/avatars/' . $filename;
            // Add profile_pic column if it doesn't exist
            $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(512) DEFAULT NULL");
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->bind_param('si', $dbPath, $user_id);
            $stmt->execute();
            $stmt->close();
            $conn->close();
            echo json_encode(['success' => true, 'profile_pic' => $dbPath]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Failed to save image']); exit;
}

// Handle file upload
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $file     = $_FILES['avatar'];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']); exit;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']); exit;
    }
    $filename  = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/avatars/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        $dbPath = 'uploads/avatars/' . $filename;
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(512) DEFAULT NULL");
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param('si', $dbPath, $user_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'profile_pic' => $dbPath]);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Upload failed']); exit;
}

echo json_encode(['success' => false, 'message' => 'No image provided']);