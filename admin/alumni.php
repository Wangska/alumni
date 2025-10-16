<?php
// Session already started in index.php, don't start again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// Handle AJAX status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $new_status = intval($_POST['status']);
    $res = $conn->query("UPDATE alumnus_bio SET status=$new_status WHERE id=$id");
    echo $res ? "1" : "0";
    exit;
}

// Pagination settings
$per_page = 10;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query for alumni
$where = "";
if ($search !== "") {
    $search_esc = $conn->real_escape_string($search);
    $where = "WHERE (Concat(a.lastname,', ',a.firstname,' ',a.middlename) LIKE '%$search_esc%' OR c.course LIKE '%$search_esc%')";
}

$total_res = $conn->query("SELECT count(*) as cnt FROM alumnus_bio a INNER JOIN courses c ON c.id=a.course_id $where")->fetch_assoc();
$total = $total_res ? intval($total_res['cnt']) : 0;
$last_page = max(1, ceil($total/$per_page));
$start = ($page-1)*$per_page;

// Main alumni query
$sql = "SELECT a.*,c.course,Concat(a.lastname,', ',a.firstname,' ',a.middlename) as name 
FROM alumnus_bio a INNER JOIN courses c ON c.id = a.course_id $where 
ORDER BY Concat(a.lastname,', ',a.firstname,' ',a.middlename) ASC 
LIMIT $start, $per_page";
$alumni = $conn->query($sql);
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
            <a href="alumni.php" class="nav-item nav-home active flex items-center gap-3 px-4 py-3 rounded-lg text-primary-700 bg-gradient-to-r from-primary-100 to-primary-50 font-medium hover:bg-primary-200 transition">
                <i class="fas fa-user-friends"></i> 
                <span>Alumni List</span>
            </a>
            <a href="jobs.php" class="nav-item nav-jobs flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-briefcase"></i> 
                <span>Jobs</span>
            </a>
            <a href="events.php" class="nav-item nav-events flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-calendar-alt"></i> 
                <span>Events</span>
            </a>
            <a href="forums.php" class="nav-item nav-forums flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-comments"></i> 
                <span>Forum</span>
            </a>
            <a href="users.php" class="nav-item nav-users flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-users-cog"></i> 
                <span>Users</span>
            </a>
        </nav>
    </aside>


    


   <main class="ml-0 md:ml-64 pt-10 transition-all min-h-screen bg-gradient-to-br from-white-50 ">
        <div class="w-full px-4 md:px-8 lg:px-12">

            <!-- Page Header -->
            <div class="mb-10">
            <div class="flex items-center gap-5 mb-4">
                <div class="w-16 h-16 bg-gradient-to-r from-red-600 to-rose-600 rounded-3xl flex items-center justify-center shadow-lg shadow-red-200">
                <i class="fas fa-user-friends text-white text-2xl"></i>
                </div>
                <div>
                <h1 class="text-4xl font-bold text-rose-800">Alumni List</h1>
                </div>
            </div>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-users text-red-600"></i>
                    </div>
                    <div>
                    <div class="text-2xl font-bold text-rose-800"><?php echo $total ?></div>
                    <div class="text-rose-600 text-sm">Total Alumni</div>
                    </div>
                </div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-green-100 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div>
                    <div class="text-2xl font-bold text-rose-800">
                        <?php $verified = $conn->query("SELECT count(*) as cnt FROM alumnus_bio WHERE status=1")->fetch_assoc(); echo $verified['cnt'];?>
                    </div>
                    <div class="text-rose-600 text-sm">Verified</div>
                    </div>
                </div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-amber-100 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-amber-600"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-rose-800">
                            <?php $unverified = $conn->query("SELECT count(*) as cnt FROM alumnus_bio WHERE status=0")->fetch_assoc(); echo $unverified['cnt'];?>
                        </div>
                        <div class="text-rose-600 text-sm">Unverified</div>
                    </div>
                </div>
            </div>
            </div>

            <!-- Alumni Table -->
            <div class="bg-white/90 backdrop-blur-sm shadow-xl rounded-3xl overflow-hidden border border-red-100 hover:shadow-2xl transition-all duration-500">
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 px-8 py-6 relative overflow-hidden">
                <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 left-0 w-40 h-40 bg-red-400 rounded-full -translate-x-16 -translate-y-16"></div>
                <div class="absolute bottom-0 right-0 w-32 h-32 bg-pink-400 rounded-full translate-x-12 translate-y-12"></div>
                </div>
                <div class="flex justify-between items-center relative z-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                    <i class="fas fa-graduation-cap text-white text-xl"></i>
                    </div>
                    <div>
                    <h3 class="text-2xl font-bold text-white">Alumni Directory</h3>
                    <p class="text-red-100">Complete list of registered alumni</p>
                    </div>
                </div>
                
                <!-- Search and Filter Controls -->
                <form class="flex items-center gap-4" method="GET" action="">
                    <div class="relative">
                    <input 
                        type="text" 
                        name="search"
                        value="<?php echo htmlspecialchars($search) ?>"
                        placeholder="Search alumni..." 
                        class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl px-4 py-2 pl-10 text-white placeholder-rose-200 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 w-64"
                    >
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-rose-200"></i>
                    </div>
                    <button type="submit" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 hover:border-white/50 text-white px-6 py-2 rounded-2xl font-semibold transition-all duration-300 flex items-center gap-2">
                    <i class="fas fa-filter"></i>
                    Search
                    </button>
                </form>
                </div>
            </div>

            <!-- Table Content -->
            <div class="px-8 py-8">
                <div class="overflow-hidden rounded-2xl border-2 border-rose-100 shadow-sm">
                <table id="alumniTable" class="min-w-full divide-y divide-rose-100">
                    <thead class="bg-gradient-to-r from-rose-50 to-red-50">
                    <tr>
                        <th class="px-6 py-4 text-sm font-bold text-rose-700 text-center border-r border-rose-100">
                        <div class="flex items-center justify-center gap-2">
                            <i class="fas fa-hashtag text-red-600"></i>
                        </div>
                        </th>
                        <th class="px-6 py-4 text-sm font-bold text-rose-700 text-center border-r border-rose-100">
                        <div class="flex items-center justify-center gap-2">
                            <i class="fas fa-user-circle text-red-600"></i>
                            Profile
                        </div>
                        </th>
                        <th class="px-6 py-4 text-sm font-bold text-rose-700 text-left border-r border-rose-100">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-id-card text-red-600"></i>
                            Full Name
                        </div>
                        </th>
                        <th class="px-6 py-4 text-sm font-bold text-rose-700 text-left border-r border-rose-100">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-graduation-cap text-red-600"></i>
                            Course Graduated
                        </div>
                        </th>
                        <th class="px-6 py-4 text-sm font-bold text-rose-700 text-center border-r border-rose-100">
                        <div class="flex items-center justify-center gap-2">
                            <i class="fas fa-check-shield text-red-600"></i>
                            Status
                        </div>
                        </th>
                        <th class="px-6 py-4 text-sm font-bold text-rose-700 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <i class="fas fa-cogs text-red-600"></i>
                            Actions
                        </div>
                        </th>
                    </tr>
                    </thead>
                   <tbody class="bg-white divide-y divide-rose-50">
                            <?php 
                            $i = $start+1;
                            while($row=$alumni->fetch_assoc()):
                            ?>
                            <tr class="hover:bg-gradient-to-r hover:from-red-25 hover:to-pink-25 transition-all duration-300 group">
                                <td class="px-6 py-5 text-center border-r border-rose-50">
                                    <span class="inline-flex items-center justify-center w-10 h-10 bg-rose-100 text-rose-700 font-bold text-sm rounded-2xl group-hover:bg-red-100 group-hover:text-red-700 transition-all duration-300">
                                        <?php echo $i++ ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center border-r border-rose-50">
                                    <div class="flex justify-center">
                                        <div class="relative group/avatar">
                                            <img 
                                                src="assets/uploads/<?php echo $row['avatar'] ?>" 
                                                class="w-16 h-16 rounded-2xl border-3 border-rose-200 object-cover shadow-sm group-hover/avatar:shadow-lg group-hover/avatar:scale-105 group-hover/avatar:border-red-300 transition-all duration-300" 
                                                alt="<?php echo ucwords($row['name']) ?>"
                                            >
                                            <div class="absolute inset-0 bg-red-600/10 rounded-2xl opacity-0 group-hover/avatar:opacity-100 transition-opacity duration-300"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 border-r border-rose-50">
                                    <div class="font-semibold text-rose-800 text-base group-hover:text-red-800 transition-colors duration-300">
                                        <?php echo ucwords($row['name']) ?>
                                    </div>
                                    <div class="text-rose-500 text-sm mt-1">Alumni ID: #<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></div>
                                </td>
                                <td class="px-6 py-5 border-r border-rose-50">
                                    <div class="flex items-center gap-3">
                                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                        <span class="font-medium text-rose-700 group-hover:text-red-700 transition-colors duration-300">
                                            <?php echo $row['course'] ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center border-r border-rose-50">
                                <!-- <select 
                                    class="px-4 py-2 rounded-2xl text-sm font-semibold border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-100 bg-white text-rose-800"
                                    onchange="updateAlumniStatus(this.dataset.id, this.value)"
                                    data-id="<?php echo $row['id']; ?>"
                                    style="min-width:120px"
                                >
                                    <option value="1" <?php if($row['status']==1) echo 'selected'; ?>>Verified</option>
                                    <option value="0" <?php if($row['status']==0) echo 'selected'; ?>>Unverified</option>
                                </select> -->
                                <span id="statusLabel<?php echo $row['id']; ?>" class="ml-2">
                                    <?php if($row['status'] == 1): ?>
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-sm font-semibold bg-green-100 text-green-800 border border-green-200">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                        Verified
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-sm font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fas fa-exclamation-circle text-amber-600"></i>
                                        Unverified
                                    </span>
                                    <?php endif; ?>
                                </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="flex justify-center gap-3">
                                        <button 
                                            class="bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white px-5 py-2 rounded-xl shadow-md hover:shadow-lg text-sm font-semibold transform hover:scale-105 transition-all duration-200 flex items-center gap-2" 
                                            type="button" 
                                            onclick="viewAlumniModal(<?php echo $row['id'] ?>)"
                                        >
                                            <i class="fas fa-eye text-xs"></i>
                                            View Profile
                                        </button>
                                        <button 
                                            class="bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white px-5 py-2 rounded-xl shadow-md hover:shadow-lg text-sm font-semibold transform hover:scale-105 transition-all duration-200 flex items-center gap-2 delete_alumni" 
                                            type="button" 
                                            data-id="<?php echo $row['id'] ?>"
                                        >
                                            <i class="fas fa-trash text-xs"></i>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <!-- Pagination -->
                <?php if($last_page > 1): ?>
                <div class="flex items-center justify-between mt-8">
                <div class="text-rose-600">
                    Showing <span class="font-semibold text-rose-800"><?php echo $start+1 ?> to <?php echo min($start+$per_page, $total) ?></span> of <span class="font-semibold text-rose-800"><?php echo $total ?></span> results
                </div>
                <div class="flex items-center gap-2">
                    <?php
                    $page_links = [];
                    $adjacency = 2;
                    $first = max(1, $page-$adjacency);
                    $last = min($last_page, $page+$adjacency);
                    if($page > 1) {
                        $params = $_GET; $params['page'] = $page-1;
                        echo '<a href="?'.http_build_query($params).'" class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-600 rounded-xl transition-colors duration-200 flex items-center gap-2"><i class="fas fa-chevron-left"></i> Previous</a>';
                    }
                    for($p=$first;$p<=$last;$p++) {
                        $params = $_GET; $params['page'] = $p;
                        if ($p == $page) {
                            echo '<span class="px-4 py-2 bg-red-600 text-white rounded-xl font-semibold">'.$p.'</span>';
                        } else {
                            echo '<a href="?'.http_build_query($params).'" class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-600 rounded-xl transition-colors duration-200">'.$p.'</a>';
                        }
                    }
                    if($page < $last_page) {
                        $params = $_GET; $params['page'] = $page+1;
                        echo '<a href="?'.http_build_query($params).'" class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-600 rounded-xl transition-colors duration-200 flex items-center gap-2">Next <i class="fas fa-chevron-right"></i></a>';
                    }
                    ?>
                </div>
                </div>
                <?php endif; ?>
            </div>
            </div>
        </div>

        <!-- Alumni View Modal -->
        <div id="viewAlumniModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-2xl border border-red-200 px-8 py-6 w-full max-w-2xl flex flex-col animate-fade-in relative">
            <button onclick="closeViewAlumniModal()" class="absolute top-3 right-3 text-gray-400 hover:text-red-600 text-2xl">&times;</button>
            <h3 class="text-2xl font-bold text-red-700 mb-4 flex items-center gap-2">
            <i class="fas fa-user"></i> Alumni Details
            </h3>
            <div id="viewAlumniContent" class="text-gray-700">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-red-600 text-3xl"></i>
                <div class="mt-2 text-red-600 font-semibold">Loading...</div>
            </div>
            </div>
        </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteAlumniModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-2xl border border-red-200 px-8 py-6 flex flex-col items-center animate-fade-in">
            <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4 animate-pulse"></i>
            <h3 class="text-2xl font-bold text-red-700 mb-2">Delete Alumni?</h3>
            <div class="text-lg text-gray-700 mb-3 text-center">
            Are you sure you want to delete this alumni? This action cannot be undone.
            </div>
            <div class="flex gap-4 mt-4">
            <button onclick="closeDeleteAlumniModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-6 py-2 rounded-lg shadow">Cancel</button>
            <button id="deleteAlumniConfirmBtn" class="bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold px-6 py-2 rounded-lg shadow">Delete</button>
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
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
         // Status update AJAX
        function updateAlumniStatus(id, status) {
            $.ajax({
                url: '',
                method: 'POST',
                data: {update_status: 1, id: id, status: status},
                beforeSend: function() {
                    // Optionally show spinner or loading state
                },
                success: function(resp) {
                    if (resp == "1") {
                        // Update the label next to dropdown
                        let label = '';
                        if (status == "1") {
                            label = `<span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-sm font-semibold bg-green-100 text-green-800 border border-green-200">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                        Verified
                                    </span>`;
                        } else {
                            label = `<span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-sm font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fas fa-exclamation-circle text-amber-600"></i>
                                        Unverified
                                    </span>`;
                        }
                        $("#statusLabel" + id).html(label);
                        showModal("Status updated successfully!");
                    } else {
                        showModal("Error updating status.");
                    }
                },
                error: function() {
                    showModal("Error updating status.");
                }
            });
        }



        function viewAlumniModal(id) {
            document.getElementById('viewAlumniModal').classList.remove('hidden');
            var contentDiv = document.getElementById('viewAlumniContent');
            contentDiv.innerHTML = `<div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-primary-600 text-3xl"></i>
                <div class="mt-2 text-primary-600 font-semibold">Loading...</div>
            </div>`;
            $.ajax({
                url: 'view_alumni.php?id=' + id,
                method: 'GET',
                success: function(data) {
                contentDiv.innerHTML = data;
                },
                error: function() {
                contentDiv.innerHTML = '<div class="text-center text-red-600">Failed to load alumni details.</div>';
                }
            });
            }
            function closeViewAlumniModal() {
            document.getElementById('viewAlumniModal').classList.add('hidden');
            }




        $(document).ready(function(){
            // Initialize DataTable for the alumni table and store instance for later use
            try{
                if(window.jQuery && $.fn.dataTable){
                    window.alumniTable = $('#alumniTable').DataTable();
                }
            }catch(e){
                // ignore initialization errors, fallback to DOM operations
                console.warn('DataTable init failed for #alumniTable', e);
            }
        })

        $('.view_alumni').click(function(){
            uni_modal("Bio","view_alumni.php?id="+$(this).attr('data-id'),'mid-large')
        })

        // For delete alumni confirmation modal
        // Use delegated handler so dynamically added delete buttons or multiple delete triggers work
        let deleteAlumniId = null;
        document.addEventListener('click', function(e){
            var target = e.target;
            // climb up in case the click was on an icon or inner element
            while(target && target !== document) {
                if(target.classList && target.classList.contains('delete_alumni')) {
                    e.preventDefault();
                    deleteAlumniId = target.getAttribute('data-id');
                    // show modal
                    var modal = document.getElementById('deleteAlumniModal');
                    if(modal) modal.classList.remove('hidden');
                    return;
                }
                target = target.parentNode;
            }
        });

        function closeDeleteAlumniModal() {
            var modal = document.getElementById('deleteAlumniModal');
            if(modal) modal.classList.add('hidden');
            deleteAlumniId = null;
        }

        // Centralized delete function so any confirm button can call it
        var isDeleting = false;
        var processedDeletes = {};
        function deleteAlumni(id) {
            if(!id) return;
            if(isDeleting) return; // guard against duplicate requests
            isDeleting = true;
            // disable confirm buttons
            document.querySelectorAll('#deleteAlumniConfirmBtn').forEach(function(bb){ bb.disabled = true; });
            console.debug('[deleteAlumni] start, id=', id);
            if(typeof start_load === 'function') start_load();
            // use jQuery AJAX for consistency with the rest of the file
            $.ajax({
                url: 'ajax.php?action=delete_alumni',
                method: 'POST',
                dataType: 'json',
                data: {id: id},
                success: function(data){
                    console.debug('[deleteAlumni] success response for id=', id, data);
                    closeDeleteAlumniModal();
                    // If we've already processed a successful delete for this id, ignore further responses
                    if(processedDeletes[id]){
                        return;
                    }
                    // data is expected to be parsed JSON
                    if(data && data.success === 1){
                        processedDeletes[id] = true;
                        showModal('Data successfully deleted');
                        var removed = false;
                        // 1) Try DataTables API by scanning nodes for matching data-id on delete button
                        try{
                            if(window.alumniTable && typeof window.alumniTable.rows === 'function'){
                                var rows = window.alumniTable.rows(function(idx, data, node){
                                    try{
                                        var btn = node.querySelector && node.querySelector('button.delete_alumni');
                                        if(btn) return String(btn.getAttribute('data-id')) === String(id);
                                    }catch(e){}
                                    return false;
                                });
                                if(rows && rows.count && rows.count() > 0){
                                    console.debug('[deleteAlumni] removing via DataTable API, id=', id, 'rowsCount=', rows.count());
                                    rows.remove().draw(false);
                                    removed = true;
                                }
                            }
                        }catch(e){
                            console.warn('Failed to remove row via DataTable API', e);
                        }
                        // 2) DOM fallback: remove any matching delete buttons' rows
                        if(!removed){
                            try{
                                var nodes = document.querySelectorAll('button.delete_alumni[data-id="'+id+'"], .delete_alumni[data-id="'+id+'"]');
                                    if(nodes && nodes.length){
                                        console.debug('[deleteAlumni] DOM nodes found for id=', id, 'count=', nodes.length);
                                        nodes.forEach(function(n){
                                            var tr = n.closest && n.closest('tr');
                                            if(tr){ tr.remove(); removed = true; }
                                        })
                                    }
                            }catch(e){ /* ignore */ }
                        }
                        // 3) Content fallback: search for 'Alumni ID: #' text in table rows (padding to 4 digits)
                        if(!removed){
                            try{
                                var padded = String(id).padStart(4, '0');
                                var trs = document.querySelectorAll('#alumniTable tbody tr');
                                trs.forEach(function(tr){
                                    if(removed) return;
                                    if(tr.innerText && tr.innerText.indexOf('Alumni ID: #'+padded) !== -1){
                                        console.debug('[deleteAlumni] removing via content match, id=', id, 'padded=', padded);
                                        tr.remove(); removed = true;
                                    }
                                })
                            }catch(e){}
                        }
                        // 4) Last resort: if nothing removed, reload to reflect server state
                        if(!removed){
                            console.warn('Could not find row to remove for deleted id='+id+', reloading as fallback');
                            location.reload();
                        }
                    } else {
                        var msg = 'Failed to delete.';
                        if(data && data.error) msg = data.error;
                        showModal(msg);
                    }
                },
                error: function(xhr){
                    closeDeleteAlumniModal();
                    // If this id has already been processed successfully, ignore this error
                    if(processedDeletes[id]) return;
                    var msg = 'Sucessfully deleted.';
                    if(xhr && xhr.responseText) {
                        try{ var parsed = JSON.parse(xhr.responseText); if(parsed.error) msg = parsed.error; }catch(e){}
                    }
                    showModal(msg);
                },
                complete: function(){
                    // re-enable confirm buttons and clear deleting flag
                    document.querySelectorAll('#deleteAlumniConfirmBtn').forEach(function(bb){ bb.disabled = false; });
                    isDeleting = false;
                }
            })
        }

        // Attach click listeners to all Delete confirm buttons (handles duplicate modals)
        function attachDeleteConfirmHandlers(){
            var btns = document.querySelectorAll('#deleteAlumniConfirmBtn');
            btns.forEach(function(b){
                // avoid adding multiple listeners
                if(b._deleteHandlerAttached) return;
                b._deleteHandlerAttached = true;
                b.addEventListener('click', function(e){
                    // disable all confirm buttons to prevent double-clicks or duplicate modals triggering multiple requests
                    document.querySelectorAll('#deleteAlumniConfirmBtn').forEach(function(bb){ bb.disabled = true; });
                    if(deleteAlumniId) deleteAlumni(deleteAlumniId);
                });
            });
        }
        attachDeleteConfirmHandlers();

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

        function showModal(msg) {
            document.getElementById('successMessage').textContent = msg;
            document.getElementById('successModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('successModal').classList.add('hidden');
        }
    </script>
</body>
</html>