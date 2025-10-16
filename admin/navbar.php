<!-- Sidebar Navigation -->
<aside id="sidebar" class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-64 bg-white shadow-xl border-r border-red-100 z-40 flex flex-col px-4 py-8 space-y-6 overflow-y-auto">
    <div class="text-center pb-6 border-b border-red-100">
        <h2 class="text-red-600 text-xl font-bold">Admin Panel</h2>
        <p class="text-gray-500 text-sm">Navigation Menu</p>
    </div>
    
    <nav class="flex flex-col gap-2">
        <a href="index.php?page=dashboard" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-red-700 hover:bg-red-100 <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard' || $_GET['page'] == 'home') ? 'bg-gradient-to-r from-red-100 to-red-50 text-red-700 font-medium' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> 
            <span>Dashboard</span>
        </a>
        
        <a href="index.php?page=gallery" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-red-700 hover:bg-red-100 <?php echo (isset($_GET['page']) && $_GET['page'] == 'gallery') ? 'bg-gradient-to-r from-red-100 to-red-50 text-red-700 font-medium' : '' ?>">
            <i class="fas fa-images"></i> 
            <span>Gallery</span>
        </a>
        
        <a href="index.php?page=courses" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-red-700 hover:bg-red-100 <?php echo (isset($_GET['page']) && $_GET['page'] == 'courses') ? 'bg-gradient-to-r from-red-100 to-red-50 text-red-700 font-medium' : '' ?>">
            <i class="fas fa-graduation-cap"></i> 
            <span>Course List</span>
        </a>
        
        <a href="index.php?page=alumni" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-red-700 hover:bg-red-100 <?php echo (isset($_GET['page']) && $_GET['page'] == 'alumni') ? 'bg-gradient-to-r from-red-100 to-red-50 text-red-700 font-medium' : '' ?>">
            <i class="fas fa-user-friends"></i> 
            <span>Alumni List</span>
        </a>
        
        <a href="index.php?page=jobs" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-red-700 hover:bg-red-100 <?php echo (isset($_GET['page']) && $_GET['page'] == 'jobs') ? 'bg-gradient-to-r from-red-100 to-red-50 text-red-700 font-medium' : '' ?>">
            <i class="fas fa-briefcase"></i> 
            <span>Jobs</span>
        </a>
        
        <a href="index.php?page=events" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-red-700 hover:bg-red-100 <?php echo (isset($_GET['page']) && $_GET['page'] == 'events') ? 'bg-gradient-to-r from-red-100 to-red-50 text-red-700 font-medium' : '' ?>">
            <i class="fas fa-calendar-alt"></i> 
            <span>Events</span>
        </a>
        
        <a href="index.php?page=forums" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-red-700 hover:bg-red-100 <?php echo (isset($_GET['page']) && $_GET['page'] == 'forums') ? 'bg-gradient-to-r from-red-100 to-red-50 text-red-700 font-medium' : '' ?>">
            <i class="fas fa-comments"></i> 
            <span>Forums</span>
        </a>
        
        <a href="index.php?page=users" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-red-700 hover:bg-red-100 <?php echo (isset($_GET['page']) && $_GET['page'] == 'users') ? 'bg-gradient-to-r from-red-100 to-red-50 text-red-700 font-medium' : '' ?>">
            <i class="fas fa-users"></i> 
            <span>Users</span>
        </a>
        
        
        <a href="moderation.php" class="nav-item nav-moderation flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100">
            <i class="fas fa-check-circle"></i>
            <span>Moderation</span>
        </a>
    </nav>
</aside>

<!-- Main Content Wrapper with left margin for sidebar -->
<div class="ml-64 pt-16">
</div>

<style>
    /* Smooth scrollbar styling */
    #sidebar::-webkit-scrollbar {
        width: 6px;
    }
    #sidebar::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    #sidebar::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        border-radius: 3px;
    }
</style>

