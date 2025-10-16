<?php
// Session already started in index.php, don't start again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// Handle insert/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal_action'])) {
    $id = isset($_POST['id']) && $_POST['id'] ? intval($_POST['id']) : null;
    $course = isset($_POST['course']) ? $_POST['course'] : '';
    if ($id) {
        $stmt = $conn->prepare("UPDATE courses SET course=? WHERE id=?");
        $stmt->bind_param("si", $course, $id);
        $stmt->execute();
        echo "<script>window.onload = function() { showModal('Course updated successfully!'); setTimeout(function(){ window.location.href='courses.php'; }, 1500); }</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (course) VALUES (?)");
        $stmt->bind_param("s", $course);
        $stmt->execute();
        echo "<script>window.onload = function() { showModal('Course added successfully!'); setTimeout(function(){ window.location.href='courses.php'; }, 1500); }</script>";
    }
}

// Handle delete
if (isset($_GET['delete']) && $_GET['delete']) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>window.onload = function() { showModal('Course deleted successfully!'); setTimeout(function(){ window.location.href='courses.php'; }, 1500); }</script>";
}

// Handle edit
$edit = false;
if (isset($_GET['edit']) && $_GET['edit']) {
    $edit = true;
    $edit_id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM courses WHERE id=$edit_id");
    $edit_row = $res->fetch_assoc();
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
            <a href="dashboard.php" class="nav-item nav-courses flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-dashboard"></i> 
                <span>Dashboard</span>
            </a>
            <a href="gallery.php" class="nav-item nav-courses flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-images"></i> 
                <span>Gallery</span>
            </a>
             <a href="courses.php" class="nav-item nav-home active flex items-center gap-3 px-4 py-3 rounded-lg text-primary-700 bg-gradient-to-r from-primary-100 to-primary-50 font-medium hover:bg-primary-200 transition">
                <i class="fas fa-graduation-cap"></i> 
                <span>Course List</span>
            </a>
            <a href="alumni.php" class="nav-item nav-alumni flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
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

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
      <div class="bg-white rounded-2xl shadow-2xl border border-primary-200 px-8 py-6 flex flex-col items-center animate-fade-in">
        <i class="fas fa-check-circle text-5xl text-primary-500 mb-4 animate-pulse"></i>
        <h3 class="text-2xl font-bold text-primary-700 mb-2">Success</h3>
        <div id="successMessage" class="text-lg text-gray-700 mb-3"></div>
        <button onclick="closeModal()" class="bg-primary-600 hover:bg-primary-700 text-white font-bold px-6 py-2 rounded-lg shadow">Close</button>
      </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-primary-200 px-8 py-6 flex flex-col items-center animate-fade-in">
        <i class="fas fa-check-circle text-5xl text-primary-500 mb-4 animate-pulse"></i>
        <h3 class="text-2xl font-bold text-primary-700 mb-2">Success</h3>
        <div id="successMessage" class="text-lg text-gray-700 mb-3"></div>
        <button onclick="closeModal()" class="bg-primary-600 hover:bg-primary-700 text-white font-bold px-6 py-2 rounded-lg shadow">Close</button>
    </div>
    </div>
    <!-- Add/Edit Modal -->
    <div id="courseModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
    <form method="POST" class="bg-white rounded-2xl shadow-xl border border-primary-100 px-8 py-8 w-full max-w-lg" id="courseForm">
        <input type="hidden" name="id" id="form_id" value="">
        <input type="hidden" name="modal_action" value="1">
        <h3 class="text-xl font-bold text-primary-700 flex items-center gap-2 mb-6">
        <i class="fas fa-graduation-cap"></i>
        <span id="modalTitle">Add Course</span>
        </h3>
        <div class="mb-6">
        <label class="block text-sm font-medium text-primary-700 mb-1">Course Name</label>
        <input type="text" class="block w-full border border-primary-300 rounded-lg px-3 py-2 focus:ring-primary-500 focus:border-primary-500" name="course" id="form_course" required>
        </div>
        <div class="flex justify-end gap-4">
        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-5 py-2 rounded-lg shadow transition-all">
            <i class="fas fa-save mr-2"></i> <span id="modalBtnLabel">Save</span>
        </button>
        <button type="button" onclick="closeCourseModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-5 py-2 rounded-lg transition-all">Cancel</button>
        </div>
    </form>
    </div>

    <div id="deleteCourseModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-red-200 px-8 py-6 flex flex-col items-center animate-fade-in">
        <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4 animate-pulse"></i>
        <h3 class="text-2xl font-bold text-red-700 mb-2">Delete Course?</h3>
        <div class="text-lg text-gray-700 mb-3 text-center">
        Are you sure you want to delete this course? This action cannot be undone.
        </div>
        <div class="flex gap-4 mt-4">
        <button onclick="closeDeleteCourseModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-6 py-2 rounded-lg shadow">Cancel</button>
        <a id="deleteConfirmBtn" href="#" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold px-6 py-2 rounded-lg shadow">Delete</a>
        </div>
    </div>
    </div>

    <main class="ml-0 md:ml-64 pt-10 transition-all min-h-screen">
    <div class="w-full px-2 md:px-6 lg:px-10">
        <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-3xl font-bold text-primary-700 flex items-center gap-2">
            <i class="fas fa-graduation-cap"></i>
            Course Management
            </h1>
        </div>
        <button onclick="openCourseModal()" class="bg-primary-600 hover:bg-primary-700 text-white font-bold px-6 py-2 rounded-lg shadow flex items-center gap-2">
            <i class="fas fa-plus"></i> Add Course
        </button>
        </div>
        <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-primary-100">
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="fas fa-list"></i>
            Course List
            </h3>
        </div>
        <div class="px-6 py-4">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-primary-100 rounded-lg overflow-hidden">
                <thead class="bg-primary-50">
                <tr>
                    <th class="px-4 py-2 text-xs font-semibold text-center text-primary-700">#</th>
                    <th class="px-4 py-2 text-xs font-semibold text-center text-primary-700">Course</th>
                    <th class="px-4 py-2 text-xs font-semibold text-center text-primary-700">Action</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-primary-100">
                <?php 
                $i = 1;
                $course = $conn->query("SELECT * FROM courses order by id asc");
                while($row = $course->fetch_assoc()):
                ?>
                <tr>
                    <td class="px-4 py-2 text-center text-sm text-primary-700 font-medium"><?php echo $i++ ?></td>
                    <td class="px-4 py-2 text-sm text-gray-600"><?php echo $row['course'] ?></td>
                    <td class="px-4 py-2 text-center">
                    <button
                        class="bg-primary-600 hover:bg-primary-700 text-white px-3 py-1 rounded-lg shadow-sm text-xs font-semibold mr-2"
                        type="button"
                        onclick="editCourse(<?php echo $row['id'] ?>, '<?php echo addslashes($row['course']) ?>')"
                    >Edit</button>
                    <button
                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg shadow-sm text-xs font-semibold"
                    type="button"
                    onclick="deleteCourse(<?php echo $row['id'] ?>)"
                    >Delete</button>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        // Modal for add/edit
        function openCourseModal() {
            document.getElementById('courseForm').reset();
            document.getElementById('form_id').value = "";
            document.getElementById('modalTitle').innerText = "Add Course";
            document.getElementById('modalBtnLabel').innerText = "Save";
            document.getElementById('courseModal').classList.remove('hidden');
        }
        function closeCourseModal() {
            document.getElementById('courseModal').classList.add('hidden');
        }
        function editCourse(id, course) {
            document.getElementById('courseForm').reset();
            document.getElementById('form_id').value = id;
            document.getElementById('form_course').value = course;
            document.getElementById('modalTitle').innerText = "Edit Course";
            document.getElementById('modalBtnLabel').innerText = "Update";
            document.getElementById('courseModal').classList.remove('hidden');
        }
        // Deletion modal and action
        let deleteCourseId = null;
        function deleteCourse(id) {
        deleteCourseId = id;
        document.getElementById('deleteConfirmBtn').href = "courses.php?delete=" + id;
        document.getElementById('deleteCourseModal').classList.remove('hidden');
        }
        function closeDeleteCourseModal() {
        document.getElementById('deleteCourseModal').classList.add('hidden');
        deleteCourseId = null;
        }
        // Success modal
        function showModal(msg) {
            document.getElementById('successMessage').textContent = msg;
            document.getElementById('successModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('successModal').classList.add('hidden');
        }



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

        $('#manage-course').submit(function(e){
            e.preventDefault()
            start_load()
            $.ajax({
                url:'ajax.php?action=save_course',
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                success:function(resp){
                    if(resp==1){
                        showModal("Course added successfully!");
                        setTimeout(function(){
                            location.reload()
                        },1500)
                    }
                    else if(resp==2){
                        showModal("Course updated successfully!");
                        setTimeout(function(){
                            location.reload()
                        },1500)
                    }
                }
            })
        })
        $('.edit_course').click(function(){
            start_load()
            var cat = $('#manage-course')
            cat.get(0).reset()
            cat.find("[name='id']").val($(this).attr('data-id'))
            cat.find("[name='course']").val($(this).attr('data-course'))
            end_load()
        })
        $('.delete_course').click(function(){
            if(confirm("Are you sure to delete this course?")) {
                var id = $(this).attr('data-id');
                start_load()
                $.ajax({
                    url:'ajax.php?action=delete_course',
                    method:'POST',
                    data:{id:id},
                    success:function(resp){
                        if(resp==1){
                            showModal("Course deleted successfully!");
                            setTimeout(function(){
                                location.reload()
                            },1500)
                        }
                    }
                })
            }
        })
        $('table').dataTable()
    </script>
</body>
</html>