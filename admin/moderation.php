<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db_connect.php';
if (!isset($_SESSION['login_type']) || $_SESSION['login_type'] != 1) {
    header('Location: login.php');
    exit;
}

// Approve/Reject handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $table = $type === 'story' ? 'success_stories' : 'testimonials';
    if ($id > 0 && in_array($action, ['approve','reject'])) {
        $status = $action === 'approve' ? 1 : 2; // 1=approved, 2=rejected
        $stmt = $conn->prepare("UPDATE $table SET status=? WHERE id=?");
        $stmt->bind_param('ii', $status, $id);
        $stmt->execute();
    }
    header('Location: moderation.php');
    exit;
}

$pending_stories = $conn->query("SELECT ss.*, u.username FROM success_stories ss LEFT JOIN users u ON u.id=ss.user_id WHERE ss.status=0 ORDER BY ss.date_created DESC");
$pending_testimonials = $conn->query("SELECT t.*, u.username FROM testimonials t LEFT JOIN users u ON u.id=t.user_id WHERE t.status=0 ORDER BY t.date_created DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderation - Admin Panel</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; }
        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .btn-approve { background: #10b981; border-color: #10b981; }
        .btn-reject { background: #ef4444; border-color: #ef4444; }
    </style>
</head>
<body>
    <?php include 'topbar.php'; ?>
    <?php include 'navbar.php'; ?>
    
    <div class="ml-64 pt-16">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Content Moderation</h1>
                    <p class="text-gray-600">Review and approve alumni submissions</p>
                </div>
            </div>

            <!-- Success Stories Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        Pending Success Stories
                    </h2>
                </div>
                <div class="p-6">
                    <?php if($pending_stories && $pending_stories->num_rows): ?>
                        <div class="space-y-4">
                            <?php while($row=$pending_stories->fetch_assoc()): ?>
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($row['title']); ?></h3>
                                            <p class="text-sm text-gray-600">By: <?php echo htmlspecialchars($row['username']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo date('M j, Y g:i A', strtotime($row['date_created'])); ?></p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <form method="post" class="inline">
                                                <input type="hidden" name="type" value="story">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button class="btn-approve text-white px-4 py-2 rounded text-sm font-medium hover:bg-green-600 transition" name="action" value="approve">
                                                    <i class="fas fa-check mr-1"></i>Approve
                                                </button>
                                            </form>
                                            <form method="post" class="inline">
                                                <input type="hidden" name="type" value="story">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button class="btn-reject text-white px-4 py-2 rounded text-sm font-medium hover:bg-red-600 transition" name="action" value="reject">
                                                    <i class="fas fa-times mr-1"></i>Reject
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="text-gray-700">
                                        <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No pending success stories</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Testimonials Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-quote-left text-blue-500 mr-2"></i>
                        Pending Testimonials
                    </h2>
                </div>
                <div class="p-6">
                    <?php if($pending_testimonials && $pending_testimonials->num_rows): ?>
                        <div class="space-y-4">
                            <?php while($row=$pending_testimonials->fetch_assoc()): ?>
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="text-sm text-gray-600">By: <?php echo htmlspecialchars($row['username']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo date('M j, Y g:i A', strtotime($row['date_created'])); ?></p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <form method="post" class="inline">
                                                <input type="hidden" name="type" value="testimonial">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button class="btn-approve text-white px-4 py-2 rounded text-sm font-medium hover:bg-green-600 transition" name="action" value="approve">
                                                    <i class="fas fa-check mr-1"></i>Approve
                                                </button>
                                            </form>
                                            <form method="post" class="inline">
                                                <input type="hidden" name="type" value="testimonial">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button class="btn-reject text-white px-4 py-2 rounded text-sm font-medium hover:bg-red-600 transition" name="action" value="reject">
                                                    <i class="fas fa-times mr-1"></i>Reject
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="text-gray-700 italic">
                                        "<?php echo nl2br(htmlspecialchars($row['quote'])); ?>"
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No pending testimonials</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

