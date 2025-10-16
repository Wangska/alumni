<!-- Header/Navbar -->
<header class="fixed top-0 left-0 w-full h-16 z-50 px-4 md:px-6 flex items-center bg-gradient-to-r from-red-900 via-red-700 to-red-500 shadow-lg backdrop-blur-lg">
    <div class="max-w-7xl w-full mx-auto flex items-center justify-between">
        <!-- Logo Section -->
        <div class="flex items-center space-x-4">
            <div class="bg-white shadow w-10 h-10 rounded-full flex items-center justify-center text-xl text-red-600 border-2 border-red-100 hover:scale-105 transition-transform">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="hidden md:block">
                <h1 class="text-white text-xl font-bold drop-shadow">
                    <?php echo isset($_SESSION['system']['name']) ? $_SESSION['system']['name'] : 'Alumni Management System' ?>
                </h1>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="bg-green-500 w-2 h-2 rounded-full inline-block animate-pulse"></span>
                    <span class="text-red-50 text-xs font-medium">System Online</span>
                </div>
            </div>
        </div>
        
        <!-- Right Section - User Info -->
        <div class="flex items-center space-x-4">
            <div class="hidden md:flex items-center space-x-3 px-4 py-2 bg-white bg-opacity-10 rounded-lg backdrop-blur">
                <i class="fas fa-user-circle text-white text-2xl"></i>
                <div class="text-left">
                    <p class="text-white text-sm font-semibold"><?php echo isset($_SESSION['login_name']) ? $_SESSION['login_name'] : 'Admin' ?></p>
                    <p class="text-red-100 text-xs">Administrator</p>
                </div>
            </div>
            <a href="ajax.php?action=logout" class="flex items-center gap-2 px-4 py-2 bg-white bg-opacity-10 hover:bg-opacity-20 rounded-lg text-white transition">
                <i class="fas fa-sign-out-alt"></i>
                <span class="hidden md:inline">Logout</span>
            </a>
        </div>
    </div>
</header>

<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>

