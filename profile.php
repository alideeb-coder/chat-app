<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'config/db.php';

$pageTitle = 'My Profile - ' . htmlspecialchars($_SESSION['username']);

try {
    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("SELECT username, email, image, last_seen FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("profile.php error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}

$imagePath = 'assets/images/default-avatar.png';
if (!empty($user['image']) && file_exists('uploads/avatars/' . $user['image'])) {
    $imagePath = 'uploads/avatars/' . $user['image'];
}

$bodyClass = 'min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-400';
require_once 'includes/header.php';
?>


<div class="w-full max-w-md mx-auto p-4">
    <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden transform transition-all hover:scale-[1.01]">
        <div class=" h-24 relative" style="background: linear-gradient(to right, #2563eb, #9333ea);">
            <a href="chat.php" class="absolute top-4 left-4 text-white/80 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                </a>
        </div>
        
        <div class="flex justify-center -mt-16">
            <div class="relative">
                <img src="<?= htmlspecialchars($imagePath) ?>" alt="Avatar" 
                     class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg ring-4 ring-blue-100">

                <span class="absolute bottom-1 right-1 w-5 h-5 bg-green-400 border-2 border-white rounded-full"></span>
            </div>
        </div>
        
        <div class="text-center px-8 pt-4 pb-2">
            <h1 class="text-2xl font-bold text-gray-800">
                <?= htmlspecialchars($user['username']) ?>
            </h1>
            <p class="text-gray-500 text-sm">
                <?= htmlspecialchars($user['email']) ?>
            </p>
            
            <?php if (!empty($user['last_seen'])): ?>
                <div class="flex items-center justify-center mt-2 text-xs text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Last seen <?= date('M j, Y \a\t g:i a', strtotime($user['last_seen'])) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex justify-center space-x-8 py-4 border-t border-gray-100 mx-8">
            <div class="text-center">
                <span class="block text-lg font-bold text-gray-700">—</span>
                <span class="text-xs text-gray-500">Messages</span>
            </div>
            <div class="text-center">
                <span class="block text-lg font-bold text-gray-700">—</span>
                <span class="text-xs text-gray-500">Friends</span>
            </div>
        </div>

        <form id="avatarForm" enctype="multipart/form-data" class="px-8 pb-8 pt-2">
            <label class="block mb-2 text-sm font-medium text-gray-600 text-center">
                Change Profile Picture
            </label>
            <div class="flex items-center gap-3">
                <input type="file" name="avatar" accept="image/*" 
                       class="flex-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0  file:text-sm file:font-semibold  transition-all
                       file:bg-blue-500 file:text-white  file:border-none file:cursor-pointer hover:file:bg-blue-600 duration-300">
                <button type="submit" 
                        class=" text-white mr-2 px-3 py-2 rounded-full font-medium transition-all shadow-md hover:shadow-lg hover:opacity-70 duration-300"
                        style="background: linear-gradient(to right, #3b82f6, #9333ea);">
                    Save
                </button>
            </div>
        </form>

    </div>
</div>

<script src="assets/js/profile.js"></script>
<?php require_once 'includes/footer.php'; ?>