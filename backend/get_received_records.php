<?php
// get_received_records.php — TECHNILOG/backend/
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$result = mysqli_query($conn,
    'SELECT r.*, COALESCE(u.username,"Guest") AS username
     FROM received_records r
     LEFT JOIN users u ON u.id = r.user_id
     ORDER BY r.received_at DESC');

if (!$result) { echo json_encode(['error'=>mysqli_error($conn)]); exit; }
$rows = [];
while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
echo json_encode($rows);