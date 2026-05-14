<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$messageId = (int)($_POST['message_id'] ?? 0);
$newMessage = trim($_POST['new_message'] ?? '');

if ($messageId <= 0 || $newMessage === '') {
    echo json_encode(['success' => false, 'error' => 'Missing or invalid data']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $checkStmt = $conn->prepare("SELECT sender_id FROM messages WHERE id = ? LIMIT 1");
    $checkStmt->execute([$messageId]);
    $row = $checkStmt->fetch();

    if (!$row || $row['sender_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Message not found or unauthorized']);
        exit;
    }

    $updateStmt = $conn->prepare("UPDATE messages SET message = ? WHERE id = ? AND sender_id = ?");
    $updateStmt->execute([$newMessage, $messageId, $_SESSION['user_id']]);

    echo json_encode(['success' => true]);
    exit;

} catch (PDOException $e) {
    error_log("edit_message.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}