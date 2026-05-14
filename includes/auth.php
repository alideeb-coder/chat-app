<?php
if (isset($_SESSION['user_id'])) {
    return;
}

if (isset($_COOKIE['remember_me'])) {
    $decoded = base64_decode($_COOKIE['remember_me']);
    $parts = explode(':', $decoded);
    if (count($parts) === 2) {
        $selector = $parts[0];
        $validator = $parts[1];

        try {
            $conn = Database::getInstance()->getConnection();

            $stmt = $conn->prepare(
                "SELECT r.id, r.hashed_validator, u.id AS user_id, u.username
                 FROM remember_me r
                 JOIN users u ON u.id = r.user_id
                 WHERE r.selector = ? AND r.expires > NOW()"
            );
            $stmt->execute([$selector]);
            $row = $stmt->fetch();

            if ($row && password_verify($validator, $row['hashed_validator'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                session_regenerate_id(true);

                $newSelector = bin2hex(random_bytes(16));
                $newValidator = bin2hex(random_bytes(32));
                $hashedNewValidator = password_hash($newValidator, PASSWORD_DEFAULT);
                $newExpires = date('Y-m-d H:i:s', strtotime('+30 days'));

                $deleteStmt = $conn->prepare("DELETE FROM remember_me WHERE id = ?");
                $deleteStmt->execute([$row['id']]);

                $insertStmt = $conn->prepare(
                    "INSERT INTO remember_me (user_id, selector, hashed_validator, expires)
                     VALUES (?, ?, ?, ?)"
                );
                $insertStmt->execute([
                    $row['user_id'],
                    $newSelector,
                    $hashedNewValidator,
                    $newExpires
                ]);

                setcookie(
                    'remember_me',
                    base64_encode($newSelector . ':' . $newValidator),
                    time() + (30 * 24 * 60 * 60),
                    '/',
                    '',
                    !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                    true
                );
                return; 
            }

            if (isset($selector)) {
                $cleanStmt = $conn->prepare("DELETE FROM remember_me WHERE selector = ?");
                $cleanStmt->execute([$selector]);
            }
        } catch (PDOException $e) {
            error_log('auth.php REMEMBER_ME error: ' . $e->getMessage());
        }
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
}

header('Location: login.php');
exit;