<?php
require_once 'includes/session.php';

if (isset($_SESSION['user_id'])) {
    header('Location: chat.php');
    exit;
}

$pageTitle = 'Welcome - Chat App';
$bodyClass = 'bg-violet-500 min-h-screen flex items-center justify-center';
require_once 'includes/header.php';
?>
<link rel="stylesheet" href="./assets/css/animation.css">
<?php if (isset($_GET['logout']) && $_GET['logout'] === '1'): ?>
<div id="logout-toast" 
     class="fixed top-6 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-sm 
            bg-white rounded-2xl shadow-2xl border border-gray-100 
            overflow-hidden animate-slideDown">
  <div class="flex items-center gap-4 p-5">
    <div class="shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
      <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M5 13l4 4L19 7" />
      </svg>
    </div>
    <div>
      <p class="text-lg font-semibold text-gray-800">Signed out</p>
      <p class="text-sm text-gray-500">Successfully</p>
    </div>
    <button onclick="document.getElementById('logout-toast').remove()" 
            class="ml-auto text-gray-400 hover:text-gray-600 transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
  <div class="h-1 bg-gray-200 w-full">
    <div class="h-full bg-green-500 animate-shrink"></div>
  </div>
</div>

<script>
  const toast = document.getElementById('logout-toast');
  if (toast) {
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.5s ease';
      setTimeout(() => toast.remove(), 500); 
    }, 3000);
  }
</script>
<?php endif; ?>
<div class="bg-white shadow-2xl rounded-2xl p-10 w-105 text-center">
    <h1 class="text-4xl font-bold text-blue-600 mb-4">
        Chat App
    </h1>
    
    <p class="text-gray-600 mb-8">
        Welcome to your real-time chat application
    </p>
    <div class="space-y-4">
        <a href="login.php"
           class="block w-full bg-blue-500 hover:bg-blue-600 transition text-white py-3 rounded-xl font-semibold">
            Login
        </a>
        <a href="register.php"
           class="block w-full bg-gray-200 hover:bg-gray-300 transition text-gray-800 py-3 rounded-xl font-semibold">
            Register
        </a>
    </div>
<div class="flex items-center my-4">
    <div class="flex-1 border-t border-gray-200"></div>
    <span class="px-3 text-gray-400 text-sm">or</span>
    <div class="flex-1 border-t border-gray-200"></div>
</div>

<a href="login-google.php"
   class=" w-full bg-white border border-gray-300 hover:bg-gray-50 transition text-gray-700 py-3 rounded-xl font-semibold flex items-center justify-center shadow-sm">
    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
    </svg>
    Login with Google
</a>
</div>

<?php require_once 'includes/footer.php'; ?>