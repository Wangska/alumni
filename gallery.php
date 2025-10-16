<?php 
session_start();
include 'admin/db_connect.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
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
                        'fade-in': 'fadeIn 1s ease-out',
                        'slide-up': 'slideUp 0.8s ease-out',
                        'slide-left': 'slideLeft 0.8s ease-out',
                        'slide-right': 'slideRight 0.8s ease-out',
                        'zoom-in': 'zoomIn 0.6s ease-out',
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-red': 'pulseRed 2s ease-in-out infinite',
                        'gradient-shift': 'gradientShift 8s ease infinite',
                        'shimmer': 'shimmer 2s linear infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(50px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        slideLeft: {
                            '0%': { transform: 'translateX(-50px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        },
                        slideRight: {
                            '0%': { transform: 'translateX(50px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        },
                        zoomIn: {
                            '0%': { transform: 'scale(0.8)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '33%': { transform: 'translateY(-20px) rotate(120deg)' },
                            '66%': { transform: 'translateY(10px) rotate(240deg)' }
                        },
                        pulseRed: {
                            '0%, 100%': { boxShadow: '0 0 30px rgba(239, 68, 68, 0.3)' },
                            '50%': { boxShadow: '0 0 60px rgba(239, 68, 68, 0.7)' }
                        },
                        gradientShift: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' }
                        },
                        shimmer: {
                            '0%': { backgroundPosition: '-200% 0' },
                            '100%': { backgroundPosition: '200% 0' }
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* landing header styles to match colors */
        .glass-effect { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(255, 255, 255, 0.95); border: 1px solid rgba(209, 213, 219, 0.3);} 
        .glass-dark { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(127, 29, 29, 0.95); border: 1px solid rgba(185, 28, 28, 0.3);} 
        .navbar-scrolled { backdrop-filter: blur(10px); background: rgba(127, 29, 29, 0.95) !important; }
        .nav-link { position: relative; overflow: hidden; }
        .nav-link::before { content: ''; position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: linear-gradient(90deg,transparent,#fca5a5,transparent); transition: all 0.3s ease; transform: translateX(-50%); }
        .nav-link:hover::before { width: 100%; }

        .red-gradient-bg {
            background: linear-gradient(-45deg, #dc2626, #ef4444, #f87171, #fca5a5, #dc2626, #b91c1c);
            background-size: 400% 400%;
            animation: gradientShift 12s ease infinite;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .gallery-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(220, 38, 38, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .gallery-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px rgba(220, 38, 38, 0.25);
            border-color: rgba(220, 38, 38, 0.3);
        }
        
        .gallery-image {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            aspect-ratio: 4/3;
        }
        
        .gallery-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .gallery-card:hover .gallery-image img {
            transform: scale(1.1) rotate(2deg);
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.8) 0%, rgba(239, 68, 68, 0.6) 50%, rgba(248, 113, 113, 0.4) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
        }
        
        .gallery-card:hover .image-overlay {
            opacity: 1;
        }
        
        .floating-elements::before,
        .floating-elements::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(220, 38, 38, 0.1);
            animation: float 8s ease-in-out infinite;
        }
        
        .floating-elements::before {
            width: 300px;
            height: 300px;
            top: 15%;
            left: 5%;
            animation-delay: 0s;
        }
        
        .floating-elements::after {
            width: 200px;
            height: 200px;
            top: 60%;
            right: 10%;
            animation-delay: 4s;
        }
        
        .shimmer-effect {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s linear infinite;
        }
        
        .masonry-grid {
            columns: 1;
            column-gap: 2rem;
        }
        
        @media (min-width: 768px) {
            .masonry-grid {
                columns: 2;
            }
        }
        
        @media (min-width: 1024px) {
            .masonry-grid {
                columns: 3;
            }
        }
        
        .masonry-item {
            break-inside: avoid;
            margin-bottom: 2rem;
        }
        
        .stats-counter {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="min-h-screen red-gradient-bg relative overflow-x-hidden floating-elements">
    <!-- Navigation -->
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
                    <a href="gallery.php" class="nav-link text-red-300 bg-white/10 rounded-lg px-4 py-2 rounded-lg transition-all duration-300 flex items-center">
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
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-red-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-pink-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse" style="animation-delay: 3s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-rose-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse" style="animation-delay: 6s;"></div>
    </div>

    <!-- Header Section -->
    <header class="relative z-10 py-12 md:py-16 animate-fade-in">
        <!-- <div class="container mx-auto px-4 text-center">
            <div class="max-w-4xl mx-auto">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white bg-opacity-20 rounded-3xl mb-8 backdrop-blur-sm border border-white border-opacity-30 animate-zoom-in">
                    <i class="fas fa-camera-retro text-4xl text-white"></i>
                </div>
                <h1 class="text-6xl md:text-7xl font-bold text-white mb-6 tracking-tight animate-slide-up">
                    Gallery
                </h1>
                <p class="text-xl md:text-2xl text-white text-opacity-90 mb-8 animate-slide-up" style="animation-delay: 0.2s;">
                    Capturing moments, preserving memories
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-2xl mx-auto animate-slide-up" style="animation-delay: 0.4s;">
                    <div class="stats-counter rounded-2xl p-6 border border-white border-opacity-20">
                        <div class="text-3xl font-bold text-white mb-2">
                            <?php echo $gallery->num_rows; ?>
                        </div>
                        <div class="text-white text-opacity-80 text-sm">
                            <i class="fas fa-images mr-2"></i>Total Images
                        </div>
                    </div>
                    <div class="stats-counter rounded-2xl p-6 border border-white border-opacity-20">
                        <div class="text-3xl font-bold text-white mb-2">
                            <span id="view-counter">0</span>
                        </div>
                        <div class="text-white text-opacity-80 text-sm">
                            <i class="fas fa-eye mr-2"></i>Total Views
                        </div>
                    </div>
                    <div class="stats-counter rounded-2xl p-6 border border-white border-opacity-20">
                        <div class="text-3xl font-bold text-white mb-2">
                            <span id="year-counter">2024</span>
                        </div>
                        <div class="text-white text-opacity-80 text-sm">
                            <i class="fas fa-calendar mr-2"></i>Latest Year
                        </div>
                    </div>
                </div>
                
                <div class="w-32 h-1 bg-white bg-opacity-60 rounded-full mx-auto mt-8 animate-slide-up" style="animation-delay: 0.6s;"></div>
            </div>
        </div> -->
    </header>

    <!-- Gallery Grid Section -->
    <div class="container mx-auto px-4 pb-20 relative z-10" style="margin-top: -28px;">
        
        <!-- Filter/Search Section -->
        <div class="glass-effect rounded-2xl p-6 mb-12 shadow-2xl animate-slide-up" style="animation-delay: 0.3s;">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-filter text-white text-xl"></i>
                    <span class="text-white font-semibold">Gallery Collection</span>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="view-toggle active px-4 py-2 bg-white bg-opacity-20 rounded-lg text-white hover:bg-opacity-30 transition-all duration-300" data-view="masonry">
                        <i class="fas fa-th mr-2"></i>Masonry
                    </button>
                    <button class="view-toggle px-4 py-2 bg-white bg-opacity-20 rounded-lg text-white hover:bg-opacity-30 transition-all duration-300" data-view="grid">
                        <i class="fas fa-grip-horizontal mr-2"></i>Grid
                    </button>
                </div>
            </div>
        </div>

        <!-- Gallery Items Container -->
        <div id="gallery-container" class="masonry-grid">
            
            <?php
            $rtl = 'rtl';
            $ci = 0;
            $img = array();
            $fpath = 'admin/assets/uploads/gallery';
            $files = is_dir($fpath) ? scandir($fpath) : array();
            foreach($files as $val){
                if(!in_array($val, array('.','..'))){
                    $n = explode('_',$val);
                    $img[$n[0]] = $val;
                }
            }
            $gallery = $conn->query("SELECT * from gallery order by id desc");
            $index = 0;
            while($row = $gallery->fetch_assoc()):
                $ci++;
                $delay = ($index % 6) * 0.15; // Stagger animation
                $animationClass = ($ci % 2 == 0) ? 'animate-slide-left' : 'animate-slide-right';
                
                // Reset counter for rtl pattern
                if($ci < 3){
                    $rtl = '';
                } else {
                    $rtl = 'rtl';
                }
                if($ci == 4){
                    $ci = 0;
                }
            ?>
            
            <div class="masonry-item gallery-item" data-id="<?php echo $row['id'] ?>">
                <div class="gallery-card rounded-3xl overflow-hidden shadow-2xl cursor-pointer <?php echo $animationClass ?>" 
                     style="animation-delay: <?php echo $delay; ?>s;">
                    
                    <!-- Image Section -->
                    <div class="gallery-image relative group">
                        <?php if(isset($img[$row['id']]) && is_file($fpath.'/'.$img[$row['id']])): ?>
                            <img 
                                src="<?php echo $fpath.'/'.$img[$row['id']]; ?>" 
                                alt="Gallery Image"
                                class="gallery-img"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="w-full aspect-[4/3] bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center">
                                <div class="text-center text-red-400">
                                    <i class="fas fa-image text-4xl mb-2"></i>
                                    <p class="text-sm">No Image Available</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Hover Overlay -->
                        <div class="image-overlay">
                            <div class="text-center text-white">
                                <i class="fas fa-search-plus text-3xl mb-2 animate-bounce"></i>
                                <p class="font-semibold">View Image</p>
                            </div>
                        </div>
                        
                        <!-- Image Actions -->
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button class="w-10 h-10 bg-white bg-opacity-90 rounded-full shadow-lg flex items-center justify-center text-red-600 hover:bg-red-50 transition-all duration-300 transform hover:scale-110">
                                <i class="fas fa-expand-arrows-alt"></i>
                            </button>
                        </div>
                        
                        <!-- Image Badge -->
                        <div class="absolute bottom-4 left-4 bg-black bg-opacity-60 text-white px-3 py-1 rounded-full text-xs font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <i class="fas fa-eye mr-1"></i>
                            Click to view
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="p-6">
                        <div class="text-center">
                            <!-- Title/Description -->
                            <h3 class="text-lg font-bold text-gray-800 mb-3 line-clamp-2">
                                <?php echo ucwords($row['about']); ?>
                            </h3>
                            
                            <!-- Divider -->
                            <div class="w-16 h-0.5 bg-gradient-to-r from-red-500 to-red-600 mx-auto mb-4 rounded-full"></div>
                            
                            <!-- Meta Information -->
                            <div class="flex items-center justify-center space-x-4 text-sm text-gray-600 mb-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-calendar-alt text-red-500"></i>
                                    <span>Recent</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-tag text-red-500"></i>
                                    <span>Gallery</span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex space-x-3">
                                <button class="flex-1 bg-gradient-to-r from-red-500 to-red-600 text-white py-3 px-4 rounded-xl font-medium hover:from-red-600 hover:to-red-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                    <i class="fas fa-eye mr-2"></i>
                                    View Full Size
                                </button>
                                <button class="book-gallery w-12 h-12 bg-gray-100 text-gray-600 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all duration-300 transform hover:scale-110 shadow-md" 
                                        data-id="<?php echo $row['id'] ?>">
                                    <i class="fas fa-bookmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php 
            $index++;
            endwhile; 
            ?>
            
        </div>
        
        <!-- Empty State -->
        <?php if($gallery->num_rows == 0): ?>
        <div class="text-center py-20">
            <div class="glass-effect rounded-3xl p-12 max-w-md mx-auto">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-images text-red-500 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">No Images Yet</h3>
                <p class="text-white text-opacity-80">The gallery is currently empty. Check back soon for amazing photos!</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Loading Animation -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl p-8 text-center shadow-2xl">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500 mx-auto mb-4"></div>
            <span class="text-gray-700 font-medium">Loading gallery...</span>
        </div>
    </div>

    <script>
        // Enhanced functionality with preserved original behavior
        
        // Preserve original click handlers
        $('.gallery-card').click(function(){
            // Uncomment if you want to enable view functionality
            // location.href = "index.php?page=view_gallery&id="+$(this).attr('data-id')
        });

        // Preserve original booking functionality
        $('.book-gallery').click(function(e){
            e.stopPropagation();
            uni_modal("Submit Booking Request","booking.php?gallery_id="+$(this).attr('data-id'))
        });

        // Enhanced image click functionality (preserved original)
        $('.gallery-img').click(function(e){
            e.stopPropagation();
            viewer_modal($(this).attr('src'));
            
            // Add click animation
            $(this).parent().addClass('animate-pulse');
            setTimeout(() => {
                $(this).parent().removeClass('animate-pulse');
            }, 300);
        });

        // View toggle functionality
        $('.view-toggle').click(function() {
            $('.view-toggle').removeClass('active bg-white bg-opacity-30').addClass('bg-opacity-20');
            $(this).addClass('active bg-opacity-30');
            
            const view = $(this).data('view');
            const container = $('#gallery-container');
            
            if (view === 'masonry') {
                container.removeClass('grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8').addClass('masonry-grid');
                $('.gallery-item').removeClass('w-full').addClass('masonry-item');
            } else {
                container.removeClass('masonry-grid').addClass('grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8');
                $('.gallery-item').removeClass('masonry-item').addClass('w-full');
            }
            
            // Re-animate items
            $('.gallery-item').each(function(index) {
                $(this).css('animation-delay', (index * 0.1) + 's');
                $(this).removeClass('animate-slide-left animate-slide-right').addClass('animate-fade-in');
            });
        });

        // Counter animations
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 50);
        }

        // Initialize counters when page loads
        setTimeout(() => {
            animateCounter(document.getElementById('view-counter'), Math.floor(Math.random() * 1000) + 500);
        }, 1000);

        // Enhanced hover effects
        $('.gallery-card').hover(
            function() {
                $(this).addClass('animate-pulse-red');
            },
            function() {
                $(this).removeClass('animate-pulse-red');
            }
        );

        // Lazy loading simulation
        $('img[loading="lazy"]').each(function() {
            $(this).on('load', function() {
                $(this).parent().addClass('shimmer-effect');
                setTimeout(() => {
                    $(this).parent().removeClass('shimmer-effect');
                }, 1000);
            });
        });

        // Add smooth scroll behavior
        $('html').css('scroll-behavior', 'smooth');

        // Enhanced loading states
        function showLoading() {
            $('#loading-overlay').removeClass('hidden');
        }
        
        function hideLoading() {
            $('#loading-overlay').addClass('hidden');
        }

        // Image error handling
        $('img').on('error', function() {
            $(this).parent().html(`
                <div class="w-full aspect-[4/3] bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center rounded-2xl">
                    <div class="text-center text-red-400">
                        <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
                        <p class="text-sm">Image not found</p>
                    </div>
                </div>
            `);
        });

        // Add intersection observer for better performance
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Initialize page
        $(document).ready(function() {
            // Add loading animation to page elements
            $('.gallery-item').each(function(index) {
                $(this).css('animation-delay', (index * 0.1) + 's');
            });
            
            hideLoading();
        });

        // Header dropdown toggle
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

    <!-- Modal CSS and HTML (same as alumni_list.php) -->
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
                        <select name="batch" id="batch_year_gallery" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-white">
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
        // Populate Batch Year Dropdown
        function populateBatchYears() {
            const batchSelect = document.getElementById('batch_year_gallery');
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
</body>
</html>