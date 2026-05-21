<?php
// backend/get_users.php
require_once __DIR__ . '/config.php';

    $result = mysqli_query($conn,
    'SELECT id, username, email, is_verified, is_banned, role, auth_provider, created_at
     FROM users ORDER BY created_at DESC');

    $users = [];
    while ($row = mysqli_fetch_assoc($result)) { $users[] = $row; }
    echo json_encode($users);