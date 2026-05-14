<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$query = trim($_GET['query'] ?? '');

try {
    $conn = Database::getInstance()->getConnection();

    if ($query === '') {
        $stmt = $conn->prepare("SELECT id, username, last_seen FROM users WHERE id != ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $conn->prepare("SELECT id, username, last_seen FROM users WHERE username LIKE ? AND id != ?");
        $stmt->execute(["%$query%", $_SESSION['user_id']]);
    }

    $users = $stmt->fetchAll();

    echo json_encode(['success' => true, 'users' => $users]);
} catch (PDOException $e) {
    error_log("search_users.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
exit;