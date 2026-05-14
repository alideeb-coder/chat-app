<?php
require_once '../includes/session.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$receiver_id = $_SESSION['user_id'];

try {
    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare("
        SELECT sender_id, COUNT(*) as unread
        FROM messages
        WHERE receiver_id = ? AND is_read = 0
        GROUP BY sender_id
    ");
    $stmt->execute([$receiver_id]);
    $counts = $stmt->fetchAll();

    echo json_encode(['success' => true, 'counts' => $counts]);
    exit;

} catch (PDOException $e) {
    error_log("get_unread_counts.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}