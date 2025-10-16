<?php
session_start();
include 'admin/db_connect.php';

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection check
if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

// Load system settings into session
if(!isset($_SESSION['system'])){
    $system = $conn->query("SELECT * FROM system_settings LIMIT 1");
    if($system && $system->num_rows > 0){
        foreach($system->fetch_assoc() as $k => $v){
            $_SESSION['system'][$k] = $v;
        }
    }
}

// Always define as array to avoid undefined variable errors
$announcements = [];

// Fetch Announcements
$res_ann = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 3");
if ($res_ann) {
    while ($row = $res_ann->fetch_assoc()) {
        $announcements[] = $row;
    }
}

// Fetch ONLY 4 upcoming events for homepage
$events = [];
$res = $conn->query("SELECT * FROM events WHERE schedule >= NOW() ORDER BY schedule ASC LIMIT 4");
if ($res) {
    while($row = $res->fetch_assoc()) $events[] = $row;
}

// Count total upcoming events (for "View All" button logic)
$total_upcoming = 0;
$res_total = $conn->query("SELECT COUNT(*) AS total FROM events WHERE schedule >= NOW()");
if ($res_total) {
    $row_total = $res_total->fetch_assoc();
    $total_upcoming = $row_total['total'];
}

// If the user clicks "Commit", handle AJAX commit request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'commit_event') {
    if (!isset($_SESSION['login_id'])) {
        echo json_encode(['status'=>'error', 'msg'=>'You must be logged in to commit.']);
        exit;
    }
    $event_id = intval($_POST['event_id']);
    $user_id = intval($_SESSION['login_id']);

    // Check for duplicate commit
    $exists = $conn->query("SELECT * FROM event_commits WHERE event_id=$event_id AND user_id=$user_id");
    if ($exists && $exists->num_rows > 0) {
        echo json_encode(['status'=>'error', 'msg'=>'You have already committed to this event!']);
        exit;
    }

    // Try insert, check for errors
    if ($conn->query("INSERT INTO event_commits (event_id, user_id) VALUES ($event_id, $user_id)")) {
        echo json_encode(['status'=>'success', 'msg'=>'Successfully committed to event!']);
    } else {
        echo json_encode(['status'=>'error', 'msg'=>'Database error: '.$conn->error]);
    }
    exit;
}

// Helper function to get commit count and if user is committed
function event_commit_info($conn, $event_id, $user_id = null) {
    $row = ['count'=>0,'committed'=>false];
    $res = $conn->query("SELECT user_id FROM event_commits WHERE event_id=$event_id");
    $row['count'] = $res ? $res->num_rows : 0;
    if ($user_id) {
        $found = false;
        if ($res) {
            $res->data_seek(0);
            while ($r = $res->fetch_assoc()) {
                if ($r['user_id'] == $user_id) $found = true;
            }
        }
        $row['committed'] = $found;
    }
    return $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .navbar-scrolled { backdrop-filter: blur(10px); background: rgba(127, 29, 29, 0.95) !important; }
        .glass-effect { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(255, 255, 255, 0.95); border: 1px solid rgba(209, 213, 219, 0.3);}
        .glass-dark { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(127, 29, 29, 0.95); border: 1px solid rgba(185, 28, 28, 0.3);}
        .gradient-text { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;}
        .hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(239,68,68,0.15);}
        .announcement-card { background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(239,68,68,0.2);}
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(135deg,#ef4444,#b91c1c); border-radius: 3px; }
        .nav-link { position: relative; overflow: hidden;}
        .nav-link::before { content: ''; position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: linear-gradient(90deg,transparent,#fca5a5,transparent); transition: all 0.3s ease; transform: translateX(-50%);}
        .nav-link:hover::before { width: 100%; }
        .masthead-bg { background: linear-gradient(135deg,#7f1d1d 0%,#b91c1c 50%,#ef4444 100%); position: relative; overflow: hidden;}
        .masthead-bg::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="%23ffffff08" points="0,1000 1000,0 1000,1000"/></svg>'); z-index: 1;}
        .floating-element { animation: float 6s ease-in-out infinite;}
        @keyframes float { 0%,100%{ transform:translateY(0px);} 50%{transform:translateY(-20px);} }
        .social-icon { transition: all 0.3s ease; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);}
        .social-icon:hover { background: rgba(255,255,255,0.2); transform: translateY(-3px) scale(1.1);}
        .modal-footer { display: flex; justify-content: flex-end; gap: 1rem; }
        .alert { margin-bottom: 1rem; }

        /* Enhanced Feature Styles */
        .feature-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 100%);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(239,68,68,0.15);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(239,68,68,0.2);
            border-color: rgba(239,68,68,0.3);
        }
        .feature-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 8px 25px rgba(239,68,68,0.3);
            animation: iconPulse 2s infinite ease-in-out;
        }
        @keyframes iconPulse {
            0%, 100% { box-shadow: 0 8px 25px rgba(239,68,68,0.3); }
            50% { box-shadow: 0 12px 35px rgba(239,68,68,0.5); }
        }

        /* Statistics Counter Animation */
        .stats-card {
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.9) 0%, rgba(185, 28, 28, 0.8) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            position: relative;
            overflow: hidden;
        }
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #fca5a5, transparent);
            animation: shimmer 3s infinite;
        }
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* News Ticker */
        .news-ticker {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            overflow: hidden;
            position: relative;
            height: 50px;
            display: flex;
            align-items: center;
        }
        .ticker-content {
            display: flex;
            animation: scroll 30s linear infinite;
            white-space: nowrap;
        }
        .ticker-item {
            padding-right: 3rem;
            display: flex;
            align-items: center;
        }
        @keyframes scroll {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        /* Search Enhancement */
        .search-container {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(239,68,68,0.2);
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(239,68,68,0.1);
        }
        .search-container:focus-within {
            border-color: #ef4444;
            box-shadow: 0 12px 35px rgba(239,68,68,0.2);
            transform: translateY(-2px);
        }

        /* Quick Actions Floating Button */
        .quick-actions {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        .action-btn {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(239,68,68,0.4);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            transform: scale(0);
            animation: popIn 0.3s ease forwards;
        }
        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(239,68,68,0.6);
        }
        .action-btn.main {
            width: 70px;
            height: 70px;
            font-size: 1.5rem;
            animation-delay: 0.1s;
        }
        @keyframes popIn {
            to { transform: scale(1); }
        }

        /* Enhanced Modal Message */
        .modal-message {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .modal-message.show {
            opacity: 1;
            visibility: visible;
        }
        .message-container {
            background: white;
            border-radius: 25px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            transform: translateY(50px) scale(0.9);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        .modal-message.show .message-container {
            transform: translateY(0) scale(1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-red-50 via-rose-50 to-red-100 min-h-screen font-sans">

    <!-- Toast Notification -->
    <div id="alert_toast" class="fixed top-4 right-4 z-50 transform translate-x-full transition-transform duration-300 ease-in-out">
        <div class="glass-dark text-white px-6 py-4 rounded-lg shadow-xl">
            <div class="toast-body"></div>
        </div>
    </div>

    <!-- Navigation -->
    <header id="mainNav" class="fixed top-0 w-full z-40 py-4 transition-all duration-300 glass-dark shadow-lg backdrop-blur-lg">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center">
                <a href="./" class="text-2xl font-bold text-white hover:text-red-300 transition-colors duration-300">
                    Alumni Nexus
                </a>

                <button id="mobile-menu-btn" class="md:hidden text-white p-2 rounded-lg hover:bg-white/10 transition-colors">
                    <i id="menu-icon" class="fas fa-bars text-xl"></i>
                </button>
                <nav id="desktop-nav" class="hidden md:flex items-center space-x-8">
                    <a href="index.php?page=home" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="alumni_list.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center">
                        <i class="fas fa-users mr-2"></i>Alumni
                    </a>
                    <a href="gallery.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center">
                        <i class="fas fa-images mr-2"></i>Gallery
                    </a>
                    <div id="auth-links" class="flex items-center space-x-6">
                    <?php if(isset($_SESSION['login_username'])): ?>
                        <a href="careers.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center">
                            <i class="fas fa-briefcase mr-2"></i>Jobs
                        </a>
                        <a href="forum.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center">
                            <i class="fas fa-comments mr-2"></i>Forums
                        </a>
                        <div id="user-dropdown" class="relative">
                            <button id="account_settings" type="button" class="flex items-center space-x-2 text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300">
                                <i class="fas fa-user-circle text-xl"></i>
                                <span><?= htmlspecialchars($_SESSION['login_username']) ?></span>
                                <i class="fas fa-angle-down"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 glass-effect rounded-lg shadow-xl py-2 hidden" id="dropdown-menu">
                                <a href="admin/ajax.php?action=logout2" class="flex items-center px-4 py-2 text-gray-700 hover:bg-red-50 transition-colors duration-200">
                                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <button onclick="openLoginModal()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </button>
                    <?php endif; ?>
                    </div>
                </nav>
                <nav id="mobile-nav" class="md:hidden absolute top-full left-0 right-0 glass-dark rounded-b-2xl shadow-xl hidden">
                    <div class="px-6 py-4 space-y-4">
                        <a href="index.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center">
                            <i class="fas fa-home mr-3"></i>Home
                        </a>
                        <a href="alumni_list.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center">
                            <i class="fas fa-users mr-3"></i>Alumni
                        </a>
                        <a href="gallery.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center">
                            <i class="fas fa-images mr-3"></i>Gallery
                        </a>
                        <div class="pt-4 border-t border-gray-600">
                            <?php if(isset($_SESSION['login_username'])): ?>
                                <a href="careers.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center">
                                    <i class="fas fa-briefcase mr-3"></i>Jobs
                                </a>
                                <a href="forum.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center">
                                    <i class="fas fa-comments mr-3"></i>Forums
                                </a>
                                <button id="mobile-account_settings" type="button" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition-all duration-300 flex items-center justify-center">
                                    <i class="fas fa-user-circle mr-2"></i><?= htmlspecialchars($_SESSION['login_username']) ?>
                                    <i class="fas fa-angle-down ml-2"></i>
                                </button>
                                <a href="admin/ajax.php?action=logout2" class="block text-center mt-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg border border-gray-200 transition">Logout</a>
                            <?php else: ?>
                                <button onclick="openLoginModal()" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition-all duration-300 flex items-center justify-center">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero/Masthead Section -->
    <section class="masthead-bg min-h-screen flex items-center justify-center relative overflow-hidden"
        style="background: url('admin/assets/img/scc1.png') center center / cover no-repeat;">
        <!-- Floating Decorative Elements -->
        <div class="absolute inset-0 bg-gradient-to-r from-red-900/20 to-rose-900/20 z-10"></div>
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-red-500/10 rounded-full blur-3xl floating-element"></div>
        <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-rose-500/10 rounded-full blur-3xl floating-element"></div>
        <div class="absolute bottom-1/4 left-1/3 w-80 h-80 bg-red-800/10 rounded-full blur-3xl floating-element"></div>
        <div class="container mx-auto px-6 text-center relative z-20">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 animate-fade-in">
                    Welcome to
                    <span class="block gradient-text bg-gradient-to-r from-red-400 via-rose-400 to-red-400 bg-clip-text text-transparent">
                        Alumni Nexus
                    </span>
                </h1>
                <p class="text-xl md:text-2xl text-red-100 mb-8 animate-fade-in">
                    Connecting graduates, sharing opportunities, building futures together
                </p>
                <div class="search-container max-w-md mx-auto mb-8 p-2 animate-fade-in">
                    <div class="flex items-center">
                        <input type="text" placeholder="Search alumni, jobs, events..." class="flex-1 bg-transparent px-4 py-3 outline-none text-gray-700">
                        <button class="bg-gradient-to-r from-red-600 to-rose-600 text-white p-3 rounded-full hover:shadow-lg transition-all">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center animate-fade-in">
                    <button onclick="openRegisterModal()" class="bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-2xl hover:shadow-red-500/25">
                        <i class="fas fa-rocket mr-2"></i>Get Started
                    </button>
                    <a href="#about" class="glass-effect text-gray-700 px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-xl">
                        <i class="fas fa-play mr-2"></i>Learn More
                    </a>
                </div>
            </div>
        </div>
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white animate-bounce-subtle">
            <i class="fas fa-chevron-down text-2xl"></i>
        </div>
    </section>



    <!-- Features Section -->
    <section class="py-20 relative">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold gradient-text mb-4">Discover Amazing Features</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">Connect, grow, and succeed with our comprehensive alumni platform designed for modern professionals</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="feature-card rounded-2xl p-8 text-center hover-lift bg-white shadow-lg">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 bg-gradient-to-r from-red-500 to-rose-500 text-white shadow">
                        <i class="fas fa-network-wired text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Global Network</h3>
                    <p class="text-gray-600">Connect with alumni from around the world and expand your professional network</p>
                </div>
                <div class="feature-card rounded-2xl p-8 text-center hover-lift bg-white shadow-lg">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 bg-gradient-to-r from-red-500 to-rose-500 text-white shadow">
                        <i class="fas fa-rocket text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Career Growth</h3>
                    <p class="text-gray-600">Access exclusive job opportunities and career advancement resources</p>
                </div>
                <div class="feature-card rounded-2xl p-8 text-center hover-lift bg-white shadow-lg">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 bg-gradient-to-r from-red-500 to-rose-500 text-white shadow">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Lifelong Learning</h3>
                    <p class="text-gray-600">Continuous education programs and workshops for skill development</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-16 -mt-20 relative z-30">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
                <div class="stats-card rounded-2xl p-6 text-center hover-lift">
                    <div class="text-3xl font-bold text-white mb-2" data-count="15000">0</div>
                    <div class="text-red-200 text-sm">Alumni Members</div>
                </div>
                <div class="stats-card rounded-2xl p-6 text-center hover-lift">
                    <div class="text-3xl font-bold text-white mb-2" data-count="2500">0</div>
                    <div class="text-red-200 text-sm">Job Placements</div>
                </div>
                <div class="stats-card rounded-2xl p-6 text-center hover-lift">
                    <div class="text-3xl font-bold text-white mb-2" data-count="180">0</div>
                    <div class="text-red-200 text-sm">Countries</div>
                </div>
                <div class="stats-card rounded-2xl p-6 text-center hover-lift">
                    <div class="text-3xl font-bold text-white mb-2" data-count="98">0</div>
                    <div class="text-red-200 text-sm">Success Rate %</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Features Section -->
    <section class="py-20 relative">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold gradient-text mb-4">Discover Amazing Features</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">Connect, grow, and succeed with our comprehensive alumni platform designed for modern professionals</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="feature-card rounded-2xl p-8 text-center hover-lift">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 text-white">
                        <i class="fas fa-network-wired text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Global Network</h3>
                    <p class="text-gray-600">Connect with alumni from around the world and expand your professional network</p>
                </div>
                
                <div class="feature-card rounded-2xl p-8 text-center hover-lift">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 text-white">
                        <i class="fas fa-rocket text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Career Growth</h3>
                    <p class="text-gray-600">Access exclusive job opportunities and career advancement resources</p>
                </div>
                
                <div class="feature-card rounded-2xl p-8 text-center hover-lift">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 text-white">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Lifelong Learning</h3>
                    <p class="text-gray-600">Continuous education programs and workshops for skill development</p>
                </div>
            </div>
        </div>
    </section>

<!-- Enhanced Announcements Section -->
<section class="py-20 relative bg-gradient-to-br from-red-50 via-rose-50 to-pink-50 overflow-hidden">
  <!-- Background Decorative Elements -->
  <div class="absolute inset-0 opacity-30">
    <div class="absolute top-10 left-10 w-32 h-32 bg-red-200 rounded-full blur-3xl"></div>
    <div class="absolute top-1/3 right-20 w-48 h-48 bg-rose-200 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 left-1/4 w-40 h-40 bg-pink-200 rounded-full blur-3xl"></div>
  </div>
  
  <div class="container mx-auto px-6 relative z-10">
    <!-- Section Header -->
    <div class="text-center mb-16">
      <div class="flex items-center justify-center gap-4 mb-6">
        <div class="w-16 h-16 bg-gradient-to-r from-red-600 to-rose-600 rounded-3xl flex items-center justify-center shadow-lg shadow-red-200">
          <i class="fas fa-bullhorn text-white text-2xl"></i>
        </div>
        <div class="text-left">
          <h2 class="text-4xl font-bold bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 bg-clip-text text-transparent">
            Latest Announcements
          </h2>
          <p class="text-red-700 text-lg mt-1">Stay updated with our latest news and updates</p>
        </div>
      </div>
    </div>

    <div class="max-w-5xl mx-auto">
      <?php if(count($announcements) === 0): ?>
        <!-- Enhanced Empty State -->
        <div class="announcement-card bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-red-100 overflow-hidden transform hover:scale-[1.02] transition-all duration-500 group">
          <div class="p-12 text-center">
            <div class="relative mb-8">
              <div class="w-24 h-24 bg-gradient-to-r from-red-100 to-rose-100 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-bullhorn text-4xl text-red-400 group-hover:text-red-500 transition-colors duration-300"></i>
              </div>
              <div class="absolute -top-2 -right-2 w-6 h-6 bg-red-400 rounded-full animate-pulse"></div>
            </div>
            <h3 class="text-2xl font-bold text-red-800 mb-4">No Announcements Available</h3>
            <p class="text-red-600 text-lg mb-6 max-w-md mx-auto">
              We'll post important updates and news here. Check back soon for the latest announcements!
            </p>
            <button class="bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white px-8 py-3 rounded-2xl font-semibold shadow-lg shadow-red-200 hover:shadow-xl hover:shadow-red-300 transition-all duration-300 transform hover:scale-105">
              <i class="fas fa-bell mr-2"></i>
              Get Notified
            </button>
          </div>
        </div>
      <?php endif; ?>

      <?php foreach ($announcements as $index => $a): ?>
      <div class="announcement-card bg-white/90 backdrop-blur-sm rounded-3xl shadow-xl border border-red-100 mb-8 overflow-hidden transform hover:scale-[1.02] transition-all duration-500 group hover:shadow-2xl hover:border-red-200">
        
        <!-- Announcement Header -->
        <div class="bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 p-8 relative overflow-hidden">
          <!-- Background Pattern -->
          <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 right-0 w-32 h-32 bg-red-400 rounded-full translate-x-12 -translate-y-12"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-rose-300 rounded-full -translate-x-8 translate-y-8"></div>
            <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-pink-400 rounded-full -translate-y-8 opacity-60"></div>
          </div>
          
          <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-5">
              <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-bullhorn text-white text-2xl"></i>
              </div>
              <div>
                <h3 class="text-3xl font-bold text-white mb-2">Official Announcement</h3>
                <div class="flex items-center gap-4 text-red-100">
                  <div class="flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="font-medium">Posted on: <?php echo date('F j, Y', strtotime($a['date_posted'])) ?></span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class="fas fa-clock"></i>
                    <span class="font-medium"><?php echo date('g:i A', strtotime($a['date_posted'])) ?></span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Priority Badge -->
            <div class="bg-white/20 backdrop-blur-sm border border-white/30 px-4 py-2 rounded-2xl">
              <div class="flex items-center gap-2 text-white font-semibold">
                <i class="fas fa-star text-yellow-300"></i>
                <span>Important</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Announcement Content -->
        <div class="p-8">
          <!-- Content Text -->
          <div class="mb-8">
            <div class="bg-red-50/50 backdrop-blur-sm rounded-2xl p-6 border border-red-100 relative">
              <div class="absolute top-4 right-4">
                <i class="fas fa-quote-right text-red-200 text-2xl"></i>
              </div>
              <p class="text-red-900 text-lg leading-relaxed font-medium relative z-10">
                <?php echo nl2br(htmlspecialchars($a['content'])) ?>
              </p>
            </div>
          </div>

          <!-- Announcement Footer -->
          <div class="flex items-center justify-between flex-wrap gap-4">
            <!-- Left Side - Category & Stats -->
            <div class="flex items-center gap-4 flex-wrap">
              <div class="bg-gradient-to-r from-red-500 to-rose-500 text-white px-5 py-2 rounded-2xl font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-tag mr-2"></i>
                <?php echo htmlspecialchars($a['category'] ?? 'Announcement') ?>
              </div>
              
              <!-- Engagement Stats -->
              
            </div>
            
          </div>


        </div>

        <!-- Progress Bar (for visual appeal) -->
        <div class="h-1 bg-gradient-to-r from-red-500 via-rose-500 to-pink-500"></div>
      </div>
      <?php endforeach; ?>

      
      </div>
    </div>
  </div>
</section>

<!-- Custom Styles -->
<style>
  /* Hover lift effect */
  .hover-lift {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  .hover-lift:hover {
    transform: translateY(-8px) scale(1.02);
  }
  
  /* Gradient text effect */
  .gradient-text {
    background: linear-gradient(135deg, #dc2626, #e11d48, #ec4899);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  /* Custom animations */
  @keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
  }
  
  .announcement-card:nth-child(odd) .fas.fa-bullhorn {
    animation: float 6s ease-in-out infinite;
  }
  
  .announcement-card:nth-child(even) .fas.fa-bullhorn {
    animation: float 6s ease-in-out infinite 3s;
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .announcement-card {
      margin-bottom: 1.5rem;
    }
    
    .flex-wrap {
      flex-direction: column;
      align-items: flex-start !important;
    }
    
    .gap-4 {
      gap: 0.75rem;
    }
  }
  
  /* Smooth scrolling effect */
  html {
    scroll-behavior: smooth;
  }
  
  /* Custom blur effects */
  .backdrop-blur-sm {
    backdrop-filter: blur(8px);
  }
</style>

<!-- Upcoming Events Section -->
<section class="py-16 relative min-h-screen">
  <div class="container mx-auto px-6">
    <div class="max-w-5xl mx-auto">
      <h2 class="text-4xl font-bold bg-gradient-to-r from-red-600 via-red-700 to-rose-600 inline-block text-transparent bg-clip-text mb-10 text-center">Upcoming Events</h2>
      <div class="grid md:grid-cols-2 gap-8">
        <?php if(count($events) === 0): ?>
          <div class="col-span-2 text-center py-8 text-gray-400">
            <div class="w-20 h-20 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-calendar-times text-red-400 text-3xl"></i>
            </div>
            <span class="block text-lg font-semibold">No upcoming events.</span>
          </div>
        <?php endif; ?>
        <?php foreach ($events as $e): 
          $commit_info = event_commit_info($conn, $e['id'], $_SESSION['login_id'] ?? null);
        ?>
          <div class="rounded-3xl bg-white/90 backdrop-blur-lg shadow-2xl border border-red-100 p-8 hover:scale-[1.02] transition-transform duration-300 flex flex-col overflow-hidden mb-4 feature-card relative">
            <!-- Decorative Circles -->
            <div class="absolute top-0 right-0 w-24 h-24 bg-rose-100 rounded-full translate-x-8 -translate-y-8 opacity-30 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-16 h-16 bg-red-100 rounded-full -translate-x-6 translate-y-6 opacity-20 pointer-events-none"></div>
            <?php if(!empty($e['banner'])): ?>
              <img src="admin/assets/uploads/<?php echo htmlspecialchars($e['banner']); ?>"
                   alt="Banner for <?php echo htmlspecialchars($e['title']); ?>"
                   class="event-banner mb-6 rounded-2xl border-2 border-red-200 shadow-lg w-full max-h-48 object-cover">
            <?php endif; ?>
            <div class="flex items-center mb-4">
              <div class="bg-gradient-to-r from-red-600 to-rose-600 text-white p-3 rounded-full mr-4 shadow-lg">
                <i class="fas fa-calendar-alt text-xl"></i>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($e['title']) ?></h3>
                <p class="text-gray-600 font-medium">Scheduled: <?php echo date('F j, Y', strtotime($e['schedule'])) ?> at <?php echo date('g:i A', strtotime($e['schedule'])) ?></p>
              </div>
            </div>
            <p class="text-red-900/80 text-base leading-relaxed mb-4 bg-white/60 rounded-xl p-4 border border-red-100">
              <?php echo nl2br(htmlspecialchars($e['content'])) ?>
            </p>
            <?php if(!empty($e['venue'])): ?>
              <div class="text-sm text-red-900 mt-2 font-semibold flex items-center">
                <i class="fas fa-map-marker-alt mr-2 text-red-600"></i><?php echo htmlspecialchars($e['venue']) ?>
              </div>
            <?php endif; ?>
            <?php if(!empty($e['link'])): ?>
              <div class="mt-4">
                <a href="<?php echo htmlspecialchars($e['link']) ?>" target="_blank" class="text-red-600 font-medium hover:text-red-800 transition-colors inline-flex items-center">
                  More Info <i class="fas fa-arrow-right ml-1"></i>
                </a>
              </div>
            <?php endif; ?>

            <!-- Commit Button and Info -->
            <div class="mt-8 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="bg-gradient-to-r from-red-200 to-rose-200 text-red-700 px-3 py-1 rounded-full font-semibold text-sm shadow">
                  <i class="fas fa-user-check mr-2"></i>
                  <?php echo $commit_info['count']; ?> committed
                </span>
              </div>
              <?php if(isset($_SESSION['login_id'])): ?>
                <?php if($commit_info['committed']): ?>
                  <button class="bg-green-600 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-green-700 transition" disabled>
                    <i class="fas fa-check mr-2"></i>Committed
                  </button>
                <?php else: ?>
                  <button class="bg-gradient-to-r from-red-600 to-rose-600 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-red-700 transition commit-btn"
                    data-event-id="<?php echo $e['id']; ?>">
                    <i class="fas fa-user-plus mr-2"></i>Commit to Event
                  </button>
                <?php endif; ?>
              <?php else: ?>
                <button onclick="openLoginModal()" class="bg-gradient-to-r from-red-400 to-rose-400 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-red-600 transition">
                  <i class="fas fa-sign-in-alt mr-2"></i>Login to Commit
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if($total_upcoming > 4): ?>
        <div class="flex justify-center mt-10">
          <a href="events.php" class="bg-gradient-to-r from-red-600 to-rose-600 text-white px-8 py-3 rounded-full shadow-lg font-semibold text-lg hover:from-rose-700 hover:to-red-800 transition">
            View All Events
            <i class="fas fa-arrow-right ml-2"></i>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

 <!-- Enhanced Footer (Improved with Another Contact Info) -->
<footer class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-gray-900/30 to-gray-800/30"></div>
    
    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-5xl mx-auto text-center">
            <!-- Heading -->
            <h2 class="text-4xl font-extrabold tracking-tight mb-6">Stay Connected</h2>
            <div class="w-20 h-1 bg-gradient-to-r from-gray-400 to-gray-500 mx-auto mb-12 rounded-full"></div>
            
            <!-- Contact Info -->
            <div class="grid md:grid-cols-3 gap-10 mb-12 text-center md:text-left">
                <!-- Call Us -->
                <div class="flex flex-col md:flex-row items-center md:items-start gap-4">
                    <i class="fas fa-phone-alt text-3xl text-gray-300"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Call Us</h3>
                        <p class="text-gray-400">+63 67 437 1023</p>
                    </div>
                </div>

                <!-- Email Us -->
                <div class="flex flex-col md:flex-row items-center md:items-start gap-4">
                    <i class="fas fa-envelope text-3xl text-gray-300"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Email Us</h3>
                        <a href="#" onclick="window.open('https://mail.google.com/mail/?view=cm&fs=1&to=scc.alumni@gmail.com','_blank')" class="text-gray-400 hover:text-white transition-colors duration-200">
                            scc.alumni@gmail.com
                        </a>
                    </div>
                </div>

                <!-- Visit Us -->
                <div class="flex flex-col md:flex-row items-center md:items-start gap-4">
                    <i class="fas fa-map-marker-alt text-3xl text-gray-300"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Visit Us</h3>
                        <p class="text-gray-400">Pob. Ward II ,<br>Minglanilla, Cebu</p>
                    </div>
                </div>
            </div>

            <!-- Social Links -->
            <div class="flex justify-center space-x-6 mb-10">
                <a href="#" class="w-12 h-12 border border-gray-600 rounded-full flex items-center justify-center hover:bg-gray-700 hover:border-gray-500 hover:shadow-lg transition-all duration-300">
                    <i class="fab fa-facebook-f text-xl"></i>
                </a>
                <a href="#" class="w-12 h-12 border border-gray-600 rounded-full flex items-center justify-center hover:bg-gray-700 hover:border-gray-500 hover:shadow-lg transition-all duration-300">
                    <i class="fab fa-twitter text-xl"></i>
                </a>
                <a href="#" class="w-12 h-12 border border-gray-600 rounded-full flex items-center justify-center hover:bg-gray-700 hover:border-gray-500 hover:shadow-lg transition-all duration-300">
                    <i class="fab fa-instagram text-xl"></i>
                </a>
                <a href="#" class="w-12 h-12 border border-gray-600 rounded-full flex items-center justify-center hover:bg-gray-700 hover:border-gray-500 hover:shadow-lg transition-all duration-300">
                    <i class="fab fa-linkedin-in text-xl"></i>
                </a>
            </div>
            
            <!-- Copyright -->
            <div class="border-t border-gray-700 pt-6">
                <p class="text-sm text-gray-400">
                    &copy; 2025 Alumni Management System. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>




    <!-- Quick Actions Floating Buttons -->
    <div class="quick-actions">
        <div class="action-btn" style="animation-delay: 0.2s;" title="Back to Top" id="backToTopBtn">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="action-btn" style="animation-delay: 0.3s;" title="Contact Support" id="contactSupportBtn">
            <i class="fas fa-headset"></i>
        </div>
        <div class="action-btn" style="animation-delay: 0.4s;" title="Share Page" id="sharePageBtn">
            <i class="fas fa-share-alt"></i>
        </div>
        <div class="action-btn main" style="animation-delay: 0.1s;" id="toggleQuickActionsBtn">
            <i class="fas fa-plus" id="quick-actions-icon"></i>
        </div>
    </div>


    <!-- Enhanced Modal Message System -->
    <div id="modalMessage" class="modal-message">
        <div class="message-container">
            <div class="p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" id="message-icon-container">
                    <i id="message-icon" class="text-4xl"></i>
                </div>
                <h3 id="message-title" class="text-2xl font-bold mb-4"></h3>
                <p id="message-text" class="text-gray-600 mb-8"></p>
                <button onclick="hideMessage()" class="bg-gradient-to-r from-red-600 to-rose-600 text-white px-8 py-3 rounded-full font-semibold hover:shadow-lg transition-all duration-300">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Support Modal -->
    <div id="supportModal" class="modal-message">
        <div class="message-container max-w-lg">
            <div class="p-8">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-blue-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-headset text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">Need Help?</h3>
                    <p class="text-gray-600">We're here to assist you</p>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                        <i class="fas fa-phone text-blue-600 mr-4 text-xl"></i>
                        <div>
                            <div class="font-semibold">Call Support</div>
                            <div class="text-sm text-gray-600">+1 (555) 123-4567</div>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                        <i class="fas fa-envelope text-green-600 mr-4 text-xl"></i>
                        <div>
                            <div class="font-semibold">Email Support</div>
                            <div class="text-sm text-gray-600">support@alumni.edu</div>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                        <i class="fas fa-comments text-purple-600 mr-4 text-xl"></i>
                        <div>
                            <div class="font-semibold">Live Chat</div>
                            <div class="text-sm text-gray-600">Available 24/7</div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-6">
                    <button onclick="hideSupportModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
         $(document).on('click', '.commit-btn', function(e){
            e.preventDefault();
            var btn = $(this);
            var event_id = btn.data('event-id');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Committing...');
            $.post('events.php', {action: 'commit_event', event_id: event_id}, function(res){
                var data = {};
                try { data = JSON.parse(res); } catch(e){}
                if(data.status && data.status === 'success'){
                btn.removeClass('bg-gradient-to-r from-red-600 to-rose-600')
                    .addClass('bg-green-600')
                    .html('<i class="fas fa-check mr-2"></i>Committed');
                btn.prop('disabled', true);
                // Optionally, you can reload the page or re-fetch the events via AJAX to update the commit count.
                // location.reload();
                }else{
                    alert(data.msg || res || 'Error committing to event.');
                }
            });
            });


        $(document).ready(function() {
            // Desktop dropdown toggle
            $('#account_settings').on('click', function(e) {
                e.stopPropagation();
                $('#dropdown-menu').toggleClass('hidden');
            });

            // Hide dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#user-dropdown').length) {
                    $('#dropdown-menu').addClass('hidden');
                }
            });

            // Mobile dropdown toggle (if you want to expand for mobile in future)
            $('#mobile-account_settings').on('click', function(e) {
                e.stopPropagation();
                // Here you can add mobile dropdown logic if needed
            });
        });



        $(document).ready(function() {
            // Toggle visibility of quick action buttons (except main)
            $('#toggleQuickActionsBtn').on('click', function() {
                $('.quick-actions .action-btn').not('.main').toggleClass('hidden');
                $('#quick-actions-icon').toggleClass('fa-plus fa-times');
            });

            // Back to Top
            $('#backToTopBtn').on('click', function() {
                $('html, body').animate({ scrollTop: 0 }, 600);
            });

            // Contact Support
            $('#contactSupportBtn').on('click', function() {
                $('#supportModal').addClass('show');
            });

            // Close support modal
            window.hideSupportModal = function() {
                $('#supportModal').removeClass('show');
            };

            // Share Page
            $('#sharePageBtn').on('click', function() {
                if (navigator.share) {
                    navigator.share({
                        title: document.title,
                        url: window.location.href
                    });
                } else {
                    // Fallback: copy URL to clipboard
                    var tempInput = document.createElement('input');
                    tempInput.value = window.location.href;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    alert('Page URL copied to clipboard!');
                }
            });
        });

        // Login Modal Functions
        function openLoginModal() {
            document.getElementById('loginModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function openRegisterModal() {
            closeLoginModal();
            document.getElementById('registerModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeRegisterModal() {
            document.getElementById('registerModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function switchToLogin() {
            closeRegisterModal();
            openLoginModal();
        }

        // Close modals on background click
        document.addEventListener('click', function(e) {
            if (e.target.id === 'loginModal') closeLoginModal();
            if (e.target.id === 'registerModal') closeRegisterModal();
        });

        // Handle ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLoginModal();
                closeRegisterModal();
            }
        });

        // Auto-open modals based on URL parameters (for backwards compatibility)
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            
            if (error === '1' || error === 'unverified') {
                // Open login modal and show appropriate error
                openLoginModal();
                if (error === 'unverified') {
                    document.getElementById('unverifiedError').classList.remove('hidden');
                } else {
                    document.getElementById('loginError').classList.remove('hidden');
                }
                
                // Clean URL without reloading
                const cleanUrl = window.location.origin + window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });
        </script>

    <!-- Login Modal -->
    <div id="loginModal" class="modal-overlay">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button onclick="closeLoginModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-600 transition-colors z-10">
                <i class="fas fa-times text-2xl"></i>
            </button>
            
            <div class="text-center mb-6">
                <span class="inline-block bg-gradient-to-br from-red-600 via-rose-600 to-pink-500 p-3 rounded-full mb-3 shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M16 21v-2a4 4 0 0 0-8 0v2"/>
                    </svg>
                </span>
                <h2 class="text-3xl font-bold text-red-700 mb-2">Alumni Network</h2>
                <p class="text-rose-500 font-medium">Sign in to your account</p>
            </div>

            <div id="loginError" class="hidden mb-4 bg-red-50 border border-red-300 text-red-700 font-semibold rounded-lg px-4 py-3 text-center shadow">
                Incorrect username or password.
            </div>

            <div id="unverifiedError" class="hidden mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 font-semibold rounded-lg px-4 py-3 shadow">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold">Account Not Verified</p>
                        <p class="text-sm mt-1">Your account is still awaiting admin approval. Please check back later or contact the administrator.</p>
                    </div>
                </div>
            </div>

            <form id="loginForm" class="space-y-5">
                <div>
                    <label for="login_username" class="block text-red-700 font-semibold mb-2">Username / Email</label>
                    <input type="text" id="login_username" name="username" required
                        class="w-full px-4 py-3 rounded-lg border border-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 bg-red-50 text-gray-800 transition duration-200 shadow-sm" 
                        placeholder="Enter your username or email">
                </div>
                <div>
                    <label for="login_password" class="block text-red-700 font-semibold mb-2">Password</label>
                    <input type="password" id="login_password" name="password" required
                        class="w-full px-4 py-3 rounded-lg border border-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 bg-red-50 text-gray-800 transition duration-200 shadow-sm" 
                        placeholder="Enter your password">
                </div>
                <button type="submit"
                    class="w-full bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold py-3 rounded-lg shadow-lg hover:from-red-700 hover:to-rose-700 transition-all duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
            </form>

            <div class="mt-6 text-center">
                <span class="text-red-500">Don't have an account?</span>
                <button onclick="openRegisterModal()" class="ml-2 text-red-700 font-bold hover:text-rose-800 underline transition-all">Create Account</button>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal-overlay">
        <div class="modal-content modal-content-large" onclick="event.stopPropagation()">
            <button onclick="closeRegisterModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-600 transition-colors z-10">
                <i class="fas fa-times text-2xl"></i>
            </button>
            
            <div class="text-center mb-6">
                <span class="inline-block bg-gradient-to-r from-red-600 to-rose-600 p-3 rounded-full mb-3 shadow-lg">
                    <i class="fas fa-user-plus text-white text-3xl"></i>
                </span>
                <h2 class="text-3xl font-bold text-red-700 mb-2">Create New Account</h2>
                <p class="text-rose-500 font-medium">Join the alumni network</p>
            </div>

            <div id="registerSuccess" class="hidden mb-4 bg-blue-50 border border-blue-300 text-blue-700 font-semibold rounded-lg px-4 py-3 shadow">
                <p class="font-bold mb-1">Registration Submitted!</p>
                <p class="text-sm">Your account is awaiting admin approval. You'll be able to login once verified.</p>
            </div>

            <form id="registerForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 text-sm">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="firstname" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 text-sm">Middle Name</label>
                        <input type="text" name="middlename" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 text-sm">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="lastname" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 text-sm">Gender <span class="text-red-500">*</span></label>
                        <select name="gender" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-white">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 text-sm">Batch / School Year <span class="text-red-500">*</span></label>
                        <select name="batch" id="batch_year" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-white">
                            <option value="">-- Select Batch Year --</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Select your graduation year</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 text-sm">Course <span class="text-red-500">*</span></label>
                        <select name="course_id" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-white">
                            <option value="">-- Choose Course --</option>
                            <?php 
                            $courses_query = $conn->query("SELECT id, course FROM courses ORDER BY course ASC");
                            while($course = $courses_query->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 text-sm">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 text-sm">Mobile <span class="text-red-500">*</span></label>
                        <input type="tel" name="mobile" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Address <span class="text-red-500">*</span></label>
                    <textarea name="address" rows="2" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50"></textarea>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 text-sm">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="reg_password" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                        <p class="text-xs text-gray-500 mt-1">Min 8 chars, 1 uppercase, 1 number</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 text-sm">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="confirm_password" id="reg_confirm_password" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Avatar (Optional)</label>
                    <input type="file" name="img" accept="image/*" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-red-500 transition shadow-sm bg-white text-sm" />
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold py-3 rounded-lg shadow-lg hover:from-red-700 hover:to-rose-700 transition-all duration-300">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </button>
            </form>

            <div class="mt-4 text-center">
                <span class="text-red-500 text-sm">Already have an account?</span>
                <button onclick="switchToLogin()" class="ml-2 text-red-700 font-bold hover:text-rose-800 underline transition-all text-sm">Sign In</button>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.show {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.9) translateY(20px);
            transition: transform 0.3s ease;
            position: relative;
        }

        .modal-content-large {
            max-width: 900px;
        }

        .modal-overlay.show .modal-content {
            transform: scale(1) translateY(0);
        }

        .modal-content::-webkit-scrollbar {
            width: 6px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            border-radius: 10px;
        }
    </style>

    <script>
        // Populate Batch Year Dropdown with School Years
        function populateBatchYears() {
            const batchSelect = document.getElementById('batch_year');
            const currentYear = new Date().getFullYear();
            const startYear = 1950; // Starting from 1950 or adjust as needed
            
            // Clear existing options except the first one
            batchSelect.innerHTML = '<option value="">-- Select Batch Year --</option>';
            
            // Generate school years from current year down to start year
            for (let year = currentYear; year >= startYear; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = `${year} - ${year + 1}`; // Display as "2024 - 2025"
                batchSelect.appendChild(option);
            }
        }

        // Call this when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            populateBatchYears();
            
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileNav = document.getElementById('mobile-nav');
            const menuIcon = document.getElementById('menu-icon');
            
            if (mobileMenuBtn && mobileNav && menuIcon) {
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isHidden = mobileNav.classList.contains('hidden');
                    
                    if (isHidden) {
                        // Open menu
                        mobileNav.classList.remove('hidden');
                        menuIcon.classList.remove('fa-bars');
                        menuIcon.classList.add('fa-times');
                    } else {
                        // Close menu
                        mobileNav.classList.add('hidden');
                        menuIcon.classList.remove('fa-times');
                        menuIcon.classList.add('fa-bars');
                    }
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuBtn.contains(event.target) && !mobileNav.contains(event.target)) {
                        mobileNav.classList.add('hidden');
                        menuIcon.classList.remove('fa-times');
                        menuIcon.classList.add('fa-bars');
                    }
                });
                
                // Close mobile menu when clicking on a link
                const mobileLinks = mobileNav.querySelectorAll('a, button');
                mobileLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        mobileNav.classList.add('hidden');
                        menuIcon.classList.remove('fa-times');
                        menuIcon.classList.add('fa-bars');
                    });
                });
            }
        });

        // Handle Login Form Submission
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('username', document.getElementById('login_username').value);
            formData.append('password', document.getElementById('login_password').value);
            
            try {
                const response = await fetch('authenticate.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Success - reload page
                    window.location.href = 'index.php';
                } else if (data.type === 'unverified') {
                    document.getElementById('unverifiedError').classList.remove('hidden');
                    document.getElementById('loginError').classList.add('hidden');
                } else {
                    document.getElementById('loginError').classList.remove('hidden');
                    document.getElementById('unverifiedError').classList.add('hidden');
                }
            } catch (error) {
                console.error('Login error:', error);
                document.getElementById('loginError').classList.remove('hidden');
                document.getElementById('unverifiedError').classList.add('hidden');
            }
        });

        // Handle Register Form Submission
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const password = document.getElementById('reg_password').value;
            const confirm = document.getElementById('reg_confirm_password').value;
            
            // Validate password
            if (password.length < 8) {
                alert('Password must be at least 8 characters long.');
                return false;
            }
            
            if (!/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                alert('Password must contain at least one uppercase letter and one number.');
                return false;
            }
            
            if (password !== confirm) {
                alert('Passwords do not match.');
                return false;
            }
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('register_save.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                // Try to parse as JSON first
                const contentType = response.headers.get('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    // It's JSON - parse it
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        // Show success message
                        document.getElementById('registerSuccess').classList.remove('hidden');
                        this.reset();
                        document.querySelector('#registerModal .modal-content').scrollTop = 0;
                        setTimeout(() => {
                            closeRegisterModal();
                            window.location.href = 'index.php';
                        }, 3000);
                    } else {
                        // Show specific error message from server
                        alert('Registration failed:\n\n' + data.message);
                    }
                } else {
                    // Fallback: parse as text
                    const text = await response.text();
                    console.log('Registration response:', text);
                    
                    if (text.includes('success') || text.includes('Registration Submitted') || text.includes('awaiting admin approval')) {
                        document.getElementById('registerSuccess').classList.remove('hidden');
                        this.reset();
                        document.querySelector('#registerModal .modal-content').scrollTop = 0;
                        setTimeout(() => {
                            closeRegisterModal();
                            window.location.href = 'index.php';
                        }, 3000);
                    } else {
                        alert('Registration failed. Please check the form and try again.');
                    }
                }
            } catch (error) {
                console.error('Registration error:', error);
                alert('Registration failed. Please try again.');
            }
        });
    </script>
   
</body>
</html>