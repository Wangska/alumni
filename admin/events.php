<?php
// Session already started in index.php, don't start again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// Handle AJAX for add/edit event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Insert or update event
    if ($_POST['action'] === 'save') {
        $id = isset($_POST['id']) && $_POST['id'] ? intval($_POST['id']) : 0;
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $schedule = $_POST['schedule'];
        $banner = '';

        // Handle banner upload
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] == UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($ext, $allowed)) {
                $banner_file = uniqid('banner_').'.'.$ext;
                $upload_dir = 'assets/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                if (move_uploaded_file($_FILES['banner']['tmp_name'], $upload_dir.$banner_file)) {
                    $banner = $banner_file;
                }
            }
        }

        if ($id) {
            // Edit
            $sql = "UPDATE events SET title=?, content=?, schedule=?";
            $params = [$title, $content, $schedule];
            $types = "sss";
            if ($banner) {
                $sql .= ", banner=?";
                $params[] = $banner;
                $types .= "s";
            }
            $sql .= " WHERE id=?";
            $params[] = $id;
            $types .= "i";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['status'=>'success','msg'=>'Event updated successfully!']);
        } else {
            // Add
            $stmt = $conn->prepare("INSERT INTO events (title, content, schedule, banner, date_created) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $title, $content, $schedule, $banner);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['status'=>'success','msg'=>'Event added successfully!']);
        }
        exit;
    }

    // Delete event
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM events WHERE id=$id");
        echo json_encode(['status'=>'success','msg'=>'Event deleted successfully!']);
        exit;
    }

    // Fetch event for editing/viewing + committed users
    if ($_POST['action'] === 'get' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $event = $conn->query("SELECT * FROM events WHERE id=$id");
        $result = [];
        if ($event && $event->num_rows > 0) {
            $result = $event->fetch_assoc();
            // Get committed users
            $commits = $conn->query("SELECT ec.*, u.name, u.username FROM event_commits ec INNER JOIN users u ON u.id = ec.user_id WHERE ec.event_id = $id");
            $result['committed_users'] = [];
            while ($row = $commits->fetch_assoc()) {
                $result['committed_users'][] = $row;
            }
        }
        echo json_encode($result);
        exit;
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
            <a href="events.php" class="nav-item nav-home active flex items-center gap-3 px-4 py-3 rounded-lg text-primary-700 bg-gradient-to-r from-primary-100 to-primary-50 font-medium hover:bg-primary-200 transition">
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
        <div class="w-full px-2 md:px-8 lg:px-12">
            <div class="mb-10">
                <div class="flex items-center gap-6 mb-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-600 to-rose-600 rounded-full flex items-center justify-center shadow-lg border-4 border-primary-100">
                        <i class="fas fa-calendar-alt text-white text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-extrabold text-primary-700 drop-shadow">Events</h1>
                        <p class="text-primary-400 mt-1 font-medium">Upcoming and Past Alumni Events</p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button id="new_event" class="bg-primary-600 hover:bg-primary-700 text-white px-7 py-3 rounded-2xl shadow-lg font-semibold flex items-center gap-2 transition-all duration-200 text-base active:scale-95 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2">
                        <i class="fa fa-plus"></i> New Entry
                    </button>
                </div>
            </div>

            <!-- Events Table -->
            <div class="bg-white shadow-2xl rounded-3xl overflow-hidden border border-primary-100 hover:shadow-3xl transition-all duration-500 mb-10">
                <div class="px-2 md:px-8 py-8">
                    <div class="overflow-x-auto rounded-2xl border-2 border-primary-100 shadow-md">
                        <table class="min-w-full divide-y divide-primary-100 text-sm" id="eventsTable">
                            <thead class="bg-gradient-to-r from-primary-100 to-primary-50">
                                <tr>
                                    <th class="px-2 md:px-6 py-4 text-xs md:text-sm font-bold text-primary-700 text-center border-r border-primary-100">#</th>
                                    <th class="px-2 md:px-6 py-4 text-xs md:text-sm font-bold text-primary-700 border-r border-primary-100">Schedule</th>
                                    <th class="px-2 md:px-6 py-4 text-xs md:text-sm font-bold text-primary-700 border-r border-primary-100">Title</th>
                                    <th class="px-2 md:px-6 py-4 text-xs md:text-sm font-bold text-primary-700 border-r border-primary-100">Description</th>
                                    <th class="px-2 md:px-6 py-4 text-xs md:text-sm font-bold text-primary-700 border-r border-primary-100">Commited</th>
                                    <th class="px-2 md:px-6 py-4 text-xs md:text-sm font-bold text-primary-700 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-primary-100">
                                <?php 
                                $i = 1;
                                $events = $conn->query("SELECT * FROM events order by unix_timestamp(schedule) desc ");
                                while($row=$events->fetch_assoc()):
                                    $trans = get_html_translation_table(HTML_ENTITIES,ENT_QUOTES);
                                    unset($trans["\""], $trans["<"], $trans[">"], $trans["<h2"]);
                                    $desc = strtr(html_entity_decode($row['content']),$trans);
                                    $desc=str_replace(array("<li>","</li>"), array("",","), $desc);
                                    $commits = $conn->query("SELECT * FROM event_commits where event_id =".$row['id'])->num_rows;
                                ?>
                                <tr class="hover:bg-gradient-to-r hover:from-primary-100 hover:to-rose-50 transition-all duration-200 group">
                                    <td class="px-2 md:px-6 py-5 text-center border-r border-primary-50">
                                        <span class="inline-flex items-center justify-center w-10 h-10 bg-primary-100 text-primary-700 font-bold text-sm rounded-full group-hover:bg-primary-200 group-hover:text-primary-800 transition-all duration-200 shadow">
                                            <?php echo $i++ ?>
                                        </span>
                                    </td>
                                    <td class="px-2 md:px-6 py-5 border-r border-primary-50 align-middle">
                                         <p class="font-semibold text-primary-700"><?php echo date("M d, Y h:i A",strtotime($row['schedule'])) ?></p>
                                    </td>
                                    <td class="px-2 md:px-6 py-5 border-r border-primary-50 align-middle">
                                         <p class="font-semibold text-primary-700"><?php echo ucwords($row['title']) ?></p>
                                    </td>
                                    <td class="px-2 md:px-6 py-5 border-r border-primary-50 align-middle max-w-xs md:max-w-lg">
                                         <div class="text-gray-700 text-xs md:text-sm leading-relaxed whitespace-pre-line truncate"><?php echo strip_tags($desc) ?></div>
                                    </td>
                                    <td class="px-2 md:px-6 py-5 border-r border-primary-50 align-middle text-right">
                                         <span class="inline-block font-semibold text-primary-600 bg-primary-50 rounded-full px-4 py-2 shadow"><?php echo $commits ?></span>
                                    </td>
                                    <td class="px-2 md:px-6 py-5 text-center align-middle">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <button class="bg-gradient-to-r from-primary-400 to-primary-500 hover:from-primary-500 hover:to-primary-600 text-white px-3 md:px-4 py-2 rounded-2xl shadow-md text-xs font-semibold transform hover:scale-105 transition-all duration-200 view_event" type="button" data-id="<?php echo $row['id'] ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="bg-gradient-to-r from-primary-400 to-primary-500 hover:from-primary-500 hover:to-primary-600 text-white px-3 md:px-4 py-2 rounded-2xl shadow-md text-xs font-semibold transform hover:scale-105 transition-all duration-200 edit_event" type="button" data-id="<?php echo $row['id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white px-3 md:px-4 py-2 rounded-2xl shadow-md text-xs font-semibold transform hover:scale-105 transition-all duration-200 delete_event" type="button" data-id="<?php echo $row['id'] ?>">
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
            </div>
            <!-- End Events Table -->

            <!-- Enhanced Add/Edit Event Modal -->
            <div id="eventModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center hidden p-4">
            <div class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl border border-gray-200 w-full max-w-4xl animate-fade-in flex flex-col overflow-hidden max-h-[90vh]">
                
                <!-- Modal Header - Gradient + Decorative Background -->
                <div class="bg-gradient-to-r from-red-600 via-red-700 to-rose-600 px-8 py-8 relative overflow-hidden">
                <!-- Decorative Background Elements -->
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-red-400 rounded-full translate-x-16 -translate-y-16"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-rose-400 rounded-full -translate-x-12 translate-y-12"></div>
                    <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-red-300 rounded-full -translate-y-12 opacity-60"></div>
                </div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-6">
                    <div class="w-20 h-20 bg-red-500/30 backdrop-blur-sm rounded-3xl flex items-center justify-center border border-red-400/40 shadow-lg">
                        <i class="fas fa-calendar-plus text-red-100 text-2xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-white mb-0 leading-tight" id="add_edit_event_title">
                        Add / Edit Event
                    </h2>
                    </div>
                    <button 
                    type="button"
                    onclick="closeEventModal()" 
                    class="w-14 h-14 bg-red-500/30 hover:bg-red-400/40 backdrop-blur-sm rounded-3xl flex items-center justify-center transition-all duration-300 border border-red-400/40 hover:border-red-300/60 group"
                    >
                    <i class="fas fa-times text-red-100 text-xl group-hover:scale-110 transition-transform duration-300"></i>
                    </button>
                </div>
                </div>
                
                <!-- Modal Form Content -->
                <div class="flex-1 overflow-y-auto">
                <form id="manage-event" class="p-8 space-y-8">
                    <input type="hidden" name="id" id="event_id" value="">
                    
                    <!-- Title -->
                    <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
                    <label class="block text-sm font-bold text-red-900 mb-1" for="title">
                        <i class="fas fa-heading mr-2 text-red-600"></i> Event Title
                    </label>
                    <input type="text" id="title" name="title" class="w-full px-4 py-2 rounded-xl border border-red-200 focus:ring-red-400 focus:border-red-400 transition-all text-red-900 bg-white/60" required>
                    </div>
                    
                    <!-- Schedule -->
                    <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
                    <label class="block text-sm font-bold text-red-900 mb-1" for="schedule">
                        <i class="fas fa-clock mr-2 text-red-600"></i> Schedule
                    </label>
                    <input type="datetime-local" id="schedule" name="schedule" class="w-full px-4 py-2 rounded-xl border border-red-200 focus:ring-red-400 focus:border-red-400 transition-all text-red-900 bg-white/60" required>
                    </div>
                    
                    <!-- Description -->
                    <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
                    <label class="block text-sm font-bold text-red-900 mb-1" for="content">
                        <i class="fas fa-align-left mr-2 text-red-600"></i> Description
                    </label>
                    <textarea id="content" name="content" class="w-full min-h-[120px] max-h-60 px-4 py-2 rounded-xl border border-red-200 focus:ring-red-400 focus:border-red-400 transition-all text-red-900 bg-white/60 custom-scrollbar" required></textarea>
                    </div>
                    
                    <!-- Banner Image -->
                    <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
                    <label class="block text-sm font-bold text-red-900 mb-1" for="banner">
                        <i class="fas fa-image mr-2 text-red-600"></i> Banner Image
                    </label>
                    <input type="file" id="banner" name="banner" class="w-full px-4 py-2 rounded-xl border border-red-200 focus:ring-red-400 focus:border-red-400 transition-all text-red-900 bg-white/60">
                    <img id="banner-field" class="mt-4 max-h-48 rounded-2xl border border-red-200 shadow-lg" src="" alt="">
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4">
                    <button type="button" class="px-5 py-2 rounded-xl bg-red-100 hover:bg-red-200 text-red-700 font-semibold border border-red-200 transition-all" onclick="closeEventModal()">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold border border-red-200 transition-all">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                    </div>
                </form>
                </div>
            </div>
            </div>
            <!-- End Modal -->

        </div>
    </main>


        <!-- Delete Confirmation Modal -->
        <div id="deleteEventModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
            <div class="bg-white rounded-2xl shadow-2xl border border-red-200 px-8 py-6 flex flex-col items-center animate-fade-in">
                <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4 animate-pulse"></i>
                <h3 class="text-2xl font-bold text-red-700 mb-2">Delete Event?</h3>
                <div class="text-lg text-gray-700 mb-3 text-center">
                    Are you sure you want to delete this event? This action cannot be undone.
                </div>
                <div class="flex gap-4 mt-4">
                    <button onclick="closeDeleteEventModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-6 py-2 rounded-lg shadow">Cancel</button>
                    <button id="deleteEventConfirmBtn" class="bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold px-6 py-2 rounded-lg shadow">Delete</button>
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

        <!-- Enhanced View Event Modal -->
        <div id="viewEventModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center hidden p-4">
        <div class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl border border-gray-200 w-full max-w-4xl animate-fade-in flex flex-col overflow-hidden max-h-[90vh]">

            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-red-600 via-red-700 to-rose-600 px-8 py-8 relative overflow-hidden">
            <!-- Background decorative elements -->
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 right-0 w-40 h-40 bg-red-400 rounded-full translate-x-16 -translate-y-16"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-rose-400 rounded-full -translate-x-12 translate-y-12"></div>
                <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-red-300 rounded-full -translate-y-12 opacity-60"></div>
            </div>
            
            <div class="flex items-start justify-between relative z-10">
                <div class="flex items-start gap-6 flex-1 pr-4">
                <div class="w-20 h-20 bg-red-500/30 backdrop-blur-sm rounded-3xl flex items-center justify-center border border-red-400/40 shadow-lg flex-shrink-0">
                    <i class="fas fa-calendar-star text-red-100 text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-3xl font-bold text-white mb-3 leading-tight" id="view_event_title">
                    Event Title Loading...
                    </h2>
                    <div class="flex items-center gap-4 text-red-100 text-sm">
                    <div class="flex items-center gap-2 bg-red-500/20 px-4 py-2 rounded-xl border border-red-400/30">
                        <i class="fas fa-clock"></i>
                        <span id="view_event_schedule" class="font-medium">Loading schedule...</span>
                    </div>
                    <div class="flex items-center gap-2 bg-red-500/20 px-4 py-2 rounded-xl border border-red-400/30">
                        <i class="fas fa-users"></i>
                        <span id="event_attendee_count" class="font-medium">0 Attendees</span>
                    </div>
                    </div>
                </div>
                </div>
                
                <button 
                onclick="closeViewEventModal()" 
                class="w-14 h-14 bg-red-500/30 hover:bg-red-400/40 backdrop-blur-sm rounded-3xl flex items-center justify-center transition-all duration-300 border border-red-400/40 hover:border-red-300/60 flex-shrink-0 group"
                >
                <i class="fas fa-times text-red-100 text-xl group-hover:scale-110 transition-transform duration-300"></i>
                </button>
            </div>
            </div>

            <!-- Modal Content -->
            <div class="flex-1 overflow-y-auto">
            <div class="p-8 space-y-8">
                
                <!-- Event Banner Section -->
                <div id="view_event_banner_container" class="hidden">
                <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-image text-red-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-red-900">Event Banner</h3>
                    </div>
                    <div class="relative group">
                    <img 
                        id="view_event_banner" 
                        class="w-full max-h-80 object-cover rounded-2xl border-2 border-red-200 shadow-lg group-hover:shadow-xl transition-all duration-300" 
                        src="" 
                        alt="Event Banner"
                        style="display:none;"
                    >
                    <div class="absolute inset-0 bg-red-600/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                </div>
                </div>
                
                <!-- Event Description -->
                <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-align-left text-red-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-red-900">Event Description</h3>
                </div>
                <div 
                    id="view_event_content" 
                    class="text-red-800 leading-relaxed whitespace-pre-line bg-white/60 rounded-xl p-4 border border-red-200 min-h-[120px] max-h-60 overflow-y-auto custom-scrollbar"
                >
                    Loading event description...
                </div>
                </div>

                

                <!-- Committed Users Section -->
                <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-red-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-red-900">Committed Attendees</h3>
                    </div>
                    <div class="bg-red-100 text-red-700 px-4 py-2 rounded-xl text-sm font-semibold border border-red-200">
                    <i class="fas fa-user-check mr-1"></i>
                    <span id="committed_count">0</span> Committed
                    </div>
                </div>
                
                <div id="view_event_commits_container" class="space-y-3">
                    <!-- Content will be populated by JavaScript -->
                    <div class="text-center py-8">
                    <div class="w-16 h-16 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-spinner fa-spin text-red-400 text-xl"></i>
                    </div>
                    <p class="text-red-600 font-medium">Loading attendees...</p>
                    </div>
                </div>
                </div>
            </div>
            </div>


            
        </div>
        </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        function openEventModal(edit=false, id=null) {
            document.getElementById('eventModal').classList.remove('hidden');
            document.getElementById('manage-event').reset();
            document.getElementById('event_id').value = '';
            document.getElementById('banner-field').src = '';
            document.getElementById('banner-field').style.display = 'none';
            if (edit && id) {
                fetch('events.php', {
                    method: 'POST',
                    body: new URLSearchParams({'action':'get','id':id})
                }).then(res=>res.json()).then(data=>{
                    document.getElementById('event_id').value = data.id;
                    document.getElementById('title').value = data.title;
                    document.getElementById('schedule').value = data.schedule.replace(' ','T');
                    document.getElementById('content').value = data.content;
                    if(data.banner){
                        document.getElementById('banner-field').src = "assets/uploads/"+data.banner;
                        document.getElementById('banner-field').style.display = '';
                    }
                });
            }
        }
        function closeEventModal() {
            document.getElementById('eventModal').classList.add('hidden');
        }

        document.getElementById('new_event').onclick = ()=>openEventModal();

        document.querySelectorAll('.edit_event').forEach(btn=>{
            btn.onclick = ()=>openEventModal(true, btn.dataset.id);
        });

        document.getElementById('manage-event').onsubmit = function(e){
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('action','save');
            fetch('events.php', {
                method: 'POST',
                body: formData
            })
            .then(r=>r.json())
            .then(data=>{
                if(data.status==='success'){
                    showSuccess(data.msg);
                    setTimeout(()=>location.reload(),1200);
                }
            });
        };

        function showSuccess(msg){
            document.getElementById('successMessage').textContent = msg;
            document.getElementById('successModal').classList.remove('hidden');
        }
        function closeModal(){
            document.getElementById('successModal').classList.add('hidden');
        }

        document.querySelectorAll('.delete_event').forEach(btn=>{
            btn.onclick = function(){
                document.getElementById('deleteEventModal').classList.remove('hidden');
                document.getElementById('deleteEventConfirmBtn').onclick = function(){
                    fetch('events.php', {
                        method: 'POST',
                        headers: {'Content-Type':'application/x-www-form-urlencoded'},
                        body: 'action=delete&id='+btn.dataset.id
                    })
                    .then(r=>r.json())
                    .then(data=>{
                        if(data.status==='success'){
                            showSuccess(data.msg);
                            setTimeout(()=>location.reload(),1200);
                        }
                    });
                    closeDeleteEventModal();
                };
            }
        });

        function closeDeleteEventModal(){
            document.getElementById('deleteEventModal').classList.add('hidden');
        }

        // View Event Modal
        document.querySelectorAll('.view_event').forEach(btn => {
            btn.onclick = function() {
                fetch('events.php', {
                    method: 'POST',
                    body: new URLSearchParams({'action': 'get', 'id': btn.dataset.id})
                }).then(res => res.json()).then(data => {
                    document.getElementById('view_event_title').textContent = data.title;
                    document.getElementById('view_event_schedule').textContent = data.schedule ? new Date(data.schedule).toLocaleString() : 'No schedule set';
                    document.getElementById('view_event_content').textContent = data.content;
                    
                    // Handle event banner
                    const bannerContainer = document.getElementById('view_event_banner_container');
                    const bannerImg = document.getElementById('view_event_banner');
                    if (data.banner) {
                        bannerImg.src = "assets/uploads/" + data.banner;
                        bannerImg.style.display = '';
                        bannerContainer.classList.remove('hidden');
                    } else {
                        bannerContainer.classList.add('hidden');
                    }

                    // Show committed users with enhanced styling
                    let container = document.getElementById('view_event_commits_container');
                    let users = data.committed_users || [];
                    
                    // Update attendee count
                    document.getElementById('committed_count').textContent = users.length;
                    document.getElementById('event_attendee_count').textContent = users.length + ' Attendees';
                    
                    if (users.length > 0) {
                        container.innerHTML = `
                            <div class="grid gap-3">
                                ${users.map(u => `
                                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 border border-red-200 shadow-sm hover:shadow-md transition-all duration-300 group">
                                        <div class="flex items-center gap-4">
                                            <div class="w-14 h-14 bg-gradient-to-r from-red-500 to-rose-500 text-white rounded-2xl flex items-center justify-center font-bold text-lg shadow-md group-hover:scale-110 transition-transform duration-300">
                                                ${u.name ? u.name[0].toUpperCase() : '?'}
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-bold text-red-900 text-lg">${u.name}</h4>
                                                <p class="text-red-600 text-sm flex items-center gap-1">
                                                    <i class="fas fa-at text-xs"></i>
                                                    ${u.username}
                                                </p>
                                            </div>
                                            <div class="bg-green-100 text-green-800 px-3 py-1 rounded-xl text-xs font-semibold border border-green-200 flex items-center gap-1">
                                                <i class="fas fa-check-circle"></i>
                                                Committed
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                    } else {
                        container.innerHTML = `
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-user-slash text-red-400 text-xl"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-red-800 mb-2">No Commitments Yet</h4>
                                <p class="text-red-600">Be the first to commit to this exciting event!</p>
                                <button class="mt-4 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white px-6 py-2 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i>
                                    Commit Now
                                </button>
                            </div>
                        `;
                    }

                    document.getElementById('viewEventModal').classList.remove('hidden');
                });
            }
        });
        function closeViewEventModal(){
            document.getElementById('viewEventModal').classList.add('hidden');
        }
        document.getElementById('banner').onchange = function(e){
            let file = e.target.files[0];
            if(file){
                let reader = new FileReader();
                reader.onload = function(ev){
                    document.getElementById('banner-field').src = ev.target.result;
                    document.getElementById('banner-field').style.display = '';
                }
                reader.readAsDataURL(file);
            }
        };
        </script>                                
</body>
</html>