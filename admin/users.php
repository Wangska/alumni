<?php
// Session already started in index.php, don't start again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// Handle verify action
if (isset($_GET['verify_id'])) {
    $verify_id = intval($_GET['verify_id']);
    // Set status=1 for alumnus_bio with this id
    $conn->query("UPDATE alumnus_bio SET status = 1 WHERE id = $verify_id");
    // Optionally show a success message (or redirect)
    header("Location: users.php?verified=1");
    exit;
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
            <a href="dashboard.php" class="nav-item nav-courses flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-dashboard"></i> 
                <span>Dashboard</span>
            </a>
            <a href="gallery.php" class="nav-item nav-courses flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-images"></i> 
                <span>Gallery</span>
            </a>
             <a href="courses.php" class="nav-item nav-jobs flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-graduation-cap"></i> 
                <span>Course List</span>
            </a>
            <a href="alumni.php" class="nav-item nav-jobs flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-user-friends"></i> 
                <span>Alumni List</span>
            </a>
            <a href="jobs.php" class="nav-item nav-jobs flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-briefcase"></i> 
                <span>Jobs</span>
            </a>
            <a href="events.php" class="nav-item nav-users flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-calendar-alt"></i> 
                <span>Events</span>
            </a>
            <a href="forums.php" class="nav-item nav-users flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-comments"></i> 
                <span>Forum</span>
            </a>
            <a href="users.php" class="nav-item nav-home active flex items-center gap-3 px-4 py-3 rounded-lg text-primary-700 bg-gradient-to-r from-primary-100 to-primary-50 font-medium hover:bg-primary-200 transition">
                <i class="fas fa-users-cog"></i> 
                <span>Users</span>
            </a>
        </nav>
    </aside>
<main class="ml-0 md:ml-64 pt-10 min-h-screen bg-gray-50">
    <div class="w-full px-2 md:px-8 lg:px-12">
        <div class="mb-10">
            <div class="flex items-center gap-6 mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-600 to-rose-600 rounded-full flex items-center justify-center shadow-lg border-4 border-primary-100">
                    <i class="fas fa-users-cog text-white text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-4xl font-extrabold text-primary-700 drop-shadow">User List</h1>
                    <p class="text-primary-400 mt-1 font-medium">View all users</p>
                </div>
            </div>
            <?php if(isset($_GET['verified'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-3 rounded mb-4 font-semibold flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-500"></i> User account verified successfully!
                </div>
            <?php endif; ?>
        </div>
        <div class="bg-white shadow-2xl rounded-3xl overflow-hidden border border-primary-100 mb-10">
            <div class="px-2 md:px-8 py-8">
                <div class="overflow-x-auto rounded-2xl border-2 border-primary-100 shadow-md">
                    <table class="min-w-full divide-y divide-primary-100 text-sm" id="usersTable">
                        <thead class="bg-gradient-to-r from-primary-100 to-primary-50">
                            <tr>
                                <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 text-center border-r border-primary-100">#</th>
                                <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 border-r border-primary-100">Name</th>
                                <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 border-r border-primary-100">Username</th>
                                <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 border-r border-primary-100">Type</th>
                                <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 border-r border-primary-100">Verified</th>
                                <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-primary-100">
                            <?php
                                $type = array("","Admin","Staff","Alumnus/Alumna");
                                $users = $conn->query("SELECT u.*, ab.status, ab.id as alumnus_id FROM users u LEFT JOIN alumnus_bio ab ON u.alumnus_id = ab.id WHERE u.type != 1 ORDER BY u.name ASC");
                                $i = 1;
                                while($row= $users->fetch_assoc()):
                            ?>
                            <tr class="hover:bg-primary-50 transition duration-150 group">
                                <td class="text-center px-2 md:px-6 py-4 font-bold text-primary-700 group-hover:text-primary-900"><?php echo $i++ ?></td>
                                <td class="px-2 md:px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 shadow-inner">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="font-semibold text-primary-700 group-hover:text-primary-900"><?php echo ucwords($row['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-2 md:px-6 py-4">
                                    <span class="font-mono text-primary-600 bg-primary-50 px-2 py-1 rounded-md shadow-sm"><?php echo $row['username'] ?></span>
                                </td>
                                <td class="px-2 md:px-6 py-4">
                                    <span class="inline-block px-3 py-1 rounded-2xl font-semibold 
                                        <?php echo $row['type'] == 2 ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700'; ?>">
                                        <?php echo $type[$row['type']] ?>
                                    </span>
                                </td>
                                <td class="px-2 md:px-6 py-4 text-center">
                                    <?php if($row['type'] == 3): // Only show for alumni ?>
                                        <?php if($row['status'] == 1): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-2xl bg-green-100 text-green-700 font-semibold text-xs">
                                                <i class="fas fa-check-circle mr-1"></i> Verified
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-2xl bg-red-100 text-red-700 font-semibold text-xs">
                                                <i class="fas fa-times-circle mr-1"></i> Not Verified
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-2 md:px-6 py-4 text-center">
                                    <?php if($row['type'] == 3 && $row['status'] == 0): ?>
                                        <a href="users.php?verify_id=<?php echo $row['alumnus_id']; ?>" 
                                           class="bg-gradient-to-r from-green-400 to-green-600 text-white px-4 py-1 rounded-lg font-semibold text-xs hover:from-green-500 hover:to-green-700 transition">
                                            <i class="fas fa-check"></i> Verify
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>


    <!-- Delete Confirmation Modal -->
    <div id="deleteForumModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-2xl border border-red-200 px-8 py-6 flex flex-col items-center animate-fade-in">
            <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4 animate-pulse"></i>
            <h3 class="text-2xl font-bold text-red-700 mb-2">Delete Forum?</h3>
            <div class="text-lg text-gray-700 mb-3 text-center">
                Are you sure you want to delete this forum topic? This action cannot be undone.
            </div>
            <div class="flex gap-4 mt-4">
                <button onclick="closeDeleteForumModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-6 py-2 rounded-lg shadow">Cancel</button>
                <button id="deleteForumConfirmBtn" class="bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold px-6 py-2 rounded-lg shadow">Delete</button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-2xl border border-red-200 px-8 py-6 flex flex-col items-center animate-fade-in">
            <i class="fas fa-check-circle text-5xl text-red-500 mb-4 animate-pulse"></i>
            <h3 class="text-2xl font-bold text-red-700 mb-2">Success</h3>
            <div id="successMessage" class="text-lg text-gray-700 mb-3"></div>
            <button onclick="closeModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2 rounded-lg shadow">Close</button>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $('table').dataTable();
        $('#new_user').click(function(){
            uni_modal('New User','manage_user.php')
        })
        $('.edit_user').click(function(){
            uni_modal('Edit User','manage_user.php?id='+$(this).attr('data-id'))
        })
        $('.delete_user').click(function(){
                _conf("Are you sure to delete this user?","delete_user",[$(this).attr('data-id')])
            })
            function delete_user($id){
                start_load()
                $.ajax({
                    url:'ajax.php?action=delete_user',
                    method:'POST',
                    data:{id:$id},
                    success:function(resp){
                        if(resp==1){
                            alert_toast("Data successfully deleted",'success')
                            setTimeout(function(){
                                location.reload()
                            },1500)

                        }
                    }
                })
            }


        // Modal close functions
        function closeForumModal() { $('#forumModal').addClass('hidden'); }
        function closeViewForumModal() { $('#viewForumModal').addClass('hidden'); }
        function closeDeleteForumModal() { $('#deleteForumModal').addClass('hidden'); }
        function showModal(msg) {
            $('#successMessage').text(msg);
            $('#successModal').removeClass('hidden');
        }
        function closeModal() {
            $('#successModal').addClass('hidden');
        }
        // Dropdown toggle logic (unchanged)
        function toggleDropdown() {/* ... unchanged ... */}
        document.addEventListener('click', function(event){
            const btn = document.getElementById('account_settings');
            const menu = document.getElementById('dropdown_menu');
            if (!btn.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('opacity-0', 'invisible');
                menu.classList.remove('opacity-100', 'visible');
            }
        });
    </script>
</body>
</html>