<?php
// Session already started in index.php, don't start again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// Handle Announcement Submission (ONLY content field)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announce_submit'])) {
    $content = $conn->real_escape_string($_POST['content'] ?? '');
    if ($content) {
        $sql = "INSERT INTO announcements (content) VALUES ('$content')";
        if ($conn->query($sql)) {
            header("Location: ".$_SERVER['PHP_SELF']."?announcement=1");
            exit;
        } else {
            $error_msg = "Failed to post announcement.";
        }
    } else {
        $error_msg = "Content is required.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alumni Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d'
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                        'pulse-red': 'pulseRed 2s infinite',
                        'float': 'float 3s ease-in-out infinite'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(40px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        bounceGentle: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' }
                        },
                        pulseRed: {
                            '0%, 100%': { boxShadow: '0 0 0 0 rgba(239, 68, 68, 0.4)' },
                            '50%': { boxShadow: '0 0 0 15px rgba(239, 68, 68, 0)' }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans pt-24 px-4 md:px-8 min-h-screen">

    <!-- Header/Navbar -->
    <header class="fixed top-0 left-0 w-full h-16 z-50 px-4 md:px-6 flex items-center bg-gradient-to-r from-primary-900 via-primary-700 to-primary-500 shadow-lg backdrop-blur-lg">
        <div class="max-w-7xl w-full mx-auto flex items-center justify-between">
            <!-- Logo Section -->
            <div class="flex items-center space-x-4">
                <div class="bg-white shadow w-10 h-10 rounded-full flex items-center justify-center text-xl text-primary-600 border-2 border-primary-100 hover:scale-105 transition-transform">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="hidden md:block">
                    <h1 class="bg-gradient-to-r from-primary-500 to-primary-700 bg-clip-text text-transparent text-xl font-bold drop-shadow">
                        <?php echo isset($_SESSION['system']['name']) ? $_SESSION['system']['name'] : 'Alumni Management System' ?>
                    </h1>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="bg-green-500 w-2 h-2 rounded-full inline-block animate-pulse"></span>
                        <span class="text-primary-50 text-xs font-medium">System Online</span>
                    </div>
                </div>
            </div>
            <!-- Quick Stats -->
            <div class="hidden lg:flex items-center space-x-4 text-white">
                <div class="flex items-center space-x-2 bg-white/10 rounded-full px-2 py-1 backdrop-blur-sm">
                    <i class="fas fa-users text-primary-200"></i>
                    <span class="text-xs font-medium">
                        <?php echo isset($conn) ? $conn->query("SELECT * FROM alumnus_bio where status = 1")->num_rows : '0' ?> Alumni
                    </span>
                </div>
                <div class="flex items-center space-x-2 bg-white/10 rounded-full px-2 py-1 backdrop-blur-sm">
                    <i class="fas fa-calendar text-primary-200"></i>
                    <span class="text-xs font-medium">
                        <?php echo isset($conn) ? $conn->query("SELECT * FROM events where date_format(schedule,'%Y-%m-%d') >= '".date('Y-m-d')."' ")->num_rows : '0' ?> Events
                    </span>
                </div>
            </div>
            <!-- User Section -->
            <div class="flex items-center space-x-4">
                <div class="hidden md:flex items-center space-x-3 px-4 py-2 bg-white bg-opacity-10 rounded-lg backdrop-blur">
                    <i class="fas fa-user-circle text-white text-2xl"></i>
                    <div class="text-left">
                        <p class="text-white text-sm font-semibold"><?php echo isset($_SESSION['login_name']) ? $_SESSION['login_name'] : 'Admin' ?></p>
                        <p class="text-primary-100 text-xs">Administrator</p>
                    </div>
                </div>
                <a href="ajax.php?action=logout" class="flex items-center gap-2 px-4 py-2 bg-white bg-opacity-10 hover:bg-opacity-20 rounded-lg text-white transition">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="hidden md:inline">Logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-64 bg-white shadow-xl border-r border-primary-100 z-40 flex flex-col px-4 py-8 space-y-6 overflow-y-auto">

        <div class="text-center pb-6 border-b border-primary-100">
            <h2 class="text-primary-600 text-xl font-bold">Dashboard</h2>
            <p class="text-gray-500 text-sm">Admin Panel</p>
        </div>
        <nav class="flex flex-col gap-2">
            <a href="dashboard.php" class="nav-item nav-home active flex items-center gap-3 px-4 py-3 rounded-lg text-primary-700 bg-gradient-to-r from-primary-100 to-primary-50 font-medium hover:bg-primary-200">
                <i class="fas fa-dashboard"></i> 
                <span>Dashboard</span>
            </a>
            <a href="gallery.php" class="nav-item nav-gallery flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100">
                <i class="fas fa-images"></i> 
                <span>Gallery</span>
            </a>
             <a href="courses.php" class="nav-item nav-courses flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100">
                <i class="fas fa-graduation-cap"></i> 
                <span>Course List</span>
            </a>
            <a href="alumni.php" class="nav-item nav-alumni flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100">
                <i class="fas fa-user-friends"></i> 
                <span>Alumni List</span>
            </a>
              <a href="jobs.php" class="nav-item nav-jobs flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100">
                <i class="fas fa-briefcase"></i> 
                <span>Jobs</span>
            </a>
            <a href="events.php" class="nav-item nav-events flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100">
                <i class="fas fa-calendar-alt"></i> 
                <span>Events</span>
            </a>
            <a href="forums.php" class="nav-item nav-forums flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100">
                <i class="fas fa-comments"></i> 
                <span>Forum</span>
            </a>
            <a href="users.php" class="nav-item nav-users flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100">
                <i class="fas fa-users-cog"></i> 
                <span>Users</span>
            </a>
            <a href="site_settings.php" class="nav-item nav-settings flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100">
                <i class="fas fa-cog"></i> 
                <span>Settings</span>
            </a>
        </nav>
    </aside>

    <main class="ml-0 md:ml-64 pt-8">
        <!-- Success Alert -->
        <?php if(isset($_GET['announcement']) && $_GET['announcement'] == '1'): ?>
        <div id="successAlert" class="fixed top-6 right-6 z-50 bg-green-500 text-white px-6 py-4 rounded-xl shadow-2xl max-w-sm animate-fade-in">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-2xl mr-3"></i>
                <div>
                    <p class="font-semibold">Success!</p>
                    <p class="text-sm opacity-90">Your announcement has been posted successfully.</p>
                </div>
                <button onclick="closeAlert()" class="ml-4 text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
        <div id="errorAlert" class="fixed top-6 right-6 z-50 bg-red-500 text-white px-6 py-4 rounded-xl shadow-2xl max-w-sm animate-fade-in">
            <div class="flex items-center">
                <i class="fas fa-times-circle text-2xl mr-3"></i>
                <div>
                    <p class="font-semibold">Error!</p>
                    <p class="text-sm opacity-90"><?= htmlspecialchars($error_msg) ?></p>
                </div>
                <button onclick="closeAlert()" class="ml-4 text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>


        <div class="container mx-auto px-6 py-8">
            <!-- Header Section -->
            <div class="mb-8 animate-fade-in">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-6 lg:mb-0">
                        <h1 class="text-4xl font-bold mb-2 flex items-center text-primary-700">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Admin Dashboard
                        </h1>
                        <p class="text-gray-600 text-lg">
                            <?php 
                            $display_name = isset($_SESSION['login_name']) && !empty($_SESSION['login_name']) 
                                ? $_SESSION['login_name'] 
                                : (isset($_SESSION['login_username']) ? $_SESSION['login_username'] : 'Admin');
                            echo "Welcome back, " . htmlspecialchars($display_name) . "!"; 
                            ?>
                        </p>
                    </div>
                    <div class="flex space-x-4">
                        <button
                            id="postAnnouncementBtn"
                            class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-red-500/25 animate-pulse-red">
                            <i class="fas fa-bullhorn mr-2"></i>
                            Post Announcement
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Announcement Form -->
            <div id="announcementModal" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50 hidden">
                <div class="bg-white max-w-xl w-full p-8 rounded-2xl shadow-xl border border-primary-100 animate-fade-in relative">
                    <button type="button"
                        onclick="closeAnnouncementModal()"
                        class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl focus:outline-none z-20">
                        <i class="fas fa-times"></i>
                    </button>
                    <h2 class="text-2xl font-bold text-primary-700 mb-4 flex items-center"><i class="fas fa-bullhorn mr-2"></i>Post Announcement</h2>
                    <form method="post" autocomplete="off">
                        <div class="mb-4">
                            <label class="block text-primary-700 font-semibold mb-2">Announcement Content</label>
                            <textarea name="content" required rows="5" class="w-full px-4 py-3 border rounded-lg focus:border-primary-500 focus:ring focus:ring-primary-100 transition" placeholder="Enter your announcement here..."></textarea>
                        </div>
                        <div class="flex justify-end gap-4 pt-2">
                            <button type="button" onclick="closeAnnouncementModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-semibold transition">Cancel</button>
                            <button type="submit" name="announce_submit" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-2 rounded-lg font-bold transition">Post</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <!-- Alumni Card -->
                <div class="bg-white rounded-2xl p-6 shadow-md animate-slide-up">
                    <div class="relative">
                        <div class="absolute top-0 right-0 text-red-500 text-2xl"><i class="fas fa-users"></i></div>
                        <div class="mb-4">
                            <div class="flex items-center mb-2">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                <p class="text-gray-600 font-medium uppercase text-sm tracking-wide">Alumni</p>
                            </div>
                            <h3 class="text-4xl font-bold text-gray-800">
                                <?php echo $conn->query("SELECT * FROM alumnus_bio where status = 1")->num_rows; ?>
                            </h3>
                        </div>
                        <div class="flex items-center text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>Active Members</span>
                        </div>
                    </div>
                </div>
                <!-- Forum Topics Card -->
                <div class="bg-white rounded-2xl p-6 shadow-md animate-slide-up">
                    <div class="relative">
                        <div class="absolute top-0 right-0 text-blue-500 text-2xl"><i class="fas fa-comments"></i></div>
                        <div class="mb-4">
                            <div class="flex items-center mb-2">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                <p class="text-gray-600 font-medium uppercase text-sm tracking-wide">Forum Topics</p>
                            </div>
                            <h3 class="text-4xl font-bold text-gray-800">
                                <?php echo $conn->query("SELECT * FROM forum_topics")->num_rows; ?>
                            </h3>
                        </div>
                        <div class="flex items-center text-sm text-blue-600">
                            <i class="fas fa-fire mr-1"></i>
                            <span>Discussions</span>
                        </div>
                    </div>
                </div>
                <!-- Posted Jobs Card -->
                <div class="bg-white rounded-2xl p-6 shadow-md animate-slide-up">
                    <div class="relative">
                        <div class="absolute top-0 right-0 text-yellow-500 text-2xl"><i class="fas fa-briefcase"></i></div>
                        <div class="mb-4">
                            <div class="flex items-center mb-2">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                <p class="text-gray-600 font-medium uppercase text-sm tracking-wide">Posted Jobs</p>
                            </div>
                            <h3 class="text-4xl font-bold text-gray-800">
                                <?php echo $conn->query("SELECT * FROM careers")->num_rows; ?>
                            </h3>
                        </div>
                        <div class="flex items-center text-sm text-yellow-600">
                            <i class="fas fa-chart-line mr-1"></i>
                            <span>Opportunities</span>
                        </div>
                    </div>
                </div>
                <!-- Upcoming Events Card -->
                <div class="bg-white rounded-2xl p-6 shadow-md animate-slide-up">
                    <div class="relative">
                        <div class="absolute top-0 right-0 text-purple-500 text-2xl"><i class="fas fa-calendar-day"></i></div>
                        <div class="mb-4">
                            <div class="flex items-center mb-2">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                                <p class="text-gray-600 font-medium uppercase text-sm tracking-wide">Upcoming Events</p>
                            </div>
                            <h3 class="text-4xl font-bold text-gray-800">
                                <?php echo $conn->query("SELECT * FROM events where date_format(schedule,'%Y-%m-%d') >= '".date('Y-m-d')."' ")->num_rows; ?>
                            </h3>
                        </div>
                        <div class="flex items-center text-sm text-purple-600">
                            <i class="fas fa-clock mr-1"></i>
                            <span>This Month</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Quick Stats Overview -->
                <div class="bg-white rounded-2xl p-8 shadow-md animate-slide-up">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-chart-bar text-red-500 mr-3"></i>
                        Quick Overview
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-red-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-user-graduate text-red-600"></i>
                                </div>
                                <span class="font-medium text-gray-700">New Alumni This Month</span>
                            </div>
                            <span class="text-2xl font-bold text-red-600">
                                <?php echo $conn->query("SELECT * FROM alumnus_bio WHERE status = 1 AND date_format(date_created,'%Y-%m') = '".date('Y-m')."'")->num_rows; ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-comment text-blue-600"></i>
                                </div>
                                <span class="font-medium text-gray-700">Active Forums</span>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">
                                <?php echo $conn->query("SELECT * FROM forum_topics WHERE date_format(date_created,'%Y-%m') = '".date('Y-m')."'")->num_rows; ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-briefcase text-green-600"></i>
                                </div>
                                <span class="font-medium text-gray-700">New Job Posts</span>
                            </div>
                            <span class="text-2xl font-bold text-green-600">
                                <?php echo $conn->query("SELECT * FROM careers WHERE date_format(date_created,'%Y-%m') = '".date('Y-m')."'")->num_rows; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-2xl p-8 shadow-md animate-slide-up">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-clock text-red-500 mr-3"></i>
                        Recent Activity
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-start p-4 border-l-4 border-red-500 bg-red-50 rounded-r-xl">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3 mt-1">
                                <i class="fas fa-plus text-red-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">New Alumni Registered</p>
                                <p class="text-sm text-gray-600">2 new members joined today</p>
                                <p class="text-xs text-gray-500 mt-1">2 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-start p-4 border-l-4 border-blue-500 bg-blue-50 rounded-r-xl">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                                <i class="fas fa-comment text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Forum Discussion</p>
                                <p class="text-sm text-gray-600">New topic in Career Advice</p>
                                <p class="text-xs text-gray-500 mt-1">4 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-start p-4 border-l-4 border-green-500 bg-green-50 rounded-r-xl">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-1">
                                <i class="fas fa-briefcase text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Job Posted</p>
                                <p class="text-sm text-gray-600">Software Engineer position added</p>
                                <p class="text-xs text-gray-500 mt-1">6 hours ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-slide-up">
                <button class="bg-white rounded-2xl p-6 hover:shadow-xl transition transform hover:scale-105 group">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:animate-bounce-gentle">
                            <i class="fas fa-users text-2xl text-white"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Manage Alumni</h3>
                        <p class="text-gray-600 text-sm">Add, edit, or view alumni profiles</p>
                    </div>
                </button>
                <button class="bg-white rounded-2xl p-6 hover:shadow-xl transition transform hover:scale-105 group">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:animate-bounce-gentle">
                            <i class="fas fa-calendar text-2xl text-white"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Event Management</h3>
                        <p class="text-gray-600 text-sm">Create and manage alumni events</p>
                    </div>
                </button>
                <button class="bg-white rounded-2xl p-6 hover:shadow-xl transition transform hover:scale-105 group">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:animate-bounce-gentle">
                            <i class="fas fa-chart-line text-2xl text-white"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Analytics</h3>
                        <p class="text-gray-600 text-sm">View detailed reports and statistics</p>
                    </div>
                </button>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
         // Show modal on button click
        document.getElementById('postAnnouncementBtn').addEventListener('click', function() {
            document.getElementById('announcementModal').classList.remove('hidden');
        });

        // Close modal function
        function closeAnnouncementModal() {
            document.getElementById('announcementModal').classList.add('hidden');
        }

        // Close modal when clicking outside the modal container
        document.getElementById('announcementModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAnnouncementModal();
            }
        });

        // Optional: ESC key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") {
                closeAnnouncementModal();
            }
        });


        // Dropdown toggle logic
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdown_menu');
            if (dropdown.classList.contains('opacity-0')) {
                dropdown.classList.remove('opacity-0', 'invisible');
                dropdown.classList.add('opacity-100', 'visible');
            } else {
                dropdown.classList.add('opacity-0', 'invisible');
                dropdown.classList.remove('opacity-100', 'visible');
            }
        }
        document.addEventListener('click', function(event){
            const btn = document.getElementById('account_settings');
            const menu = document.getElementById('dropdown_menu');
            if (!btn.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('opacity-0', 'invisible');
                menu.classList.remove('opacity-100', 'visible');
            }
        });
        function closeAlert() {
            const alert = document.getElementById('successAlert');
            if (alert) {
                alert.classList.add('opacity-0');
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        }
        setTimeout(() => { closeAlert(); }, 5000);
        document.getElementById('manage_my_account')?.addEventListener('click', function() {
            uni_modal("Manage Account", "manage_user.php?id=<?php echo isset($_SESSION['login_id']) ? $_SESSION['login_id'] : '1' ?>&mtype=own");
            toggleDropdown();
        });
        // Dummy modal
        function uni_modal(title, url) {
            alert(`Would open modal: ${title}`);
        }
    </script>
</body>
</html>