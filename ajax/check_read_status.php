<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$ids = isset($_POST['ids']) ? json_decode($_POST['ids']) : [];
if (!is_array($ids) || empty($ids)) {
    echo json_encode(['success' => false, 'read_ids' => []]);
    exit;
}

try {
    $ids = array_map('intval', $ids);

    $conn = Database::getInstance()->getConnection();
    $currentUserId = $_SESSION['user_id'];

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("
        SELECT id FROM messages
        WHERE id IN ($placeholders)
          AND sender_id = ?
          AND is_read = 1
    ");
    $stmt->execute([...$ids, $currentUserId]);
    $readIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'read_ids' => $readIds]);
    exit;
} catch (PDOException $e) {
    error_log("check_read_status.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}