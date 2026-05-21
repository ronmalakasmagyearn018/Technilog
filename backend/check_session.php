<?php
// backend/check_session.php
// Returns current login state — used by frontend JS.

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success'     => true,
        'loggedIn'    => true,
        'logged_in'   => true,
        'user_id'     => $_SESSION['user_id'],
        'id'          => $_SESSION['user_id'],
        'name'        => $_SESSION['name']        ?? '',
        'fullname'    => $_SESSION['fullname']     ?? $_SESSION['name'] ?? '',
        'username'    => $_SESSION['username']     ?? $_SESSION['name'] ?? '',
        'email'       => $_SESSION['email']        ?? '',
        'role'        => $_SESSION['role']         ?? 'user',
        'profile_pic' => $_SESSION['profile_pic']  ?? '',
    ]);
} else {
    echo json_encode([
        'success'  => false,
        'loggedIn' => false,
        'logged_in'=> false,
    ]);
}
?>