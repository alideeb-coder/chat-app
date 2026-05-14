<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("UPDATE users SET last_seen = UTC_TIMESTAMP() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode(['success' => true]);
    exit;
} catch (PDOException $e) {
    error_log("update_activity.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}