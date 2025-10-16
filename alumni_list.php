<?php 
session_start();
include 'admin/db_connect.php'; 
// determine current page for active nav styling
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni List</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.8s ease-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'bounce-in': 'bounceIn 0.6s ease-out',
                        'pulse-red': 'pulseRed 2s ease-in-out infinite',
                        'gradient-shift': 'gradientShift 4s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        bounceIn: {
                            '0%': { transform: 'scale(0.3)', opacity: '0' },
                            '50%': { transform: 'scale(1.05)', opacity: '1' },
                            '70%': { transform: 'scale(0.9)' },
                            '100%': { transform: 'scale(1)' }
                        },
                        pulseRed: {
                            '0%, 100%': { boxShadow: '0 0 20px rgba(239, 68, 68, 0.4)' },
                            '50%': { boxShadow: '0 0 40px rgba(239, 68, 68, 0.8)' }
                        },
                        gradientShift: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' }
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* match landing page header styles */
        .glass-effect { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(255, 255, 255, 0.95); border: 1px solid rgba(209, 213, 219, 0.3);} 
        .glass-dark { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(127, 29, 29, 0.95); border: 1px solid rgba(185, 28, 28, 0.3);} 
        .navbar-scrolled { backdrop-filter: blur(10px); background: rgba(127, 29, 29, 0.95) !important; }
        .nav-link { position: relative; }
        .red-gradient-bg {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 50%, #f87171 100%);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }
        
        .alumni-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(220, 38, 38, 0.1);
        }
        
        .alumni-card:hover {
            border-color: rgba(220, 38, 38, 0.3);
        }
        
        .highlight {
            background: linear-gradient(45deg, #fef3c7, #fcd34d);
            padding: 2px 4px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .search-glow:focus {
            box-shadow: 0 0 20px rgba(220, 38, 38, 0.4);
        }
        
        /* Floating elements removed for cleaner look */
    </style>
</head>

<body class="min-h-screen red-gradient-bg relative overflow-x-hidden">
    
    <!-- Background Decorative Elements (Simplified - removed animations) -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-red-400 rounded-full mix-blend-multiply filter blur-xl opacity-10"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-pink-400 rounded-full mix-blend-multiply filter blur-xl opacity-10"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-rose-400 rounded-full mix-blend-multiply filter blur-xl opacity-10"></div>
    </div>

    <!-- Reused Navigation from landing page -->
    <header id="mainNav" class="fixed top-0 w-full z-40 py-4 glass-dark shadow-lg backdrop-blur-lg">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center">
                <a href="./" class="text-2xl font-bold text-white hover:text-red-300">
                    Alumni Nexus
                </a>

                <button id="mobile-menu-btn" class="md:hidden text-white p-2 rounded-lg hover:bg-white/10">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <nav id="desktop-nav" class="hidden md:flex items-center space-x-8">
                    <a href="index.php?page=home" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="alumni_list.php" class="nav-link <?php echo $current_page == 'alumni_list.php' ? 'text-red-300 bg-white/10 rounded-lg' : 'text-white'; ?> hover:text-red-300 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-users mr-2"></i>Alumni
                    </a>
                    <a href="gallery.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-images mr-2"></i>Gallery
                    </a>
                    <div id="auth-links" class="flex items-center space-x-6">
                    <?php if(isset($_SESSION['login_username'])): ?>
                        <a href="careers.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-briefcase mr-2"></i>Jobs
                        </a>
                        <a href="forum.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-comments mr-2"></i>Forums
                        </a>
                        <div id="user-dropdown" class="relative">
                            <button id="account_settings" type="button" class="flex items-center space-x-2 text-white hover:text-red-300 px-4 py-2 rounded-lg">
                                <i class="fas fa-user-circle text-xl"></i>
                                <span><?= htmlspecialchars($_SESSION['login_username']) ?></span>
                                <i class="fas fa-angle-down"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 glass-effect rounded-lg shadow-xl py-2 hidden" id="dropdown-menu">
                                <a href="admin/ajax.php?action=logout2" class="flex items-center px-4 py-2 text-gray-700 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <button onclick="openLoginModal()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-full shadow-lg flex items-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </button>
                    <?php endif; ?>
                    </div>
                </nav>
                <nav id="mobile-nav" class="md:hidden absolute top-full left-0 right-0 glass-dark rounded-b-2xl shadow-xl hidden">
                    <div class="px-6 py-4 space-y-4">
                        <a href="index.php" class="block text-white hover:text-red-300 py-2 flex items-center">
                            <i class="fas fa-home mr-3"></i>Home
                        </a>
                        <a href="alumni_list.php" class="block text-white hover:text-red-300 py-2 flex items-center">
                            <i class="fas fa-users mr-3"></i>Alumni
                        </a>
                        <a href="gallery.php" class="block text-white hover:text-red-300 py-2 flex items-center">
                            <i class="fas fa-images mr-3"></i>Gallery
                        </a>
                        <div class="pt-4 border-t border-gray-600">
                            <?php if(isset($_SESSION['login_username'])): ?>
                                <a href="careers.php" class="block text-white hover:text-red-300 py-2 flex items-center">
                                    <i class="fas fa-briefcase mr-3"></i>Jobs
                                </a>
                                <a href="forum.php" class="block text-white hover:text-red-300 py-2 flex items-center">
                                    <i class="fas fa-comments mr-3"></i>Forums
                                </a>
                                <button id="mobile-account_settings" type="button" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user-circle mr-2"></i><?= htmlspecialchars($_SESSION['login_username']) ?>
                                    <i class="fas fa-angle-down ml-2"></i>
                                </button>
                                <a href="admin/ajax.php?action=logout2" class="block text-center mt-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg border border-gray-200">Logout</a>
                            <?php else: ?>
                                <button onclick="openLoginModal()" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Header Section -->
    <header class="relative z-10 py-16">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-4xl mx-auto">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-3xl mb-6 backdrop-blur-sm border border-white border-opacity-30">
                    <i class="fas fa-graduation-cap text-3xl text-white"></i>
                </div>
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-4 tracking-tight">
                    Alumni Network
                </h1>
                <p class="text-xl text-white text-opacity-90">
                    Discover and connect with our accomplished graduates
                </p>
                <div class="w-32 h-1 bg-white bg-opacity-60 rounded-full mx-auto mt-6"></div>
            </div>
        </div>
    </header>

    <!-- Search Section -->
    <div class="container mx-auto px-4 mb-8 relative z-10">
        <div class="max-w-4xl mx-auto">
            <div class="glass-card rounded-2xl p-6 shadow-2xl">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-red-500"></i>
                            </div>
                            <input 
                                type="text" 
                                id="filter" 
                                class="w-full pl-10 pr-4 py-3 bg-white bg-opacity-50 border border-red-200 rounded-xl text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent search-glow transition-all duration-300" 
                                placeholder="Search by name, course, batch, or workplace..."
                            >
                        </div>
                    </div>
                    <div class="md:w-auto">
                        <button 
                            id="search" 
                            class="w-full md:w-auto bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 px-8 rounded-xl shadow-lg transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-opacity-50"
                        >
                            <span class="flex items-center justify-center space-x-2">
                                <i class="fas fa-search"></i>
                                <span>Search</span>
                            </span>
                        </button>
                    </div>
                </div>
                
                <!-- Search Stats -->
                <div class="mt-4 text-center">
                    <p class="text-gray-600 text-sm">
                        <i class="fas fa-users text-red-500 mr-2"></i>
                        <span class="mx-2">•</span>
                        <span id="visible-count"><?php echo $alumni->num_rows; ?> Showing</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alumni Grid -->
    <div class="container mx-auto px-4 pb-16 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="alumni-grid">
            
            <?php
            $fpath = 'admin/assets/uploads';
            $alumni = $conn->query("SELECT a.*,c.course,Concat(a.lastname,', ',a.firstname,' ',a.middlename) as name from alumnus_bio a inner join courses c on c.id = a.course_id order by Concat(a.lastname,', ',a.firstname,' ',a.middlename) asc");
            $index = 0;
            while($row = $alumni->fetch_assoc()):
                $delay = ($index % 6) * 0.1; // Stagger animation
            ?>
            
            <div class="item animate-slide-up alumni-card rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 cursor-pointer" 
                 data-id="<?php echo $row['id'] ?>" 
                 style="animation-delay: <?php echo $delay; ?>s;">
                
                <!-- Alumni Image -->
                <div class="relative h-64 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-t from-red-900 via-transparent to-transparent z-10"></div>
                    <img 
                        src="<?php echo $fpath.'/'.$row['avatar'] ?>" 
                        alt="<?php echo $row['name'] ?>"
                        class="w-full h-full object-cover transition-transform duration-500 hover:scale-110"
                        onerror="this.src='https://via.placeholder.com/300x300/dc2626/white?text=No+Image'"
                    >
                    
                    <!-- Floating Action Button -->
                    <div class="absolute top-4 right-4 z-20">
                        <button class="w-10 h-10 bg-white bg-opacity-90 rounded-full shadow-lg flex items-center justify-center text-red-600 hover:bg-red-50 transition-all duration-300 transform hover:scale-110">
                            <i class="fas fa-expand-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Alumni Info -->
                <div class="p-6">
                    <div class="text-center mb-4">
                        <h3 class="filter-txt text-xl font-bold text-gray-800 mb-2"><?php echo $row['name'] ?></h3>
                        <div class="w-16 h-0.5 bg-gradient-to-r from-red-500 to-red-600 mx-auto rounded-full"></div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope text-red-500 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="filter-txt font-semibold text-gray-800 truncate"><?php echo $row['email'] ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-graduation-cap text-red-500 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-600">Course</p>
                                <p class="filter-txt font-semibold text-gray-800"><?php echo $row['course'] ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar-alt text-red-500 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-600">Batch</p>
                                <p class="filter-txt font-semibold text-gray-800"><?php echo $row['batch'] ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-briefcase text-red-500 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-600">Currently working</p>
                                <p class="filter-txt font-semibold text-gray-800"><?php echo $row['connected_to'] ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-6 flex space-x-3">
                        <button class="flex-1 bg-gradient-to-r from-red-500 to-red-600 text-white py-2 px-4 rounded-lg font-medium hover:from-red-600 hover:to-red-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-user-circle mr-2"></i>
                            View Profile
                        </button>
                        <button class="book-alumni w-12 h-10 bg-gray-100 text-gray-600 rounded-lg hover:bg-red-50 hover:text-red-600 transition-all duration-300 transform hover:scale-110" 
                                data-id="<?php echo $row['id'] ?>">
                            <i class="fas fa-calendar-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <?php 
            $index++;
            endwhile; 
            ?>
            
        </div>
        
        <!-- No Results Message -->
        <div id="no-results" class="hidden text-center py-16">
            <div class="glass-card rounded-2xl p-8 max-w-md mx-auto">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">No Alumni Found</h3>
                <p class="text-gray-600">Try adjusting your search terms or clear the filter to see all alumni.</p>
                <button id="clear-search" class="mt-4 bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-all duration-300">
                    Clear Search
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl p-8 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-500"></div>
            <span class="text-gray-700 font-medium">Searching...</span>
        </div>
    </div>

    <script>
        // Enhanced search functionality with improved UX
        function start_load() {
            $('#loading-overlay').removeClass('hidden');
        }
        
        function end_load() {
            $('#loading-overlay').addClass('hidden');
        }
        
        function updateVisibleCount() {
            const visibleItems = $('.item:visible').length;
            const totalItems = $('.item').length;
            $('#visible-count').text(visibleItems + ' Showing');
            
            if (visibleItems === 0 && $('#filter').val() !== '') {
                $('#no-results').removeClass('hidden');
                $('#alumni-grid').addClass('hidden');
            } else {
                $('#no-results').addClass('hidden');
                $('#alumni-grid').removeClass('hidden');
            }
        }

        // Alumni card click functionality (preserved original)
        $('.alumni-card').click(function(){
            // Uncomment if you want to enable view functionality
            // location.href = "index.php?page=view_alumni&id="+$(this).attr('data-id')
        });

        // Book alumni functionality (preserved original)
        $('.book-alumni').click(function(e){
            e.stopPropagation();
            uni_modal("Submit Booking Request","booking.php?alumni_id="+$(this).attr('data-id'))
        });

        // Image click functionality (preserved original)
        $('img').click(function(e){
            e.stopPropagation();
            viewer_modal($(this).attr('src'))
        });

        // Enhanced search functionality
        $('#filter').keypress(function(e){
            if(e.which == 13) {
                $('#search').trigger('click')
            }
        });

        $('#filter').on('input', function() {
            // Real-time search as user types (with debounce)
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(function() {
                $('#search').trigger('click');
            }, 300);
        });

        $('#search').click(function(){
            var txt = $('#filter').val().toLowerCase();
            
            if(txt == ''){
                $('.item').show().removeClass('animate-fade-in');
                updateVisibleCount();
                return false;
            }
            
            start_load();
            
            setTimeout(function() {
                $('.item').each(function(){
                    var content = "";
                    $(this).find(".filter-txt").each(function(){
                        content += ' ' + $(this).text().toLowerCase();
                    });
                    
                    if(content.includes(txt)){
                        $(this).show().addClass('animate-fade-in');
                        // Highlight search terms
                        $(this).find(".filter-txt").each(function(){
                            var text = $(this).text();
                            var regex = new RegExp('(' + txt + ')', 'gi');
                            var highlighted = text.replace(regex, '<span class="highlight">$1</span>');
                            $(this).html(highlighted);
                        });
                    } else {
                        $(this).hide().removeClass('animate-fade-in');
                    }
                });
                
                updateVisibleCount();
                end_load();
            }, 500); // Add slight delay for better UX
        });

        // Clear search functionality
        $('#clear-search').click(function() {
            $('#filter').val('');
            $('.item').show().removeClass('animate-fade-in');
            // Remove highlighting
            $('.filter-txt').each(function() {
                $(this).html($(this).text());
            });
            updateVisibleCount();
        });

        // Enhanced hover effects
        $('.alumni-card').hover(
            function() {
                $(this).addClass('animate-pulse-red');
            },
            function() {
                $(this).removeClass('animate-pulse-red');
            }
        );

        // Initialize visible count
        updateVisibleCount();

        // Add smooth scroll behavior
        $('html').css('scroll-behavior', 'smooth');

        // Add loading state to search button
        $('#search').click(function() {
            const $btn = $(this);
            $btn.prop('disabled', true).html(`
                <span class="flex items-center justify-center space-x-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                    <span>Searching...</span>
                </span>
            `);
            
            setTimeout(function() {
                $btn.prop('disabled', false).html(`
                    <span class="flex items-center justify-center space-x-2">
                        <i class="fas fa-search"></i>
                        <span>Search</span>
                    </span>
                `);
            }, 500);
        });

            // Header dropdown and mobile menu behavior (like landing page)
            function toggleDropdown() {
                const dropdown = document.getElementById('dropdown-menu');
                if(!dropdown) return;
                if (dropdown.classList.contains('hidden')) {
                    dropdown.classList.remove('hidden');
                } else {
                    dropdown.classList.add('hidden');
                }
            }
            document.getElementById('account_settings')?.addEventListener('click', function(e){
                e.stopPropagation();
                toggleDropdown();
            });
            document.addEventListener('click', function(e){
                const menu = document.getElementById('dropdown-menu');
                const btn = document.getElementById('account_settings');
                if(menu && btn && !btn.contains(e.target) && !menu.contains(e.target)){
                    menu.classList.add('hidden');
                }
            });

            // Mobile menu toggle
            document.getElementById('mobile-menu-btn')?.addEventListener('click', function(){
                const m = document.getElementById('mobile-nav');
                if(!m) return;
                if(m.classList.contains('hidden')) m.classList.remove('hidden'); else m.classList.add('hidden');
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

            // Auto-open modals based on URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            if (error === '1' || error === 'unverified') {
                openLoginModal();
                if (error === 'unverified') {
                    document.getElementById('unverifiedError')?.classList.remove('hidden');
                } else {
                    document.getElementById('loginError')?.classList.remove('hidden');
                }
                const cleanUrl = window.location.origin + window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
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

            <form id="loginForm" class="space-y-5" onsubmit="return handleLogin(event)">
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

            <form id="registerForm" class="space-y-4" onsubmit="return handleRegister(event)">
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
                        <select name="batch" id="batch_year_modal" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-white">
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
                            if($courses_query):
                            while($course = $courses_query->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course']); ?></option>
                            <?php endwhile; endif; ?>
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
            const batchSelect = document.getElementById('batch_year_modal');
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

        // Call on modal open
        document.addEventListener('DOMContentLoaded', populateBatchYears);

        // Handle Login Form Submission
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

        // Handle Register Form Submission
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
</body>
</html>