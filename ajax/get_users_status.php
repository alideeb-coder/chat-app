<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("SELECT id, last_seen FROM users WHERE id != ?");
    $stmt->execute([$_SESSION['user_id']]);
    $users = $stmt->fetchAll();
    echo json_encode($users);
    exit;
} catch (PDOException $e) {
    error_log("get_users_status.php error: " . $e->getMessage());
    echo json_encode([]);
    exit;
}