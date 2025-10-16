<?php 
session_start();
include 'admin/db_connect.php'; 
?>
<!-- Bootstrap CSS and JS (REQUIRED for modal) -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- Tailwind + FontAwesome (ensure Tailwind loads after Bootstrap so utilities win) -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Landing-style Navigation (copied from index.php) -->
<style>
    .navbar-scrolled { backdrop-filter: blur(10px); background: rgba(127, 29, 29, 0.95) !important; }
    .glass-effect { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(255, 255, 255, 0.95); border: 1px solid rgba(209, 213, 219, 0.3);}
    .glass-dark { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(127, 29, 29, 0.95); border: 1px solid rgba(185, 28, 28, 0.3);}    
    /* Normalize nav item sizing to match landing; ensure active state keeps same size */
    .nav-link, .nav-link.active { position: relative; overflow: hidden; display: inline-flex; align-items: center; padding: 0.5rem 1rem; gap: 0.5rem; line-height: 1; }
    .nav-link i, .nav-link.active i { margin-right: 0.5rem; }
    .nav-link::before { content: ''; position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: linear-gradient(90deg,transparent,#fca5a5,transparent); transition: all 0.3s ease; transform: translateX(-50%);}    
    .nav-link:hover::before { width: 100%; }
</style>

<header id="mainNav" class="fixed top-0 w-full z-40 py-4 transition-all duration-300 glass-dark shadow-lg backdrop-blur-lg">
    <div class="container mx-auto px-6">
        <div class="flex justify-between items-center">
            <a href="./" class="text-2xl font-bold text-white hover:text-red-300 transition-colors duration-300">
                Alumni Nexus
            </a>

            <button id="mobile-menu-btn" class="md:hidden text-white p-2 rounded-lg hover:bg-white/10 transition-colors">
                <i class="fas fa-bars text-xl"></i>
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
                    <a href="careers.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center active">
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

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function(){
        const nav = document.getElementById('mobile-nav');
        if(nav) nav.classList.toggle('hidden');
    });
    // User dropdown toggle
    document.getElementById('account_settings')?.addEventListener('click', function(e){
        e.preventDefault();
        const menu = document.getElementById('dropdown-menu');
        if(menu) menu.classList.toggle('hidden');
    });
    document.getElementById('mobile-account_settings')?.addEventListener('click', function(){
        const menu = document.getElementById('dropdown-menu');
        if(menu) menu.classList.toggle('hidden');
    });
</script>
<style>
/* Modern red theme variables */
:root {
    --primary-red: #dc2626;
    --secondary-red: #ef4444;
    --light-red: #fef2f2;
    --dark-red: #991b1b;
    --accent-red: #f87171;
    --gradient-red: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    --gradient-light: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
    --shadow-red: rgba(220, 38, 38, 0.2);
    --text-dark: #1f2937;
    --text-light: #6b7280;
    --white: #ffffff;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
}

/* Global styles */
body {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
}

/* Enhanced masthead */
.masthead {
    background: var(--gradient-red);
    position: relative;
    overflow: hidden;
    min-height: 40vh !important;
    height: 40vh !important;
    box-shadow: 0 10px 30px var(--shadow-red);
}

.masthead::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><path d="M0 40L40 0h40v40L40 80H0V40z"/></g></g></svg>');
    opacity: 0.3;
    min-height: 40vh !important;
    height: 40vh !important;
}

.masthead .container-fluid {
    position: relative;
    z-index: 2;
}

.masthead h3 {
    font-size: 3.2rem;
    font-weight: 700;
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    margin-bottom: 2rem;
    letter-spacing: -0.025em;
}

.masthead .divider {
    max-width: 120px;
    border-top: 4px solid rgba(255, 255, 255, 0.8);
    margin: 2rem auto 3rem;
}

/* Enhanced "Post Job" button */
.post-job-btn {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 15px 30px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-transform: none;
}

.post-job-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
    color: white;
}

.post-job-btn i {
    font-size: 1.2rem;
}

/* Search section */
.search-section {
    background: var(--white);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    padding: 30px;
    margin: -40px 15px 40px;
    position: relative;
    z-index: 10;
    border: none;
}

.search-container {
    display: flex;
    gap: 15px;
    align-items: stretch;
}

.search-input-wrapper {
    flex: 1;
    position: relative;
}

.search-input {
    width: 100%;
    padding: 18px 20px 18px 55px;
    border: 2px solid var(--gray-200);
    border-radius: 15px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: var(--gray-50);
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-red);
    background: var(--white);
    box-shadow: 0 0 0 3px var(--shadow-red);
}

.search-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
    font-size: 1.1rem;
}

.search-btn {
    background: var(--gradient-red);
    border: none;
    color: white;
    padding: 18px 30px;
    border-radius: 15px;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px var(--shadow-red);
    min-width: 120px;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px var(--shadow-red);
    color: white;
}

.search-btn:active {
    transform: translateY(0);
}

/* Main content */
.jobs-container {
    padding: 0 15px 60px;
}

.section-header {
    text-align: center;
    margin-bottom: 40px;
}

.job-stats {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.stat-item {
    background: var(--white);
    padding: 20px 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    text-align: center;
    min-width: 120px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-red);
    display: block;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Enhanced job cards */
.job-list {
    background: var(--white);
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    margin-bottom: 25px;
    border: none;
    cursor: pointer;
    position: relative;
}

.job-list::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: var(--gradient-red);
    z-index: 1;
}

.job-list:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
}

.job-list .card-body {
    padding: 35px 40px;
}

.job-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    gap: 20px;
}

.job-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0 0 10px 0;
    line-height: 1.3;
}

.job-meta {
    display: flex;
    gap: 25px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-light);
    font-size: 0.95rem;
    font-weight: 500;
}

.meta-item i {
    color: var(--primary-red);
    font-size: 1rem;
}

.job-description {
    color: var(--text-light);
    line-height: 1.6;
    margin-bottom: 25px;
    font-size: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.job-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 2px solid var(--gray-100);
    gap: 15px;
}

.posted-by {
    background: var(--gradient-light);
    color: var(--primary-red);
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 0.85rem;
    font-weight: 600;
    border: none;
    display: flex;
    align-items: center;
    gap: 6px;
}

.posted-by i {
    font-size: 0.9rem;
}

.read-more-btn {
    background: var(--gradient-red);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 15px var(--shadow-red);
}

.read-more-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px var(--shadow-red);
    color: white;
}

.read-more-btn i {
    transition: transform 0.3s ease;
}

.read-more-btn:hover i {
    transform: translateX(3px);
}

/* No jobs message */
.no-jobs {
    text-align: center;
    padding: 80px 20px;
    background: var(--white);
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    margin-top: 40px;
}

.no-jobs-icon {
    font-size: 4rem;
    color: var(--accent-red);
    margin-bottom: 20px;
}

.no-jobs h5 {
    color: var(--text-dark);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.no-jobs p {
    color: var(--text-light);
    font-size: 1.1rem;
}

/* Search highlighting */
.highlight {
    background: linear-gradient(120deg, #fef3c7 0%, #fbbf24 100%);
    padding: 2px 4px;
    border-radius: 4px;
    font-weight: 600;
}

/* Loading spinner */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid var(--gray-200);
    border-top: 4px solid var(--primary-red);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive design */
@media (max-width: 992px) {
    .masthead h3 {
        font-size: 2.5rem;
    }
    
    .search-container {
        flex-direction: column;
    }
    
    .search-btn {
        width: 100%;
    }
    
    .job-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .job-footer {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .posted-by {
        text-align: center;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .masthead {
        min-height: 35vh !important;
        height: 35vh !important;
    }
    
    .masthead::before {
        min-height: 35vh !important;
        height: 35vh !important;
    }
    
    .masthead h3 {
        font-size: 2rem;
    }
    
    .post-job-btn {
        padding: 12px 25px;
        font-size: 1rem;
    }
    
    .search-section {
        margin: -30px 10px 30px;
        padding: 20px;
    }
    
    .search-input {
        padding: 15px 18px 15px 50px;
        font-size: 14px;
    }
    
    .search-icon {
        left: 18px;
    }
    
    .search-btn {
        padding: 15px 25px;
    }
    
    .job-list .card-body {
        padding: 25px 20px;
    }
    
    .job-title {
        font-size: 1.5rem;
    }
    
    .job-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .job-stats {
        gap: 15px;
    }
    
    .stat-item {
        padding: 15px 20px;
        min-width: 100px;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
}

/* Animations */
.job-list {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

.job-list:nth-child(even) {
    animation-delay: 0.1s;
}

.job-list:nth-child(odd) {
    animation-delay: 0.2s;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Click effect */
.job-list.clicked {
    transform: translateY(-4px) scale(0.98);
    transition: all 0.1s ease;
}
</style>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Landing-style hero (matches index.php) -->
<style>
    /* push hero content below fixed header so title is visible */
    .masthead-bg { background: linear-gradient(135deg,#7f1d1d 0%,#b91c1c 50%,#ef4444 100%); position: relative; overflow: hidden; padding-top: 6.5rem; }
    @media (max-width: 1024px) { .masthead-bg { padding-top: 6rem; } }
    @media (max-width: 640px) { .masthead-bg { padding-top: 5rem; } }
    .masthead-bg::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="%23ffffff08" points="0,1000 1000,0 1000,1000"/></svg>'); z-index: 1; }
    .masthead-bg .container { position: relative; z-index: 2; }
</style>

<style>
    /* Ensure jobs content sits below the fixed header */
    .jobs-container { padding-top: 8rem; }
    @media (max-width: 1024px) { .jobs-container { padding-top: 7rem; } }
    @media (max-width: 640px) { .jobs-container { padding-top: 6rem; } }
</style>

<section class="masthead-bg min-h-[35vh] flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg,#7f1d1d 0%,#b91c1c 50%,#ef4444 100%);">
    <div class="absolute inset-0 bg-gradient-to-r from-red-900/20 to-rose-900/20 z-10"></div>
    <div class="container mx-auto px-6 text-center relative z-20">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">
                Job Opportunities
            </h1>
            <p class="text-lg md:text-xl text-red-100 mb-4">Explore current openings and connect with employers</p>
            <hr class="divider my-4 w-24 mx-auto" />
        </div>
    </div>
</section>

<div class="container jobs-container">
    <!-- Search Section -->
    <div class="search-section">
        <div class="search-container">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search jobs by title, company, location..." id="filter">
            </div>
            <button class="search-btn" id="search">
                <i class="fas fa-search"></i>
                Search
            </button>
        </div>
    </div>

    <!-- Job Statistics -->
    <div class="section-header">
        <div class="job-stats">
            <div class="stat-item">
                <span class="stat-number" id="total-jobs">0</span>
                <span class="stat-label">Total Jobs</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="active-jobs">0</span>
                <span class="stat-label">Active Jobs</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="companies">0</span>
                <span class="stat-label">Companies</span>
            </div>
        </div>
    </div>

    <!-- Jobs List -->
    <div class="jobs-grid">
        <?php
        $event = $conn->query("SELECT c.*,u.name from careers c inner join users u on u.id = c.user_id order by id desc");
        $total_jobs = 0;
        $companies = array();
        
        if($event && $event->num_rows > 0):
            while($row = $event->fetch_assoc()):
                $total_jobs++;
                if (!in_array($row['company'], $companies)) {
                    $companies[] = $row['company'];
                }
                
                $trans = get_html_translation_table(HTML_ENTITIES,ENT_QUOTES);
                unset($trans["\""], $trans["<"], $trans[">"], $trans["<h2"]);
                $desc = strtr(html_entity_decode($row['description']),$trans);
                $desc = str_replace(array("<li>","</li>"), array("",","), $desc);
        ?>
        <div class="card job-list" data-id="<?php echo $row['id'] ?>">
            <div class="card-body">
                <div class="job-header">
                    <div class="job-info">
                        <h3 class="job-title">
                            <span class="filter-txt"><?php echo ucwords($row['job_title']) ?></span>
                        </h3>
                        <div class="job-meta">
                            <div class="meta-item">
                                <i class="fas fa-building"></i>
                                <span class="filter-txt"><?php echo ucwords($row['company']) ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="filter-txt"><?php echo ucwords($row['location']) ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                <span>Posted recently</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="job-description">
                    <span class="filter-txt"><?php echo strip_tags($desc) ?></span>
                </div>
                
                <div class="job-footer">
                    <span class="posted-by">
                        <i class="fas fa-user-circle"></i>
                        Posted by: <?php echo $row['name'] ?>
                    </span>                 
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <div class="no-jobs">
            <div class="no-jobs-icon">        
                <i class="fas fa-briefcase"></i>
            </div>
            <h5>No Job Opportunities</h5>
            <p>There are no job postings at the moment. Check back later or post your own job opportunity!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update job statistics
    const totalJobs = <?php echo $total_jobs; ?>;
    const totalCompanies = <?php echo count($companies); ?>;
    
    $('#total-jobs').text(totalJobs);
    $('#active-jobs').text(totalJobs);
    $('#companies').text(totalCompanies);
    
    // Enhanced new career modal
    $('#new_career').click(function() {
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        // Add loading state
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        setTimeout(() => {
            uni_modal("New Job Hiring", "manage_career.php", 'mid-large');
            $btn.html(originalHtml);
        }, 300);
    });
    
    // Enhanced read more functionality
    $('.read_more').click(function(e) {
        e.stopPropagation();
        const jobId = $(this).attr('data-id');
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        // Add loading state
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        setTimeout(() => {
            uni_modal("Career Opportunity", "view_jobs.php?id=" + jobId, 'mid-large');
            $btn.html(originalHtml);
        }, 300);
    });
    
    // Enhanced search functionality
    let searchTimeout;
    
    $('#filter').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch();
        }, 300);
    });
    
    $('#filter').keypress(function(e) {
        if (e.which == 13) {
            e.preventDefault();
            $('#search').trigger('click');
        }
    });
    
    $('#search').click(function() {
        performSearch();
    });
    
    function performSearch() {
        const txt = $('#filter').val().toLowerCase().trim();
        let visibleCount = 0;
        
        showLoading();
        
        setTimeout(() => {
            if (txt == '') {
                $('.job-list').show().removeClass('filtered-out');
                removeHighlight($('.filter-txt'));
                visibleCount = $('.job-list').length;
            } else {
                $('.job-list').each(function() {
                    const $job = $(this);
                    let content = "";
                    
                    $job.find(".filter-txt").each(function() {
                        content += ' ' + $(this).text();
                    });
                    
                    if (content.toLowerCase().includes(txt)) {
                        $job.show().removeClass('filtered-out');
                        highlightText($job.find('.filter-txt'), txt);
                        visibleCount++;
                    } else {
                        $job.hide().addClass('filtered-out');
                    }
                });
            }
            
            // Update active jobs counter
            $('#active-jobs').text(visibleCount);
            
            // Show/hide no results message
            toggleNoResultsMessage(visibleCount === 0 && txt !== '');
            
            hideLoading();
        }, 500);
    }
    
    // Highlight matching text
    function highlightText($elements, searchTerm) {
        $elements.each(function() {
            const $element = $(this);
            const text = $element.text();
            const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
            const highlightedText = text.replace(regex, '<span class="highlight">$1</span>');
            $element.html(highlightedText);
        });
    }
    
    // Remove highlighting
    function removeHighlight($elements) {
        $elements.each(function() {
            const $element = $(this);
            const text = $element.text();
            $element.text(text);
        });
    }
    
    // Escape special regex characters
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    // Show/hide no results message
    function toggleNoResultsMessage(show) {
        const $noResults = $('#no-results-message');
        
        if (show && $noResults.length === 0) {
            const noResultsHtml = `
                <div id="no-results-message" class="no-jobs">
                    <div class="no-jobs-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h5>No Jobs Found</h5>
                    <p>Try adjusting your search terms to find relevant job opportunities.</p>
                </div>
            `;
            $('.jobs-grid').append(noResultsHtml);
        } else if (!show && $noResults.length > 0) {
            $noResults.remove();
        }
    }
    
    // Clear search on Escape key
    $('#filter').on('keydown', function(e) {
        if (e.key === 'Escape') {
            $(this).val('');
            performSearch();
        }
    });
    
    // Job card click functionality
    $('.job-list').click(function(e) {
        if (!$(e.target).hasClass('read_more') && !$(e.target).closest('.read_more').length) {
            const jobId = $(this).attr('data-id');
            
            // Add click effect
            $(this).addClass('clicked');
            setTimeout(() => {
                $(this).removeClass('clicked');
            }, 200);
            
            // Open modal
            setTimeout(() => {
                uni_modal("Career Opportunity", "view_jobs.php?id=" + jobId, 'mid-large');
            }, 150);
        }
    });
    
    // Loading functions
    function showLoading() {
        if ($('.loading-overlay').length === 0) {
            $('body').append(`
                <div class="loading-overlay">
                    <div class="loading-spinner"></div>
                </div>
            `);
        }
    }
    
    function hideLoading() {
        $('.loading-overlay').remove();
    }
    
    // Real-time search suggestions (optional enhancement)
    $('#filter').on('focus', function() {
        if ($(this).val() === '') {
            // Could show popular search suggestions here
        }
    });
    
    // Auto-refresh job stats periodically (optional)
    setInterval(() => {
        const visibleJobs = $('.job-list:visible').length;
        $('#active-jobs').text(visibleJobs);
    }, 5000);
});

// Custom loading functions for compatibility
function start_load() {
    if ($('.loading-overlay').length === 0) {
        $('body').append(`
            <div class="loading-overlay">
                <div class="loading-spinner"></div>
            </div>
        `);
    }
}

function end_load() {
    $('.loading-overlay').remove();
}

// Modal Functions
window.openLoginModal = function() {
    document.getElementById('loginModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
window.closeLoginModal = function() {
    document.getElementById('loginModal').classList.remove('show');
    document.body.style.overflow = 'auto';
}
window.openRegisterModal = function() {
    closeLoginModal();
    document.getElementById('registerModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
window.closeRegisterModal = function() {
    document.getElementById('registerModal').classList.remove('show');
    document.body.style.overflow = 'auto';
}
window.switchToLogin = function() {
    closeRegisterModal();
    openLoginModal();
}

// Close modals
document.addEventListener('click', function(e) {
    if (e.target.id === 'loginModal') closeLoginModal();
    if (e.target.id === 'registerModal') closeRegisterModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLoginModal();
        closeRegisterModal();
    }
});

// Populate Batch Years
function populateBatchYears() {
    const batchSelect = document.getElementById('batch_year_careers');
    if (!batchSelect) return;
    const currentYear = new Date().getFullYear();
    const startYear = 1950;
    batchSelect.innerHTML = '<option value="">-- Select Batch Year --</option>';
    for (let year = currentYear; year >= startYear; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = `${year} - ${year + 1}`;
        batchSelect.appendChild(option);
    }
}
document.addEventListener('DOMContentLoaded', populateBatchYears);

// Handle Login
async function handleLogin(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('username', document.getElementById('login_username').value);
    formData.append('password', document.getElementById('login_password').value);
    try {
        const response = await fetch('authenticate.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await response.json();
        if (data.status === 'success') {
            window.location.reload();
        } else if (data.type === 'unverified') {
            document.getElementById('unverifiedError').classList.remove('hidden');
            document.getElementById('loginError').classList.add('hidden');
        } else {
            document.getElementById('loginError').classList.remove('hidden');
            document.getElementById('unverifiedError').classList.add('hidden');
        }
    } catch (error) {
        document.getElementById('loginError').classList.remove('hidden');
    }
    return false;
}

// Handle Register
async function handleRegister(e) {
    e.preventDefault();
    const password = document.getElementById('reg_password').value;
    const confirm = document.getElementById('reg_confirm_password').value;
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
    const formData = new FormData(e.target);
    try {
        const response = await fetch('register_save.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const text = await response.text();
        if (text.includes('success') || text.includes('Registration Submitted') || text.includes('awaiting admin approval')) {
            document.getElementById('registerSuccess').classList.remove('hidden');
            e.target.reset();
            document.querySelector('#registerModal .modal-content').scrollTop = 0;
            setTimeout(() => {
                closeRegisterModal();
                window.location.href = 'index.php';
            }, 3000);
        } else if (text.includes('error') || text.includes('exists')) {
            alert('Registration failed: Username or email may already exist. Please try different credentials.');
        } else {
            alert('Registration failed. Please try again.');
        }
    } catch (error) {
        alert('Registration failed. Please try again.');
    }
    return false;
}
</script>

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

<!-- Note: careers.php and view_jobs.php now use modals via openLoginModal() function.
     Full modal HTML is in dedicated modal files or included via JavaScript -->