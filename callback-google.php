<?php
require_once 'includes/session.php';
require_once 'vendor/autoload.php';
require_once 'config/db.php';

$client = new Google_Client();
$client->setClientId(getenv('GOOGLE_CLIENT_ID'));
$client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
$client->setRedirectUri('https://chat-app-vroj.onrender.com/callback-google.php');
$client->addScope('email');
$client->addScope('profile');

$code = $_GET['code'] ?? '';
if ($code === '') {
    error_log('Google OAuth: Authorization code missing');
    die('Authorization failed. Missing authorization code.');
}

function downloadGoogleAvatar(string $url): string
{
    if ($url === '') return '';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $imageContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($imageContent === false || $httpCode !== 200) {
        error_log("Google avatar download failed: HTTP $httpCode, error: $error, URL: $url");
        return '';
    }

    $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $extension = 'jpg';
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $uploadDir = __DIR__ . '/uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $destination = $uploadDir . $filename;
    file_put_contents($destination, $imageContent);
    return $filename;
}

try {
    $tokenData = $client->fetchAccessTokenWithAuthCode($code);
    if (isset($tokenData['error'])) {
        throw new Exception('Google authentication error: ' . $tokenData['error']);
    }

    $payload = $client->verifyIdToken($tokenData['id_token']);
    if (!$payload) {
        throw new Exception('Invalid ID token');
    }

    $email = $payload['email'];
    $name = $payload['name'] ?? 'User';
    $avatarUrl = $payload['picture'] ?? '';

    $avatar = downloadGoogleAvatar($avatarUrl);

    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare("SELECT id, username, image FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        if (empty($user['image']) && !empty($avatar)) {
            $updateStmt = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
            $updateStmt->execute([$avatar, $_SESSION['user_id']]);
        }
    } else {
        $password = bin2hex(random_bytes(16));
        $hashedpassword = password_hash($password, PASSWORD_DEFAULT);

        $insertStmt = $conn->prepare("INSERT INTO users (username, email, password, image) VALUES (?, ?, ?, ?)");
        $insertStmt->execute([$name, $email, $hashedpassword, $avatar]);

        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['username'] = $name;
    }

    session_regenerate_id(true);

    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

    $rememberStmt = $conn->prepare("INSERT INTO remember_me (user_id, selector, hashed_validator, expires) VALUES (?, ?, ?, ?)");
    $rememberStmt->execute([$_SESSION['user_id'], $selector, $hashedValidator, $expires]);

    setcookie(
        'remember_me',
        base64_encode($selector . ':' . $validator),
        time() + (30 * 24 * 60 * 60),
        '/',
        '',
        !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        true
    );

    header('Location: chat.php');
    exit;
} catch (Exception $e) {
    error_log('Google OAuth error: ' . $e->getMessage());
    die('Authentication failed. Please try again.');
}
