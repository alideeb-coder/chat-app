<?php
require_once 'includes/session.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

$userId = $_GET['user'] ?? '';
$conn = Database::getInstance()->getConnection();

try {
    $stmtCurrent = $conn->prepare("SELECT image FROM users WHERE id = ?");
    $stmtCurrent->execute([$_SESSION['user_id']]);
    $currentImage = $stmtCurrent->fetchColumn();

    $currentAvatar = 'assets/images/default-avatar.png';
    if (!empty($currentImage)) {
        if (filter_var($currentImage, FILTER_VALIDATE_URL)) {
            $currentAvatar = $currentImage;
        } elseif (file_exists('uploads/avatars/' . $currentImage)) {
            $currentAvatar = 'uploads/avatars/' . $currentImage;
        }
    }

    $stmt = $conn->prepare("
        SELECT id, username, last_seen, image
        FROM users
        WHERE id != ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("chat.php error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}

$pageTitle = 'Chat - ' . htmlspecialchars($_SESSION['username']);
$bodyClass = 'min-h-screen w-full';
include 'includes/header.php';
?>



<div class="max-w-6xl  mx-auto mt-2 mb-2 bg-white rounded-2xl shadow px-6 py-4 flex justify-between items-center">
    <div class="flex justify-between gap-3">
        <div class="flex  gap-3">
            
            <button id="menuBtn" type="button" class="md:hidden text-gray-600 hover:text-gray-800 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <a href="profile.php" class="flex items-center gap-2 hover:opacity-80 transition">
                <img src="<?= htmlspecialchars($currentAvatar) ?>" alt="My Avatar"
                    class="w-9 h-9 rounded-full object-cover border-2 border-blue-200 shadow-sm">
                <h1 class="text-xl font-bold text-gray-700">
                    Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
                </h1>
            </a>
        </div>
    </div>
    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl transition">
        Logout
    </a>
</div>
<div class=" flex max-w-6xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
    <div class="hidden md:block w-1/4 bg-white p-3 h-[85vh] border-r overflow-y-auto ">
        <input type="text" id="userSearch" placeholder="Search users..."
            class="w-full p-2 mb-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-400">
        <div id="userList">
            <?php foreach ($users as $user):
                $userAvatar = 'assets/images/default-avatar.png';
                if (!empty($user['image'])) {
                    if (filter_var($user['image'], FILTER_VALIDATE_URL)) {
                        $userAvatar = $user['image'];
                    } elseif (file_exists('uploads/avatars/' . $user['image'])) {
                        $userAvatar = 'uploads/avatars/' . $user['image'];
                    }
                }

                $lastSeen = !empty($user['last_seen']) ?
                    (new DateTime($user['last_seen'], new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z') : '';
            ?>
                <a href="?user=<?= $user['id'] ?>"
                    class="user-item p-3 mb-2 rounded-xl border transition duration-300 text-gray-700 hover:bg-blue-100 flex items-center gap-3 hover:text-black
               <?php if ($user['id'] == ($_GET['user'] ?? '')) echo '!bg-blue-700 !text-white'; ?>">
                    <div class="relative">
                        <img src="<?= htmlspecialchars($userAvatar) ?>" alt="Avatar"
                            class="w-8 h-8 rounded-full object-cover border border-gray-300 shrink-0 ">

                        <span class="status-do absolute left-5 w-2 h-2 rounded-full bg-gray-400 shrink-0 bottom-0.5"
                            data-last-seen="<?= $lastSeen ?>"
                            data-user-id="<?= $user['id'] ?>">
                        </span>

                    </div>
                    <span class="truncate"><?= htmlspecialchars($user['username']) ?></span>
                    <span class="unread-badge hidden ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full" data-user-id="<?= $user['id'] ?>"></span>
                </a>
            <?php endforeach; ?>

        </div>
    </div>
    
    <div id="sidebarOverlay" class="fixed inset-0 z-50 md:hidden pointer-events-none">
        
        <div id="sidebarBackdrop" class="absolute inset-0 bg-black opacity-0 transition-all duration-500"></div>
        
        <div id="sidebarPanel" class="absolute top-0 left-0 w-72 h-full bg-white shadow-xl transform -translate-x-full transition-transform duration-500 p-3 overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold text-gray-700">Users</h2>
                <button id="sidebarClose" type="button" class="text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <input type="text" id="mobileUserSearch" placeholder="Search users..."
                class="w-full p-2 mb-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-400">
            <div id="mobileUserList">
                
            </div>
        </div>
    </div>
    <div class="w-full md:w-3/4 p-4 relative bg-white">
        <div id="message"
            class="bg-gray-100 space-y-2 h-[68vh] overflow-y-auto p-6 rounded-xl">
        </div>

        <button id="scrollBtn"
            class="hidden absolute bottom-16 right-1/2 bg-blue-600 z-10
            text-white px-3 py-2 rounded-2xl transition-all duration-1000 hover:scale-110">
        </button>

        <?php if ($userId): ?>
            <form id="messageForm" class="p-4 border-t bg-white flex gap-2">
                <input
                    id="messageInput"
                    type="text"
                    required
                    class="flex-1 p-3 border rounded-full outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Type a message">
                <button
                    type="submit"
                    class="bg-blue-500 hover:bg-blue-600 transition text-white px-6 py-3 rounded-full shadow">
                    Send
                </button>
            </form>
        <?php else: ?>
            <div class="p-6 text-center text-gray-500">
                Select a user to start chatting
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const CURRENT_USER_ID = <?= $_SESSION['user_id'] ?>;
    const CHAT_USER_ID = "<?= $userId ?>";
</script>

<script src="assets/js/chat.js"></script>

<?php include 'includes/footer.php'; ?>