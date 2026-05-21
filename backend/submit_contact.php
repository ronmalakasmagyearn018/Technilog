<?php
// backend/submit_contact.php
header('Content-Type: application/json');
require_once 'config.php';

$user_id = intval($_POST['user_id'] ?? 0);
$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to send a message.']);
    exit;
}

if (!$email || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO contacts (user_id, email, subject, message) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param('isss', $user_id, $email, $subject, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Your message has been sent! We will reply shortly.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
}

$stmt->close();
$conn->close();
?>