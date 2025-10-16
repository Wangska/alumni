<?php
// Session already started in index.php, don't start again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// Pagination Logic
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count total jobs for pagination
$total_jobs = $conn->query("SELECT COUNT(*) as cnt FROM careers")->fetch_assoc()['cnt'];
$total_pages = ceil($total_jobs / $limit);

// Handle AJAX status update (keep if needed for jobs)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $new_status = intval($_POST['status']);
    $res = $conn->query("UPDATE careers SET status=$new_status WHERE id=$id");
    echo $res ? "1" : "0";
    exit;
}

// Add or update job
$response = ['success' => false, 'msg' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company'], $_POST['title'], $_POST['location'], $_POST['description'])) {
    $id = isset($_POST['id']) && $_POST['id'] ? intval($_POST['id']) : 0;
    $company = $_POST['company'];
    $title = $_POST['title'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $user_id = $_SESSION['login_id'] ?? 1;

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE careers SET company=?, job_title=?, location=?, description=? WHERE id=?");
        $stmt->bind_param("ssssi", $company, $title, $location, $description, $id);
        $res = $stmt->execute();
        $response['success'] = $res;
        $response['msg'] = $res ? 'Job updated successfully.' : 'Failed to update.';
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO careers (company, job_title, location, description, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $company, $title, $location, $description, $user_id);
        $res = $stmt->execute();
        $response['success'] = $res;
        $response['msg'] = $res ? 'Job added successfully.' : 'Failed to add.';
        $stmt->close();
    }
    echo json_encode($response);
    exit;
}

// If editing, get the job data
$job = null;
if (isset($_GET['id']) && $_GET['id']) {
    $id = intval($_GET['id']);
    $qry = $conn->query("SELECT * FROM careers WHERE id = $id");
    $job = $qry ? $qry->fetch_assoc() : null;
}

// Get jobs for the current page
$jobs = $conn->query("SELECT c.*,u.name FROM careers c INNER JOIN users u ON u.id = c.user_id ORDER BY id DESC LIMIT $limit OFFSET $offset");

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
            <a href="alumni.php" class="nav-item nav-events flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-user-friends"></i> 
                <span>Alumni List</span>
            </a>
            <a href="jobs.php" class="nav-item nav-home active flex items-center gap-3 px-4 py-3 rounded-lg text-primary-700 bg-gradient-to-r from-primary-100 to-primary-50 font-medium hover:bg-primary-200 transition">
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


    <main class="ml-0 md:ml-64 pt-10 min-h-screen">
        <div class="w-full px-4 md:px-8 lg:px-12">
            <!-- Page Header -->
            <div class="mb-10">
                <div class="flex items-center gap-5 mb-4">
                    <div class="w-16 h-16 bg-gradient-to-r from-primary-600 to-rose-600 rounded-3xl flex items-center justify-center shadow-lg shadow-primary-100">
                        <i class="fas fa-briefcase text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-primary-700">Jobs List</h1>
                        <p class="text-primary-400 mt-1">Current Career Opportunities</p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button id="new_job_btn" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-xl shadow font-semibold flex items-center gap-2 transition-all" type="button">
                        <i class="fa fa-plus"></i> New Job
                    </button>
                </div>
            </div>

            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-primary-100 hover:shadow-2xl transition-all duration-500 mb-8">
                <div class="px-4 md:px-8 py-8">
                    <div class="overflow-x-auto rounded-2xl border-2 border-primary-100 shadow-sm">
                        <table class="min-w-full divide-y divide-primary-50" id="jobsTable">
                            <thead class="bg-gradient-to-r from-primary-100 to-primary-50">
                                <tr>
                                    <th class="px-4 md:px-6 py-4 text-sm font-bold text-primary-700 text-center border-r border-primary-100">#</th>
                                    <th class="px-4 md:px-6 py-4 text-sm font-bold text-primary-700 border-r border-primary-100">Company</th>
                                    <th class="px-4 md:px-6 py-4 text-sm font-bold text-primary-700 border-r border-primary-100">Job Title</th>
                                    <th class="px-4 md:px-6 py-4 text-sm font-bold text-primary-700 border-r border-primary-100">Location</th>
                                    <th class="px-4 md:px-6 py-4 text-sm font-bold text-primary-700 border-r border-primary-100">Description</th>
                                    <th class="px-4 md:px-6 py-4 text-sm font-bold text-primary-700 border-r border-primary-100">Posted By</th>
                                    <th class="px-4 md:px-6 py-4 text-sm font-bold text-primary-700 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-primary-50">
                                <?php 
                                $i = $offset + 1;
                                while($row = $jobs->fetch_assoc()):
                                ?>
                                <tr class="hover:bg-gradient-to-r hover:from-primary-100 hover:to-rose-50 transition-all duration-300 group">
                                    <td class="px-4 md:px-6 py-5 text-center border-r border-primary-50">
                                        <span class="inline-flex items-center justify-center w-10 h-10 bg-primary-100 text-primary-700 font-bold text-sm rounded-2xl group-hover:bg-primary-200 group-hover:text-primary-800 transition-all duration-300">
                                            <?php echo $i++ ?>
                                        </span>
                                    </td>
                                    <td class="px-4 md:px-6 py-5 border-r border-primary-50">
                                        <p class="font-semibold text-primary-700"><?php echo ucwords($row['company']) ?></p>
                                    </td>
                                    <td class="px-4 md:px-6 py-5 border-r border-primary-50">
                                        <p class="font-semibold text-primary-700"><?php echo ucwords($row['job_title']) ?></p>
                                    </td>
                                    <td class="px-4 md:px-6 py-5 border-r border-primary-50">
                                        <p class="text-primary-700"><?php echo htmlspecialchars($row['location']) ?></p>
                                    </td>
                                    <td class="px-4 md:px-6 py-5 border-r border-primary-50 max-w-xs md:max-w-md truncate">
                                        <div class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">
                                            <?php 
                                            $desc = strip_tags($row['description']);
                                            if (strlen($desc) > 120) {
                                                echo substr($desc, 0, 120) . '...';
                                            } else {
                                                echo $desc;
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-4 md:px-6 py-5 border-r border-primary-50">
                                        <p class="font-semibold text-primary-700"><?php echo ucwords($row['name']) ?></p>
                                    </td>
                                    <td class="px-4 md:px-6 py-5 text-center">
                                        <div class="flex justify-center gap-2">
                                            <button class="bg-gradient-to-r from-primary-400 to-primary-500 hover:from-primary-500 hover:to-primary-600 text-white px-3 md:px-4 py-2 rounded-xl shadow-md text-xs font-semibold transform hover:scale-105 transition-all duration-200 edit_job_btn" type="button" data-id="<?php echo $row['id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white px-3 md:px-4 py-2 rounded-xl shadow-md text-xs font-semibold transform hover:scale-105 transition-all duration-200 delete_job_btn" type="button" data-id="<?php echo $row['id'] ?>">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="w-full flex justify-center items-center py-6 bg-white border-t border-primary-100">
                    <nav class="inline-flex -space-x-px text-sm">
                        <?php
                        $adjacents = 2;
                        $start = max(1, $page - $adjacents);
                        $end = min($total_pages, $page + $adjacents);
                        ?>
                        <a href="?page=1" class="px-3 py-2 rounded-l-lg border border-primary-200 bg-primary-50 hover:bg-primary-100 text-primary-700 font-semibold <?php if($page == 1) echo 'opacity-50 cursor-not-allowed'; ?>" <?php if($page == 1) echo 'tabindex="-1"'; ?>>
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?page=<?php echo max(1, $page-1); ?>" class="px-3 py-2 border border-primary-200 bg-primary-50 hover:bg-primary-100 text-primary-700 font-semibold <?php if($page == 1) echo 'opacity-50 cursor-not-allowed'; ?>" <?php if($page == 1) echo 'tabindex="-1"'; ?>>
                            <i class="fas fa-angle-left"></i>
                        </a>
                        <?php for($i = $start; $i <= $end; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="px-3 py-2 border border-primary-200 <?php echo $i==$page ? 'bg-primary-600 text-white font-bold' : 'bg-primary-50 hover:bg-primary-100 text-primary-700 font-semibold'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <a href="?page=<?php echo min($total_pages, $page+1); ?>" class="px-3 py-2 border border-primary-200 bg-primary-50 hover:bg-primary-100 text-primary-700 font-semibold <?php if($page == $total_pages) echo 'opacity-50 cursor-not-allowed'; ?>" <?php if($page == $total_pages) echo 'tabindex="-1"'; ?>>
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?page=<?php echo $total_pages; ?>" class="px-3 py-2 rounded-r-lg border border-primary-200 bg-primary-50 hover:bg-primary-100 text-primary-700 font-semibold <?php if($page == $total_pages) echo 'opacity-50 cursor-not-allowed'; ?>" <?php if($page == $total_pages) echo 'tabindex="-1"'; ?>>
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </main>

<!-- Modal for Add/Edit Job -->
    <div id="jobModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-2xl border border-primary-200 px-8 py-6 w-full max-w-lg animate-fade-in">
            <form id="manage-career-form" class="space-y-6">
                <input type="hidden" name="id" id="job_id" value="">
                <div>
                    <label class="block text-sm font-medium text-primary-700 mb-1" for="company">Company</label>
                    <input type="text" id="company" name="company" class="w-full px-4 py-2 rounded-xl border border-primary-200 focus:ring-primary-400 focus:border-primary-400 transition-all text-primary-900 bg-primary-50" value="" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-primary-700 mb-1" for="title">Job Title</label>
                    <input type="text" id="title" name="title" class="w-full px-4 py-2 rounded-xl border border-primary-200 focus:ring-primary-400 focus:border-primary-400 transition-all text-primary-900 bg-primary-50" value="" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-primary-700 mb-1" for="location">Location</label>
                    <input type="text" id="location" name="location" class="w-full px-4 py-2 rounded-xl border border-primary-200 focus:ring-primary-400 focus:border-primary-400 transition-all text-primary-900 bg-primary-50" value="" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-primary-700 mb-1" for="description">Description</label>
                    <textarea id="description" name="description" class="w-full min-h-[120px] px-4 py-2 rounded-xl border border-primary-200 focus:ring-primary-400 focus:border-primary-400 transition-all text-primary-900 bg-primary-50"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" class="px-5 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold transition-all" onclick="closeJobModal()">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold transition-all">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Success Modal (as before) -->
    <div id="successModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
      <div class="bg-white rounded-2xl shadow-2xl border border-primary-200 px-8 py-6 flex flex-col items-center animate-fade-in">
        <i class="fas fa-check-circle text-5xl text-primary-500 mb-4 animate-pulse"></i>
        <h3 class="text-2xl font-bold text-primary-700 mb-2">Success</h3>
        <div id="successMessage" class="text-lg text-gray-700 mb-3"></div>
        <button onclick="closeModal()" class="bg-primary-600 hover:bg-primary-700 text-white font-bold px-6 py-2 rounded-lg shadow">Close</button>
      </div>
    </div>

    <!-- Delete Confirmation Modal (as before) -->

    <div id="deleteJobModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-2xl border border-red-200 px-8 py-6 flex flex-col items-center animate-fade-in w-full max-w-md">
            <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4 animate-pulse"></i>
            <h3 class="text-2xl font-bold text-red-700 mb-2">Delete Job?</h3>
            <div class="text-lg text-gray-700 mb-3 text-center">
                Are you sure you want to delete this job post? This action cannot be undone.
            </div>
            <div class="flex gap-4 mt-4">
                <button onclick="closeDeleteJobModal()" 
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-6 py-2 rounded-lg shadow">
                    Cancel
                </button>
                <button id="deleteJobConfirmBtn" 
                    class="bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold px-6 py-2 rounded-lg shadow">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {

            // Add New Job
            $('#new_job_btn').click(function () {
                openJobModal();
            });

            // Edit Job
            $('.edit_job_btn').click(function (e) {
                e.preventDefault(); // <-- Prevent default action!
                var id = $(this).data('id');
                $.ajax({
                    url: 'ajax.php', // Use a dedicated AJAX endpoint
                    method: 'POST',
                    data: { action: 'get_career', id: id },
                    dataType: 'json',
                    success: function(data){
                        if (data) {
                            $('#job_id').val(data.id);
                            $('#company').val(data.company);
                            $('#title').val(data.job_title);
                            $('#location').val(data.location);
                            $('#description').val(data.description);
                            $('#jobModal').removeClass('hidden');
                        }
                    },
                    error: function(){
                        alert('Failed to fetch job data.');
                    }
                });
            });

            // Delete Job
            $('.delete_job_btn').click(function () {
                var id = $(this).data('id');
                openDeleteJobModal(id);
            });
        });

        function openJobModal(id = null) {
            $('#manage-career-form')[0].reset();
            $('#job_id').val('');
            $('#company').val('');
            $('#title').val('');
            $('#location').val('');
            $('#description').val('');
            $('#jobModal').removeClass('hidden');
        }

        function closeJobModal() {
            $('#jobModal').addClass('hidden');
        }

        // Form submit AJAX for add/edit
        $('#manage-career-form').submit(function(e){
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: '', // same page
                method: 'POST',
                data: {
                    id: $('#job_id').val(),
                    company: $('#company').val(),
                    title: $('#title').val(),
                    location: $('#location').val(),
                    description: $('#description').val()
                },
                dataType: 'json',
                success: function(resp){
                    if(resp.success){
                        showModal(resp.msg);
                        setTimeout(function(){
                            location.reload();
                        }, 1500);
                    } else {
                        alert(resp.msg);
                    }
                    closeJobModal();
                },
                error: function(){
                    alert('An error occurred');
                }
            });
        });

        // Success Modal logic
        function showModal(msg) {
            document.getElementById('successMessage').textContent = msg;
            document.getElementById('successModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('successModal').classList.add('hidden');
        }

        // Dropdown toggle logic (unchanged)
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

        // ... delete modal logic ...
        let deleteJobId = null;
        function openDeleteJobModal(id) {
            deleteJobId = id;
            $('#deleteJobModal').removeClass('hidden');
        }
        function closeDeleteJobModal() {
            deleteJobId = null;
            $('#deleteJobModal').addClass('hidden');
        }
        $('#deleteJobConfirmBtn').click(function () {
            if (deleteJobId) {
                delete_job(deleteJobId);
                closeDeleteJobModal();
            }
        });
        function delete_job(id){
            $.ajax({
                url:'ajax.php?action=delete_career',
                method:'POST',
                data:{id:id},
                success:function(resp){
                    if(resp==1){
                        showModal("Data successfully deleted");
                        setTimeout(function(){
                            location.reload()
                        },1500)
                    }
                }
            })
        }
    </script>
</body>
</html>