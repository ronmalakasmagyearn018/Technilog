<?php
// backend/update_user.php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }

$data   = json_decode(file_get_contents('php://input'), true);
$id     = (int)($data['id']     ?? 0);
$action = trim($data['action']  ?? '');

if (!$id || !$action) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }

// Block status changes on admin accounts
if (in_array($action, ['verify','unverify','ban','unban'])) {
    $chk = mysqli_prepare($conn, 'SELECT role FROM users WHERE id=?');
    mysqli_stmt_bind_param($chk, 'i', $id);
    mysqli_stmt_execute($chk);
    mysqli_stmt_bind_result($chk, $targetRole);
    mysqli_stmt_fetch($chk);
    mysqli_stmt_close($chk);
    if ($targetRole === 'admin') {
        echo json_encode(['success'=>false,'message'=>'Cannot change status of an admin account.']);
        exit;
    }
}

switch ($action) {
    case 'verify':
        $stmt = mysqli_prepare($conn, 'UPDATE users SET is_verified=1 WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        break;
    case 'unverify':
        $stmt = mysqli_prepare($conn, 'UPDATE users SET is_verified=0 WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        break;
    case 'ban':
        $stmt = mysqli_prepare($conn, 'UPDATE users SET is_banned=1 WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        break;
    case 'unban':
        $stmt = mysqli_prepare($conn, 'UPDATE users SET is_banned=0 WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        break;
    case 'make_admin':
        $stmt = mysqli_prepare($conn, 'UPDATE users SET role="admin" WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        break;
    case 'remove_admin':
        $stmt = mysqli_prepare($conn, 'UPDATE users SET role="user" WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        break;
    default:
        echo json_encode(['success'=>false,'message'=>'Unknown action.']); exit;
}

$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo json_encode(['success'=>$ok, 'message' => $ok ? 'Updated.' : mysqli_error($conn)]);