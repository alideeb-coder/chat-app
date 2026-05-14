<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$senderId   = (int) $_SESSION['user_id'];
$receiverId = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : 0;
$message    = trim($_POST['message'] ?? '');

if ($receiverId <= 0 || $message === '') {
    echo json_encode(['success' => false, 'error' => 'Missing or invalid data']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare("
        INSERT INTO messages (sender_id, receiver_id, message)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$senderId, $receiverId, $message]);

    echo json_encode(['success' => true]);
    exit;

} catch (PDOException $e) {
    error_log("send_message.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}