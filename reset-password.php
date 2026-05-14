<?php
require_once 'includes/session.php';
require_once 'config/db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$userId = null;

if ($token === '') {
    $error = 'Invalid or missing token.';
} else {
    try {
        $conn = Database::getInstance()->getConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > UTC_TIMESTAMP()");
        $stmt->execute([$token]);
        $userId = $stmt->fetchColumn();

        if (!$userId) {
            $error = 'Invalid or expired token.';
        }
    } catch (PDOException $e) {
        error_log("reset-password.php error: " . $e->getMessage());
        $error = 'A database error occurred. Please try again later.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        die('Invalid CSRF token');
    }

    $submittedToken = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    if ($submittedToken !== $token) {
        $error = 'Invalid token.';
    } elseif ($password !== $cpassword) {
        $error = 'Passwords do not match.';
    } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W]).{7,}$/', $password)) {
        $error = 'Password must contain letters, number, special character and be at least 7 characters.';
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $conn = Database::getInstance()->getConnection();
            $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $userId]);

            $success = 'Password has been reset successfully. You can now <a href="login.php" class="text-blue-600 underline">login</a>.';
            $userId = null;
        } catch (PDOException $e) {
            error_log("reset-password.php error: " . $e->getMessage());
            $error = 'A database error occurred. Please try again later.';
        }
    }
}

$pageTitle = 'Reset Password - Chat App';
$bodyClass = 'bg-violet-500 min-h-screen flex items-center justify-center';
require_once 'includes/header.php';
?>
<script src="./assets/js/auth.js"></script>
<div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
    <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">Reset Your Password</h1>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded-lg mb-4 text-center">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded-lg mb-4 text-center">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if ($userId): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="relative w-full mb-4">
                <input
                    type="password"
                    name="password"
                    placeholder="New Password"
                    class="w-full mb-3 p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-400"
                    required>
                <button type="button" class="absolute right-3 top-4 -translate-1/2 toggle-password text-gray-500 hover:text-gray-700">
                    <span class="eye-open hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </span>
                    <span class="eye-closed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5z" />
                            <line x1="4" y1="4" x2="20" y2="20" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                </button>
            </div>
            <div class="relative w-full mb-4">
                <input
                    type="password"
                    name="cpassword"
                    placeholder="Confirm New Password"
                    class="w-full mb-4 p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-400"
                    required>
                <button type="button" class="absolute right-3 top-4 -translate-1/2 toggle-password text-gray-500 hover:text-gray-700">
                    <span class="eye-open hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </span>
                    <span class="eye-closed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5z" />
                            <line x1="4" y1="4" x2="20" y2="20" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                </button>
            </div>
            <button
                type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 transition text-white p-3 rounded-lg font-semibold">
                Reset Password
            </button>
        </form>
    <?php endif; ?>

    <p class="text-center mt-4 text-sm text-gray-600">
        <a href="login.php" class="text-blue-600 font-semibold hover:underline">Back to Login</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>