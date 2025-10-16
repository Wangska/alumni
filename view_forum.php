<?php
session_start();
include 'admin/db_connect.php'; 
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid forum topic ID.");
}

$topic = $conn->query("SELECT f.*,u.name FROM forum_topics f INNER JOIN users u ON u.id = f.user_id WHERE f.id = ".$_GET['id']);
if (!$topic || $topic->num_rows == 0) {
    die("Topic not found.");
}
foreach($topic->fetch_array() as $k=>$v){
    if(!is_numeric($k))
        $$k = $v;
}

// Handle POST for new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['login_id'])) {
    $comment = trim($_POST['comment']);
    $topic_id = intval($_POST['topic_id']);
    $user_id = $_SESSION['login_id'];
    if ($comment && $topic_id) {
        $stmt = $conn->prepare("INSERT INTO forum_comments (topic_id, comment, user_id, date_created) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("isi", $topic_id, $comment, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: view_forum.php?id=$topic_id");
        exit();
    }
}

// Handle AJAX edit comment
if (isset($_POST['edit_comment_id']) && isset($_POST['edit_comment_content']) && isset($_SESSION['login_id'])) {
    $cid = intval($_POST['edit_comment_id']);
    $ccontent = trim($_POST['edit_comment_content']);
    // Check ownership
    $check = $conn->query("SELECT * FROM forum_comments WHERE id=$cid AND user_id=".$_SESSION['login_id']);
    if ($check && $check->num_rows == 1 && $ccontent) {
        $conn->query("UPDATE forum_comments SET comment='".$conn->real_escape_string($ccontent)."' WHERE id=$cid");
        echo "success";
        exit();
    }
    echo "error";
    exit();
}

// Handle AJAX delete comment
if (isset($_POST['delete_comment_id']) && isset($_SESSION['login_id'])) {
    $cid = intval($_POST['delete_comment_id']);
    // Check ownership
    $check = $conn->query("SELECT * FROM forum_comments WHERE id=$cid AND user_id=".$_SESSION['login_id']);
    if ($check && $check->num_rows == 1) {
        $conn->query("DELETE FROM forum_comments WHERE id=$cid");
        echo "success";
        exit();
    }
    echo "error";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forum Topic | Alumni Network</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/4c3b3c8a0e.js" crossorigin="anonymous"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-red-50 via-rose-100 to-red-200 font-sans">

<a href="javascript:history.back()" class="fixed top-6 left-6 bg-white/90 border-2 border-red-200 rounded-full px-6 py-2 text-red-700 font-semibold flex items-center gap-2 shadow-lg hover:bg-red-600 hover:text-white hover:border-red-600 transition z-50">
    <i class="fa fa-arrow-left"></i> Back to Forum
</a>

<!-- Topic Header -->
<header class="w-full h-44 md:h-60 flex flex-col justify-center items-center bg-gradient-to-r from-red-600 via-rose-500 to-red-400 relative shadow-lg mb-8">
    <h3 class="text-3xl md:text-4xl font-bold text-white text-center drop-shadow-lg"><?php echo htmlspecialchars($title); ?></h3>
    <hr class="border-white opacity-60 w-20 mx-auto my-4" />
    <span class="inline-flex items-center gap-2 bg-white/20 border-2 border-white/30 backdrop-blur-md px-4 py-2 rounded-full font-semibold text-white shadow">
        <i class="fa fa-user"></i>
        Created by: <?php echo htmlspecialchars($name); ?>
    </span>
</header>

<div class="max-w-3xl mx-auto px-4 md:px-0">
    <!-- Topic Content -->
    <div class="bg-white rounded-2xl shadow-xl mb-8 p-8">
        <div class="prose prose-lg prose-red max-w-none text-gray-700">
            <?php echo html_entity_decode($description) ?>
        </div>
        <hr class="my-6 border-red-100">
    </div>

    <?php 
    $comments = $conn->query("SELECT f.*,u.name,u.username FROM forum_comments f INNER JOIN users u ON u.id = f.user_id WHERE f.topic_id = $id ORDER BY f.id ASC");
    ?>

    <!-- Comments Section -->
    <div class="bg-white rounded-2xl shadow-xl mb-8">
        <div class="bg-gradient-to-r from-red-600 via-rose-500 to-red-400 text-white rounded-t-2xl px-8 py-5 flex items-center gap-4">
            <i class="fa fa-comments text-2xl"></i>
            <h3 class="text-xl font-bold">
                <?php echo $comments ? $comments->num_rows : 0; ?> 
                Comment<?php echo ($comments && $comments->num_rows != 1) ? 's' : ''; ?>
            </h3>
        </div>
        
        <div class="p-8 space-y-6">
            <?php 
            if ($comments && $comments->num_rows > 0):
                while($row = $comments->fetch_assoc()):
            ?>
            <div class="group relative bg-rose-50 rounded-xl p-6 shadow-md border-l-4 border-rose-400 transition hover:bg-rose-100">
                <?php if(isset($_SESSION['login_id']) && $_SESSION['login_id'] == $row['user_id']): ?>
                    <div class="absolute top-3 right-3 flex gap-2">
                        <button class="edit_comment bg-yellow-100 text-yellow-700 px-3 py-2 rounded-lg shadow hover:bg-yellow-200 flex items-center gap-1 transition" data-id="<?php echo $row['id']; ?>">
                            <i class="fa fa-edit"></i> Edit
                        </button>
                        <button class="delete_comment bg-red-100 text-red-700 px-3 py-2 rounded-lg shadow hover:bg-red-200 flex items-center gap-1 transition" data-id="<?php echo $row['id']; ?>">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                <?php endif; ?>

                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 bg-gradient-to-r from-red-500 to-rose-400 rounded-full flex items-center justify-center text-white text-lg font-bold shadow">
                        <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div class="font-semibold text-red-700"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="text-xs text-gray-500">@<?php echo htmlspecialchars($row['username']); ?></div>
                    </div>
                </div>
                <div class="text-base text-gray-700 whitespace-pre-line" id="comment-content-<?php echo $row['id']; ?>">
                    <?php echo nl2br(html_entity_decode($row['comment'])) ?>
                </div>
                <!-- Edit form (hidden by default) -->
                <form class="edit-comment-form mt-3 space-y-2" id="edit-form-<?php echo $row['id']; ?>" style="display:none;">
                    <textarea class="w-full border-2 border-red-200 rounded-lg p-3 text-base" name="comment" rows="3" placeholder="Edit your comment..."><?php echo htmlspecialchars($row['comment']); ?></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="cancel-edit bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition" data-id="<?php echo $row['id']; ?>">
                            <i class="fa fa-times mr-1"></i>Cancel
                        </button>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                            <i class="fa fa-check mr-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
            <?php endwhile; 
            else: ?>
            <div class="text-center py-12 text-gray-400">
                <i class="fa fa-comments-o text-4xl mb-2"></i>
                <h4 class="text-lg font-semibold mb-2">No Comments Yet</h4>
                <p>Be the first to share your thoughts on this topic!</p>
            </div>
            <?php endif; ?>

            <!-- New Comment Form -->
            <div class="bg-gradient-to-br from-rose-50 to-white rounded-xl p-8 border-2 border-rose-100 mt-4">
                <?php if(isset($_SESSION['login_id'])): ?>
                <h4 class="font-bold text-red-700 text-lg mb-4 flex items-center gap-2">
                    <i class="fa fa-plus-circle"></i> Add Your Comment
                </h4>
                <form action="" method="POST" id="manage-comment" class="space-y-4">
                    <input type="hidden" name="topic_id" value="<?php echo isset($id) ? $id : '' ?>">
                    <textarea class="w-full border-2 border-red-200 rounded-lg p-4 text-base" name="comment" rows="5" placeholder="Share your thoughts, ask questions, or join the discussion..." required></textarea>
                    <div class="flex justify-end">
                        <button class="bg-gradient-to-r from-red-600 to-rose-600 text-white font-semibold px-6 py-3 rounded-xl shadow hover:from-rose-700 hover:to-red-800 transition" type="submit">
                            <i class="fa fa-paper-plane mr-2"></i>Post Comment
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="bg-blue-50 border border-blue-200 text-blue-600 rounded-xl px-8 py-6 text-center shadow">
                    <i class="fa fa-info-circle text-2xl mb-3"></i>
                    <p class="font-semibold mb-4">Please log in to join the discussion and add your comments.</p>
                    <button onclick="openLoginModal()" class="bg-gradient-to-r from-red-600 to-rose-600 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:from-red-700 hover:to-rose-700 transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login to Comment
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.edit_comment').forEach(function(btn){
    btn.addEventListener('click', function(){
        var id = this.dataset.id;
        document.getElementById('edit-form-' + id).style.display = 'block';
        document.getElementById('comment-content-' + id).style.display = 'none';
    });
});
document.querySelectorAll('.cancel-edit').forEach(function(btn){
    btn.addEventListener('click', function(){
        var id = this.dataset.id;
        document.getElementById('edit-form-' + id).style.display = 'none';
        document.getElementById('comment-content-' + id).style.display = '';
    });
});
document.querySelectorAll('.edit-comment-form').forEach(function(form){
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var id = this.id.replace('edit-form-','');
        var comment = this.querySelector('textarea').value;
        fetch('', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'edit_comment_id='+encodeURIComponent(id)+'&edit_comment_content='+encodeURIComponent(comment)
        })
        .then(r=>r.text())
        .then(res=>{
            if(res.trim() === 'success') location.reload();
            else alert('Failed to edit comment.');
        });
    });
});
document.querySelectorAll('.delete_comment').forEach(function(btn){
    btn.addEventListener('click', function(){
        if(confirm('Are you sure you want to delete this comment?')){
            var id = this.dataset.id;
            fetch('', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'delete_comment_id='+encodeURIComponent(id)
            })
            .then(r=>r.text())
            .then(res=>{
                if(res.trim() === 'success') location.reload();
                else alert('Failed to delete comment.');
            });
        }
    });
});
</script>
<style>
.glass-effect {
    backdrop-filter: blur(16px) saturate(180%);
    background-color: rgba(255, 255, 255, 0.90);
    border: 1px solid rgba(239, 68, 68, 0.18);
}
@keyframes float {
    0%,100% { transform: translateY(0px) scale(1);}
    50% { transform: translateY(-32px) scale(1.06);}
}
@keyframes float2 {
    0%,100% { transform: translateY(0px) scale(1);}
    50% { transform: translateY(32px) scale(1.08);}
}
@keyframes float3 {
    0%,100% { transform: translateX(0px) scale(1);}
    50% { transform: translateX(-32px) scale(1.05);}
}
.animate-float { animation: float 8s ease-in-out infinite; }
.animate-float2 { animation: float2 11s ease-in-out infinite; }
.animate-float3 { animation: float3 10s ease-in-out infinite; }

/* Modal Styles */
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
                    <select name="batch" id="batch_year_forum" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-white">
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

<script>
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

// Populate Batch Years
function populateBatchYears() {
    const batchSelect = document.getElementById('batch_year_forum');
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