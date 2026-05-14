<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_GET['user'])) {
    echo json_encode(['messages' => [], 'updated_read_ids' => []]);
    exit;
}

$senderId   = (int) $_SESSION['user_id'];
$receiverId = (int) $_GET['user'];
$lastId     = isset($_GET['last_id']) ? (int) $_GET['last_id'] : 0;

try {
    $conn = Database::getInstance()->getConnection();

    $updatedIds = [];
    if ($receiverId > 0) {
        $selectStmt = $conn->prepare("
            SELECT id FROM messages
            WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
        ");
        $selectStmt->execute([$receiverId, $senderId]);
        $updatedIds = $selectStmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($updatedIds)) {
            $updateStmt = $conn->prepare("
                UPDATE messages SET is_read = 1
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
            ");
            $updateStmt->execute([$receiverId, $senderId]);
        }
    }

    $stmt = $conn->prepare("
        SELECT id, sender_id, receiver_id, message, is_read, created_at
        FROM messages
        WHERE
            ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
            AND id > ?
        ORDER BY id ASC
    ");
    $stmt->execute([$senderId, $receiverId, $receiverId, $senderId, $lastId]);
    $messages = $stmt->fetchAll();

    echo json_encode([
        'messages'         => $messages,
        'updated_read_ids' => $updatedIds
    ]);

} catch (PDOException $e) {
    error_log("fetch_messages.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}