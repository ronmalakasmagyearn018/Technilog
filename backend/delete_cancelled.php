<?php
// ════════════════════════════════════════════════════
//  delete_cancelled.php
//  Location: Technilog/backend/delete_cancelled.php
//
//  Clears cancelled orders from the user's view (orders table)
//  but first backs them up to cancelled_records so the admin
//  always retains the full history.
// ════════════════════════════════════════════════════
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$ids     = $data['ids']     ?? [];
$user_id = intval($data['user_id'] ?? 0);
$email   = trim($data['email']   ?? '');

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No cancelled orders to delete.']); exit;
}
if (!$user_id && !$email) {
    echo json_encode(['success' => false, 'message' => 'Missing user identity.']); exit;
}

// Sanitize IDs
$ids = array_values(array_filter(array_map('intval', $ids)));
if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No valid IDs.']); exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

// ── Step 1: Fetch full order details BEFORE deleting ─────────────
if ($user_id && $email) {
    $selTypes = str_repeat('i', count($ids)) . 'is';
    $selSql   = "SELECT id, order_ref, user_id, customer_name, customer_email, customer_phone,
                        address, payment_method, service, installation_fee,
                        items_json, subtotal, shipping, total
                 FROM orders
                 WHERE id IN ($placeholders)
                   AND status = 'Cancelled'
                   AND (user_id = ? OR (user_id = 0 AND customer_email = ?))";
    $selBind  = array_merge($ids, [$user_id, $email]);
} elseif ($user_id) {
    $selTypes = str_repeat('i', count($ids)) . 'i';
    $selSql   = "SELECT id, order_ref, user_id, customer_name, customer_email, customer_phone,
                        address, payment_method, service, installation_fee,
                        items_json, subtotal, shipping, total
                 FROM orders
                 WHERE id IN ($placeholders)
                   AND status = 'Cancelled'
                   AND user_id = ?";
    $selBind  = array_merge($ids, [$user_id]);
} else {
    $selTypes = str_repeat('i', count($ids)) . 's';
    $selSql   = "SELECT id, order_ref, user_id, customer_name, customer_email, customer_phone,
                        address, payment_method, service, installation_fee,
                        items_json, subtotal, shipping, total
                 FROM orders
                 WHERE id IN ($placeholders)
                   AND status = 'Cancelled'
                   AND user_id = 0
                   AND customer_email = ?";
    $selBind  = array_merge($ids, [$email]);
}

$selStmt = mysqli_prepare($conn, $selSql);
if (!$selStmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed (select): ' . mysqli_error($conn)]); exit;
}
mysqli_stmt_bind_param($selStmt, $selTypes, ...$selBind);
mysqli_stmt_execute($selStmt);
$selResult = mysqli_stmt_get_result($selStmt);
$ordersToBackup = [];
while ($row = mysqli_fetch_assoc($selResult)) {
    $ordersToBackup[] = $row;
}
mysqli_stmt_close($selStmt);

// ── Step 2: Insert missing ones into cancelled_records ────────────
// INSERT IGNORE skips any already logged (safe duplicate protection)
$insStmt = mysqli_prepare($conn,
    "INSERT IGNORE INTO cancelled_records
     (order_id, order_ref, user_id, customer_name, customer_email, customer_phone,
      address, payment_method, service, installation_fee,
      items_json, subtotal, shipping, total, cancelled_at)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())"
);

if ($insStmt) {
    foreach ($ordersToBackup as $o) {
        $oid   = (int)$o['id'];
        $oref  = (string)($o['order_ref'] ?? '');
        $ouid  = (int)$o['user_id'];
        $oname = (string)($o['customer_name'] ?? '');
        $oemail= (string)($o['customer_email'] ?? '');
        $ophone= (string)($o['customer_phone'] ?? '');
        $oaddr = (string)($o['address'] ?? '');
        $opay  = (string)($o['payment_method'] ?? '');
        $osvc  = (string)($o['service'] ?? '');
        $ofee  = (float)($o['installation_fee'] ?? 0);
        $oitems= (string)($o['items_json'] ?? '[]');
        $osub  = (float)($o['subtotal'] ?? 0);
        $oshp  = (float)($o['shipping'] ?? 0);
        $otot  = (float)($o['total'] ?? 0);

        mysqli_stmt_bind_param(
            $insStmt, 'isissssssdsddd',
            $oid, $oref, $ouid, $oname, $oemail, $ophone,
            $oaddr, $opay, $osvc, $ofee,
            $oitems, $osub, $oshp, $otot
        );
        mysqli_stmt_execute($insStmt);
    }
    mysqli_stmt_close($insStmt);
}

// ── Step 3: Delete from orders (clears user's view) ───────────────
if ($user_id && $email) {
    $delTypes = str_repeat('i', count($ids)) . 'is';
    $delSql   = "DELETE FROM orders
                 WHERE id IN ($placeholders)
                   AND status = 'Cancelled'
                   AND (user_id = ? OR (user_id = 0 AND customer_email = ?))";
    $delBind  = array_merge($ids, [$user_id, $email]);
} elseif ($user_id) {
    $delTypes = str_repeat('i', count($ids)) . 'i';
    $delSql   = "DELETE FROM orders
                 WHERE id IN ($placeholders)
                   AND status = 'Cancelled'
                   AND user_id = ?";
    $delBind  = array_merge($ids, [$user_id]);
} else {
    $delTypes = str_repeat('i', count($ids)) . 's';
    $delSql   = "DELETE FROM orders
                 WHERE id IN ($placeholders)
                   AND status = 'Cancelled'
                   AND user_id = 0
                   AND customer_email = ?";
    $delBind  = array_merge($ids, [$email]);
}

$delStmt = mysqli_prepare($conn, $delSql);
if (!$delStmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed (delete): ' . mysqli_error($conn)]); exit;
}
mysqli_stmt_bind_param($delStmt, $delTypes, ...$delBind);

if (!mysqli_stmt_execute($delStmt)) {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . mysqli_stmt_error($delStmt)]); exit;
}

$deleted = mysqli_stmt_affected_rows($delStmt);
mysqli_stmt_close($delStmt);

if ($deleted === 0) {
    echo json_encode(['success' => false, 'message' => 'No orders were deleted. They may already be gone or not belong to you.']); exit;
}

echo json_encode([
    'success' => true,
    'deleted' => $deleted,
    'message' => "$deleted cancelled order(s) cleared."
]);