<?php
require_once 'includes/session.php';
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: chat.php');
    exit;
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        die('Invalid CSRF token');
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']);

    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if ($password !== $cpassword) {
        $errors[] = "Passwords do not match.";
    }
    if (!empty($password)) {
        if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W]).{7,}$/', $password)) {
            $errors[] = "Password must contain letters, number, special character and be at least 7 characters.";
        }
    }

    if (empty($errors)) {
        try {
            $conn = Database::getInstance()->getConnection();

            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $errors[] = "Email already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword]);

                $_SESSION['user_id'] = $conn->lastInsertId();
                $_SESSION['username'] = $username;
                session_regenerate_id(true);

                if (isset($_POST["remember_me"]) && $_POST["remember_me"] === '1') {
                    $selector = bin2hex(random_bytes(16));
                    $validator = bin2hex(random_bytes(32));
                    $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
                    $expires = date("Y-m-d H:i:s", strtotime("+30 days"));

                    $stmt = $conn->prepare("INSERT INTO remember_me (user_id, selector, hashed_validator, expires) VALUES (?,?,?,?)");
                    $stmt->execute([$_SESSION['user_id'], $selector, $hashedValidator, $expires]);

                    setcookie(
                        'remember_me',
                        base64_encode($selector . ':' . $validator),
                        time() + (30 * 24 * 60 * 60),
                        '/',
                        '',
                        !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                        true
                    );
                }

                header("Location: chat.php");
                exit;
            }
        } catch (PDOException $e) {
            error_log("register.php error: " . $e->getMessage());
            $errors[] = "A database error occurred. Please try again later.";
            die($e->getMessage());
        }
    }
}

$pageTitle = 'Register - Chat App';
$bodyClass = 'bg-violet-500 min-h-screen flex items-center justify-center';
require_once 'includes/header.php';
?>
<script src="./assets/js/auth.js"></script>
<form method="POST" class="bg-white p-8 rounded-2xl shadow-2xl w-100">
    <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">
        Register
    </h1>

    <?php foreach ($errors as $error): ?>
        <p class="text-red-500 text-center mb-3 font-semibold">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endforeach; ?>

    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <input
        type="text"
        name="username"
        placeholder="Username"
        value="<?= htmlspecialchars($username) ?>"
        class="w-full mb-3 p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-400"
        required
    >
    <input
        type="email"
        name="email"
        placeholder="Email"
        value="<?= htmlspecialchars($email) ?>"
        class="w-full mb-3 p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-400"
        required
    >
    <div class="relative w-full mb-4">
        <input
            type="password"
            name="password"
            placeholder="Password"
            class="w-full p-3 pr-12 border rounded-lg outline-none focus:ring-2 focus:ring-blue-400"
            required
        >
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
            placeholder="Confirm Password"
            class="w-full mb-4 p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-400"
            required
        >
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
    <div class="flex items-center mb-4">
        <input type="checkbox" name="remember_me" id="remember_me" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
        <label for="remember_me" class="ml-2 text-sm text-gray-700">Remember me for a month</label>
    </div>
    <button
        type="submit"
        class="w-full bg-blue-500 hover:bg-blue-600 transition text-white p-3 rounded-lg font-semibold"
    >
        Register
    </button>

    <p class="text-center mt-4 text-sm text-gray-600">
        Already have an account?
        <a href="login.php" class="text-blue-600 font-semibold hover:underline">
            Login
        </a>
    </p>
</form>

<?php require_once 'includes/footer.php'; ?>
