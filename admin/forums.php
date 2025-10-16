<?php
// Session already started in index.php, don't start again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// =========================
// Add a new forum topic
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_topic') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $user_id = $_SESSION['login_id'] ?? 0;

        if ($title && $description && $user_id) {
            $stmt = $conn->prepare("INSERT INTO forum_topics (title, description, user_id, date_created) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("ssi", $title, $description, $user_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['status'=>'success','msg'=>'Topic added!']);
        } else {
            echo json_encode(['status'=>'error','msg'=>'Missing fields or not logged in.']);
        }
        exit;
    }

    // =========================
    // Add a new comment to topic
    // =========================
    if ($_POST['action'] === 'add_comment') {
        $topic_id = intval($_POST['topic_id']);
        $comment = trim($_POST['comment']);
        $user_id = $_SESSION['login_id'] ?? 0;

        if ($comment && $topic_id && $user_id) {
            $stmt = $conn->prepare("INSERT INTO forum_comments (topic_id, comment, user_id, date_created) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("isi", $topic_id, $comment, $user_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['status'=>'success','msg'=>'Comment added!']);
        } else {
            echo json_encode(['status'=>'error','msg'=>'Missing fields or not logged in.']);
        }
        exit;
    }

    // =========================
    // Edit a comment (only by owner)
    // =========================
    if ($_POST['action'] === 'edit_comment') {
        $comment_id = intval($_POST['comment_id']);
        $comment = trim($_POST['comment']);
        $user_id = $_SESSION['login_id'] ?? 0;

        // Check ownership
        $check = $conn->query("SELECT * FROM forum_comments WHERE id=$comment_id AND user_id=$user_id");
        if ($check && $check->num_rows === 1 && $comment) {
            $stmt = $conn->prepare("UPDATE forum_comments SET comment=? WHERE id=?");
            $stmt->bind_param("si", $comment, $comment_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['status'=>'success','msg'=>'Comment updated!']);
        } else {
            echo json_encode(['status'=>'error','msg'=>'Not allowed or missing fields.']);
        }
        exit;
    }

    // =========================
    // Delete a comment (only by owner)
    // =========================
    if ($_POST['action'] === 'delete_comment') {
        $comment_id = intval($_POST['comment_id']);
        $user_id = $_SESSION['login_id'] ?? 0;

        // Check ownership
        $check = $conn->query("SELECT * FROM forum_comments WHERE id=$comment_id AND user_id=$user_id");
        if ($check && $check->num_rows === 1) {
            $conn->query("DELETE FROM forum_comments WHERE id=$comment_id");
            echo json_encode(['status'=>'success','msg'=>'Comment deleted!']);
        } else {
            echo json_encode(['status'=>'error','msg'=>'Not allowed.']);
        }
        exit;
    }

    // =========================
    // Delete a topic (only by owner or admin)
    // =========================
    if ($_POST['action'] === 'delete_topic') {
        $topic_id = intval($_POST['topic_id']);
        $user_id = $_SESSION['login_id'] ?? 0;
        $is_admin = isset($_SESSION['login_type']) && $_SESSION['login_type'] == 1;

        // Check ownership or admin
        $check = $conn->query("SELECT * FROM forum_topics WHERE id=$topic_id AND (user_id=$user_id OR $is_admin)");
        if ($check && $check->num_rows === 1) {
            $conn->query("DELETE FROM forum_topics WHERE id=$topic_id");
            // Also delete associated comments
            $conn->query("DELETE FROM forum_comments WHERE topic_id=$topic_id");
            echo json_encode(['status'=>'success','msg'=>'Topic deleted!']);
        } else {
            echo json_encode(['status'=>'error','msg'=>'Not allowed.']);
        }
        exit;
    }

    // =========================
    // Edit a topic (only by owner)
    // =========================
    if ($_POST['action'] === 'edit_topic') {
        $topic_id = intval($_POST['topic_id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $user_id = $_SESSION['login_id'] ?? 0;

        // Check ownership
        $check = $conn->query("SELECT * FROM forum_topics WHERE id=$topic_id AND user_id=$user_id");
        if ($check && $check->num_rows === 1 && $title && $description) {
            $stmt = $conn->prepare("UPDATE forum_topics SET title=?, description=? WHERE id=?");
            $stmt->bind_param("ssi", $title, $description, $topic_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['status'=>'success','msg'=>'Topic updated!']);
        } else {
            echo json_encode(['status'=>'error','msg'=>'Not allowed or missing fields.']);
        }
        exit;
    }

    // =========================
    // Get topic details
    // =========================
    if ($_POST['action'] === 'get_topic') {
        $topic_id = intval($_POST['topic_id']);
        $res = $conn->query("SELECT t.*, u.name FROM forum_topics t INNER JOIN users u ON u.id = t.user_id WHERE t.id = $topic_id");
        $topic = $res ? $res->fetch_assoc() : [];
        echo json_encode($topic);
        exit;
    }

    // =========================
    // Get comments for topic
    // =========================
    if ($_POST['action'] === 'get_comments' && isset($_POST['topic_id'])) {
        $topic_id = intval($_POST['topic_id']);
        $comments = [];
        $res = $conn->query("SELECT c.*, u.name, u.username FROM forum_comments c INNER JOIN users u ON u.id = c.user_id WHERE c.topic_id = $topic_id ORDER BY c.date_created ASC");
        while ($row = $res->fetch_assoc()) $comments[] = $row;
        echo json_encode($comments);
        exit;
    }
}

// =========================
// Display topics (default GET)
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'get_topics') {
        $topics = [];
        $res = $conn->query("SELECT t.*, u.name FROM forum_topics t INNER JOIN users u ON u.id = t.user_id ORDER BY t.date_created DESC");
        while ($row = $res->fetch_assoc()) $topics[] = $row;
        echo json_encode($topics);
        exit;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'get_comments' && isset($_GET['topic_id'])) {
        $topic_id = intval($_GET['topic_id']);
        $comments = [];
        $res = $conn->query("SELECT c.*, u.name, u.username FROM forum_comments c INNER JOIN users u ON u.id = c.user_id WHERE c.topic_id = $topic_id ORDER BY c.date_created ASC");
        while ($row = $res->fetch_assoc()) $comments[] = $row;
        echo json_encode($comments);
        exit;
    }
}
?>
<!-- ...The rest of your HTML remains unchanged... -->
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
            <a href="forums.php" class="nav-item nav-home active flex items-center gap-3 px-4 py-3 rounded-lg text-primary-700 bg-gradient-to-r from-primary-100 to-primary-50 font-medium hover:bg-primary-200 transition">
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
                        <i class="fas fa-comments text-white text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-extrabold text-primary-700 drop-shadow">Forum List</h1>
                        <p class="text-primary-400 mt-1 font-medium">Manage and View Forum Topics</p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button id="new_forum" class="bg-primary-600 hover:bg-primary-700 text-white px-7 py-3 rounded-2xl shadow-lg font-semibold flex items-center gap-2 transition-all duration-200 text-base active:scale-95 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2">
                        <i class="fa fa-plus"></i> Add New Forum
                    </button>
                </div>
            </div>
            <div class="bg-white shadow-2xl rounded-3xl overflow-hidden border border-primary-100 mb-10">
                <div class="px-2 md:px-8 py-8">
                    <div class="overflow-x-auto rounded-2xl border-2 border-primary-100 shadow-md">
                        <table class="min-w-full divide-y divide-primary-100 text-sm" id="forumsTable">
                            <thead class="bg-gradient-to-r from-primary-100 to-primary-50">
                                <tr>
                                    <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 text-center border-r border-primary-100">#</th>
                                    <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 border-r border-primary-100">Topic</th>
                                    <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 border-r border-primary-100">Description</th>
                                    <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 border-r border-primary-100">Created By</th>
                                    <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 border-r border-primary-100">Comments</th>
                                    <th class="px-2 md:px-6 py-4 text-xs font-bold text-primary-700 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-primary-100">
                                <?php 
                                $i = 1;
                                $Forum = $conn->query("SELECT f.*,u.name from forum_topics f inner join users u on u.id = f.user_id order by f.id desc");
                                while($row=$Forum->fetch_assoc()):
                                    $trans = get_html_translation_table(HTML_ENTITIES,ENT_QUOTES);
                                    unset($trans["\""], $trans["<"], $trans[">"], $trans["<h2"]);
                                    $desc = strtr(html_entity_decode($row['description']),$trans);
                                    $desc = str_replace(array("<li>","</li>"), array("",","), $desc);
                                    $count_comments = $conn->query("SELECT * FROM forum_comments where topic_id = ".$row['id'])->num_rows;
                                ?>
                                <tr class="hover:bg-primary-50 transition duration-150 group">
                                    <td class="text-center px-2 md:px-6 py-4 font-bold text-primary-700 group-hover:text-primary-900"><?php echo $i++ ?></td>
                                    <td class="px-2 md:px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold shadow-inner">
                                                <i class="fas fa-hashtag"></i>
                                            </div>
                                            <span class="font-semibold text-primary-700 group-hover:text-primary-900"><?php echo ucwords($row['title']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-2 md:px-6 py-4 max-w-xs">
                                        <span class="block truncate cursor-pointer" title="<?php echo strip_tags($desc) ?>">
                                            <?php echo strip_tags($desc) ?>
                                        </span>
                                    </td>
                                    <td class="px-2 md:px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-primary-300 to-primary-50 flex items-center justify-center text-primary-700 font-bold shadow">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span class="font-semibold text-primary-700"><?php echo ucwords($row['name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-2 md:px-6 py-4 text-right">
                                        <span class="inline-flex items-center gap-1 bg-primary-100 text-primary-700 px-3 py-1 rounded-2xl font-bold shadow-sm">
                                            <i class="fas fa-comment-dots"></i>
                                            <?php echo number_format($count_comments) ?>
                                        </span>
                                    </td>
                                    <td class="px-2 md:px-6 py-4 text-center">
                                        <div class="flex justify-center items-center gap-2">
                                            <button class="bg-primary-50 hover:bg-primary-200 text-primary-700 border border-primary-200 px-3 py-2 rounded-xl font-semibold text-xs view_forum transition-all duration-150"
                                                type="button" data-id="<?php echo $row['id'] ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="bg-primary-50 hover:bg-primary-200 text-primary-700 border border-primary-200 px-3 py-2 rounded-xl font-semibold text-xs edit_forum transition-all duration-150"
                                                type="button" data-id="<?php echo $row['id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="bg-red-50 hover:bg-red-200 text-red-700 border border-red-200 px-3 py-2 rounded-xl font-semibold text-xs delete_forum transition-all duration-150"
                                                type="button" data-id="<?php echo $row['id'] ?>">
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
        </div>
    </main>

<!-- Enhanced Add Forum Modal -->
<div id="forumModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center hidden p-4">
  <div class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl border border-gray-200 w-full max-w-3xl animate-fade-in flex flex-col overflow-hidden max-h-[90vh]">
    
    <!-- Modal Header - Gradient + Decorative Background -->
    <div class="bg-gradient-to-r from-red-600 via-red-700 to-rose-600 px-8 py-8 relative overflow-hidden">
      <!-- Decorative Background Elements -->
      <div class="absolute inset-0 opacity-20">
        <div class="absolute top-0 right-0 w-36 h-36 bg-red-400 rounded-full translate-x-10 -translate-y-10"></div>
        <div class="absolute bottom-0 left-0 w-28 h-28 bg-rose-400 rounded-full -translate-x-10 translate-y-10"></div>
        <div class="absolute top-1/2 left-1/3 w-20 h-20 bg-red-300 rounded-full -translate-y-10 opacity-60"></div>
      </div>
      <div class="flex items-center justify-between relative z-10">
        <div class="flex items-center gap-6">
          <div class="w-16 h-16 bg-red-500/30 backdrop-blur-sm rounded-3xl flex items-center justify-center border border-red-400/40 shadow-lg">
            <i class="fas fa-comments text-red-100 text-2xl"></i>
          </div>
          <h2 class="text-2xl font-bold text-white mb-0 leading-tight" id="add_forum_title">
            Add Forum Topic
          </h2>
        </div>
        <button 
          type="button"
          onclick="closeForumModal()" 
          class="w-12 h-12 bg-red-500/30 hover:bg-red-400/40 backdrop-blur-sm rounded-3xl flex items-center justify-center transition-all duration-300 border border-red-400/40 hover:border-red-300/60 group"
        >
          <i class="fas fa-times text-red-100 text-xl group-hover:scale-110 transition-transform duration-300"></i>
        </button>
      </div>
    </div>
    
    <!-- Modal Form Content -->
    <div class="flex-1 overflow-y-auto">
      <form id="manage-forum" class="p-8 space-y-8">
        <input type="hidden" name="id" id="forum_id" value="">
        
        <!-- Topic Title -->
        <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
          <label class="block text-sm font-bold text-red-900 mb-1" for="forum_title">
            <i class="fas fa-heading mr-2 text-red-600"></i> Topic Title
          </label>
          <input type="text" id="forum_title" name="title" class="w-full px-4 py-2 rounded-xl border border-red-200 focus:ring-red-400 focus:border-red-400 transition-all text-red-900 bg-white/60" required>
        </div>
        
        <!-- Description -->
        <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
          <label class="block text-sm font-bold text-red-900 mb-1" for="forum_description">
            <i class="fas fa-align-left mr-2 text-red-600"></i> Description
          </label>
          <textarea id="forum_description" name="description" class="w-full min-h-[120px] max-h-60 px-4 py-2 rounded-xl border border-red-200 focus:ring-red-400 focus:border-red-400 transition-all text-red-900 bg-white/60 custom-scrollbar" required></textarea>
        </div>
        
        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" class="px-5 py-2 rounded-xl bg-red-100 hover:bg-red-200 text-red-700 font-semibold border border-red-200 transition-all" onclick="closeForumModal()">
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

<!-- View Forum Modal -->
<div id="viewForumModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center hidden p-4">
  <div class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl border border-gray-200 w-full max-w-3xl animate-fade-in flex flex-col overflow-hidden max-h-[90vh]">


    
    <!-- Modal Header -->
    <div class="bg-gradient-to-r from-red-600 via-red-700 to-rose-600 px-8 py-8 relative overflow-hidden">
      <!-- Background decorative elements -->
      <div class="absolute inset-0 opacity-20">
        <div class="absolute top-0 right-0 w-32 h-32 bg-red-400 rounded-full translate-x-12 -translate-y-12"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-rose-400 rounded-full -translate-x-8 translate-y-8"></div>
        <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-red-300 rounded-full -translate-y-8"></div>
      </div>
      
      <div class="flex items-start justify-between relative z-10">
        <div class="flex items-start gap-5 flex-1 pr-4">
          <div class="w-16 h-16 bg-red-500/30 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-red-400/40 shadow-lg flex-shrink-0">
            <i class="fas fa-comments text-red-100 text-2xl"></i>
          </div>
          <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold text-white mb-2 leading-tight" id="view_forum_title">
              Forum Discussion Topic
            </h2>
            <div class="flex items-center gap-4 text-red-100 text-sm">
              <div class="flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i>
                <span>Today</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="fas fa-user"></i>
                <span id="view_forum_created_by">Loading...</span>
              </div>
            </div>
          </div>
        </div>
        
        <button 
          onclick="closeViewForumModal()" 
          class="w-12 h-12 bg-red-500/30 hover:bg-red-400/40 backdrop-blur-sm rounded-2xl flex items-center justify-center transition-all duration-300 border border-red-400/40 hover:border-red-300/60 flex-shrink-0"
        >
          <i class="fas fa-times text-red-100 text-lg"></i>
        </button>
      </div>
    </div>

    <!-- Modal Content -->
    <div class="flex-1 overflow-y-auto p-8">
      <div class="space-y-8">
        
        <!-- Description Section -->
        <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center">
              <i class="fas fa-align-left text-red-600 text-sm"></i>
            </div>
            <h3 class="text-lg font-bold text-red-900">Description</h3>
          </div>
          <div 
            id="view_forum_description" 
            class="text-red-800 leading-relaxed whitespace-pre-line bg-white/60 rounded-xl p-4 border border-red-200 min-h-[100px] max-h-48 overflow-y-auto custom-scrollbar"
            >
            Loading description...
            </div>
        </div>

        <!-- Comments Section -->
        <div class="bg-red-50/70 backdrop-blur-sm rounded-2xl p-6 border border-red-100 shadow-sm">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-comments text-red-600 text-sm"></i>
              </div>
              <h3 class="text-lg font-bold text-red-900">Comments</h3>
            </div>
            <div class="bg-red-100 text-red-700 px-3 py-1 rounded-xl text-sm font-semibold border border-red-200">
              <i class="fas fa-comment-dots mr-1"></i>
              <span id="comment_count">0</span> Comments
            </div>
          </div>
          
          <!-- Comments Container -->
          <div 
            id="view_forum_comments" 
            class="space-y-4 max-h-80 overflow-y-auto custom-scrollbar"
          >
            <!-- Comments will be injected here by JS -->
          </div>
          <!-- Empty State -->
          <div id="no_comments_state" class="text-center py-8 hidden">
            <div class="w-16 h-16 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-comment-slash text-red-400 text-2xl"></i>
            </div>
            <h4 class="text-lg font-semibold text-red-800 mb-2">No Comments Yet</h4>
            <p class="text-red-600">Be the first to share your thoughts on this discussion.</p>
          </div>
        </div>
        
      </div>
    </div>

    
  </div>
</div>

<!-- Custom Styles -->
<style>
  @keyframes fade-in {
    from { 
      opacity: 0; 
      transform: scale(0.95) translateY(20px); 
    }
    to { 
      opacity: 1; 
      transform: scale(1) translateY(0); 
    }
  }
  
  .animate-fade-in {
    animation: fade-in 0.4s ease-out;
  }
  
  /* Custom Red Scrollbar */
  .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  
  .custom-scrollbar::-webkit-scrollbar-track {
    background: rgb(254 242 242);
    border-radius: 10px;
  }
  
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgb(239 68 68);
    border-radius: 10px;
  }
  
  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgb(220 38 38);
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .grid-cols-1.md\:grid-cols-3 {
      grid-template-columns: 1fr;
    }
  }
  
  /* Additional hover effects */
  .bg-white\/80:hover {
    background-color: rgba(255, 255, 255, 0.9);
  }
  
  .bg-red-50\/70:hover {
    background-color: rgba(254, 242, 242, 0.8);
  }
</style>

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
    // Helper to render the forums table
    function renderForumsTable(data) {
        let $tbody = $("#forumsTable tbody");
        $tbody.empty();
        let i = 1;
        data.forEach(function(row){
            let desc = (row.description || "").replace(/<[^>]+>/g, "");
            $tbody.append(`
                <tr class="hover:bg-primary-50 transition duration-150 group">
                    <td class="text-center px-2 md:px-6 py-4 font-bold text-primary-700 group-hover:text-primary-900">${i++}</td>
                    <td class="px-2 md:px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold shadow-inner">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <span class="font-semibold text-primary-700 group-hover:text-primary-900">${row.title}</span>
                        </div>
                    </td>
                    <td class="px-2 md:px-6 py-4 max-w-xs">
                        <span class="block truncate cursor-pointer" title="${desc}">${desc}</span>
                    </td>
                    <td class="px-2 md:px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-primary-300 to-primary-50 flex items-center justify-center text-primary-700 font-bold shadow">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="font-semibold text-primary-700">${row.name}</span>
                        </div>
                    </td>
                    <td class="px-2 md:px-6 py-4 text-right">
                        <span class="inline-flex items-center gap-1 bg-primary-100 text-primary-700 px-3 py-1 rounded-2xl font-bold shadow-sm">
                            <i class="fas fa-comment-dots"></i>
                            ${row.comment_count || 0}
                        </span>
                    </td>
                    <td class="px-2 md:px-6 py-4 text-center">
                        <div class="flex justify-center items-center gap-2">
                            <button class="bg-primary-50 hover:bg-primary-200 text-primary-700 border border-primary-200 px-3 py-2 rounded-xl font-semibold text-xs view_forum transition-all duration-150"
                                type="button" data-id="${row.id}">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="bg-primary-50 hover:bg-primary-200 text-primary-700 border border-primary-200 px-3 py-2 rounded-xl font-semibold text-xs edit_forum transition-all duration-150"
                                type="button" data-id="${row.id}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="bg-red-50 hover:bg-red-200 text-red-700 border border-red-200 px-3 py-2 rounded-xl font-semibold text-xs delete_forum transition-all duration-150"
                                type="button" data-id="${row.id}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });

        // Re-bind buttons for new table rows
        bindForumTableButtons();
    }

    // Reload forum table with AJAX
    function reloadForumTable() {
        $.get('forums.php?action=get_topics', function(result){
            let data = JSON.parse(result);
            // For each topic, get the comment count via AJAX (optional, or include it in your PHP above)
            let completed = 0;
            if(data.length === 0) {
                renderForumsTable([]);
                return;
            }
            data.forEach(function(row, idx){
                $.get('forums.php?action=get_comments&topic_id=' + row.id, function(cres){
                    data[idx].comment_count = JSON.parse(cres).length;
                    completed++;
                    if(completed === data.length) { renderForumsTable(data); }
                });
            });
        });
    }

    // Bind forum table buttons after rendering
    function bindForumTableButtons() {
        $('.view_forum').off('click').on('click', function(){
            let id = $(this).data('id');
            viewForum(id);
        });
        $('.edit_forum').off('click').on('click', function(){
            openForumModal(true, $(this).data('id'));
        });
        $('.delete_forum').off('click').on('click', function(){
            let id = $(this).data('id');
            $('#deleteForumModal').removeClass('hidden');
            $('#deleteForumConfirmBtn').off('click').on('click', function(){
                $.post('forums.php', {action:'delete_topic', topic_id:id}, function(result){
                    let data = JSON.parse(result);
                    if(data.status === 'success'){
                        $('#deleteForumModal').addClass('hidden');
                        reloadForumTable();
                        showSuccess(data.msg);
                    }else{
                        alert(data.msg);
                    }
                });
            });
        });
    }

    // Open Add/Edit Modal
    function openForumModal(edit=false, id=null) {
        $('#forumModal').removeClass('hidden');
        $('#manage-forum')[0].reset();
        $('#forum_id').val('');
        if(edit && id){
            $.post('forums.php', {action:'get_topic', topic_id:id}, function(result){
                let data = JSON.parse(result);
                $('#forum_id').val(data.id);
                $('#forum_title').val(data.title);
                $('#forum_description').val(data.description);
            });
        }
    }
    function closeForumModal() { $('#forumModal').addClass('hidden'); }
    $('#new_forum').click(function(){ openForumModal(false); });

    // Add/Edit Topic
    $('#manage-forum').submit(function(e){
        e.preventDefault();
        let id = $('#forum_id').val();
        let action = id ? 'edit_topic' : 'add_topic';
        $.post('forums.php', {
            action: action,
            topic_id: id,
            title: $('#forum_title').val(),
            description: $('#forum_description').val()
        }, function(result){
            let data = JSON.parse(result);
            if(data.status === 'success'){
                closeForumModal();
                reloadForumTable();
                showSuccess(data.msg);
            }else{
                alert(data.msg);
            }
        });
    });
    function closeDeleteForumModal(){ $('#deleteForumModal').addClass('hidden'); }
    function showSuccess(msg){ $('#successMessage').text(msg); $('#successModal').removeClass('hidden'); }
    function closeModal(){ $('#successModal').addClass('hidden'); }

    // View Topic and Comments
    function viewForum(id) {
        $.post('forums.php', {action:'get_topic', topic_id:id}, function(result){
            let topic = JSON.parse(result);
            $('#view_forum_title').text(topic.title);
            $('#view_forum_description').text(topic.description);
            $('#view_forum_created_by').text(topic.name);
            $.post('forums.php', {action:'get_comments', topic_id:id}, function(res){
                let comments = JSON.parse(res);
                let html = '';
                $('#comment_count').text(comments.length);
                if(comments.length === 0){
                    $('#no_comments_state').show();
                    $('#view_forum_comments').hide();
                }else{
                    $('#no_comments_state').hide();
                    $('#view_forum_comments').show();
                    comments.forEach(function(c){
                        html += `<div class="bg-white rounded-xl p-4 border border-red-100 shadow flex items-start gap-3">
                            <div class="w-10 h-10 bg-red-200 rounded-full flex items-center justify-center font-bold text-red-700">${(c.name||'')[0]}</div>
                            <div>
                                <div class="font-semibold text-red-700">${c.name} <span class="text-xs text-gray-400">@${c.username}</span></div>
                                <div class="text-gray-800 mt-1">${c.comment}</div>
                            </div>
                        </div>`;
                    });
                    $('#view_forum_comments').html(html);
                }
            });
            $('#viewForumModal').removeClass('hidden');
        });
    }
    function closeViewForumModal(){ $('#viewForumModal').addClass('hidden'); }

    // Initial load
    $(document).ready(function(){
        reloadForumTable();
    });
    </script>

     
</body>
</html>