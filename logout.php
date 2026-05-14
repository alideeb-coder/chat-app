<?php
require_once './includes/session.php';
require_once './config/db.php';


if (isset($_SESSION['user_id'])) {
    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("DELETE FROM remember_me WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

$_SESSION = [];


if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}


setcookie(
    'remember_me',
    '',
    time() - 42000,
    '/',
    '',
    !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    true
);

session_destroy();

header('Location: index.php?logout=1');
exit;