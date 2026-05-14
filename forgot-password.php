<?php
require_once 'includes/session.php';
require_once 'config/db.php';

$email = '';
$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        die('Invalid CSRF token');
    }

    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Valid email is required.';
    } else {
        try {
            $conn = Database::getInstance()->getConnection();
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
            
                $token = bin2hex(random_bytes(32));
                $expires = (new DateTime('+1 hour', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

                $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
                $updateStmt->execute([$token, $expires, $email]);

            
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $resetLink = $protocol . '://' . $host . '/chat-app/reset-password.php?token=' . $token;

                $message = "✅ A reset link has been generated.<br>
                            <a href=\"$resetLink\" class=\"text-blue-600 underline\">Click here to reset your password</a> 
                            (TESTING ONLY)";
            } else {
                
                $error = 'This email is not registered (testing message).';
                
            }
        } catch (PDOException $e) {
            error_log("forgot-password.php error: " . $e->getMessage());
            $error = 'A database error occurred. Please try again later.';
        }
    }
}


$pageTitle = 'Forgot Password - Chat App';
$bodyClass = 'bg-violet-500 min-h-screen flex items-center justify-center';
require_once 'includes/header.php';
?>

<div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
    <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">Forgot Password</h1>

    <?php if ($error): ?>
        <p class="text-red-500 text-center mb-3 font-semibold"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded-lg mb-4 text-center">
            <?= $message ?>
        </div>
    <?php else: ?>
        <p class="text-gray-600 text-center mb-4">Enter your email and we'll send you a reset link.</p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input
                type="email"
                name="email"
                placeholder="Your email address"
                value="<?= htmlspecialchars($email) ?>"
                class="w-full mb-4 p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-400"
                required
            >
            <button
                type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 transition text-white p-3 rounded-lg font-semibold"
            >
                Send Reset Link
            </button>
        </form>
    <?php endif; ?>

    <p class="text-center mt-4 text-sm text-gray-600">
        Remember your password?
        <a href="login.php" class="text-blue-600 font-semibold hover:underline">Login</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>