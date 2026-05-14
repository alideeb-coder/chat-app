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

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
    exit;
}
$extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($extension, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp']);
    exit;
}

$maxSize = 8 * 1024 * 1024;
if ($_FILES['avatar']['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File size exceeds 8MB limit']);
    exit;
}

if (!getimagesize($_FILES['avatar']['tmp_name'])) {
    echo json_encode(['success' => false, 'error' => 'Uploaded file is not a valid image']);
    exit;
}

$filename = bin2hex(random_bytes(16)) . '.' . $extension;
$uploadDir = __DIR__ . '/../uploads/avatars/';

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
        exit;
    }
}

$destination = $uploadDir . $filename;
if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare("SELECT image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $oldImage = $stmt->fetchColumn();

    if ($oldImage && !filter_var($oldImage, FILTER_VALIDATE_URL) && file_exists($uploadDir . $oldImage)) {
        unlink($uploadDir . $oldImage);
    }

    $updateStmt = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
    $updateStmt->execute([$filename, $_SESSION['user_id']]);

    echo json_encode([
        'success'   => true,
        'image_url' => 'uploads/avatars/' . $filename
    ]);
    exit;

} catch (PDOException $e) {
    error_log("upload_avatar.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}