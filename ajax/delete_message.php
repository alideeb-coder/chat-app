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
if ($messageId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
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

    $deleteStmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
    $deleteStmt->execute([$messageId, $_SESSION['user_id']]);

    echo json_encode(['success' => true]);
    exit;

} catch (PDOException $e) {
    error_log("delete_message.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}