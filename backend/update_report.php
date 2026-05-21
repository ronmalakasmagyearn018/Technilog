<?php
ob_start();
require_once 'config.php';
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']); exit;
}

$id         = intval($_POST['id']         ?? 0);
$status     = trim($_POST['status']       ?? '');
$admin_note = trim($_POST['admin_note']   ?? '');

$allowed = ['Pending', 'Reviewed', 'Dismissed'];
if (!$id || !in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']); exit;
}

$stmt = mysqli_prepare($conn, "UPDATE forum_reports SET status=?, admin_note=?, reviewed_at=NOW() WHERE id=?");
mysqli_stmt_bind_param($stmt, 'ssi', $status, $admin_note, $id);
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}
mysqli_stmt_close($stmt);
mysqli_close($conn);