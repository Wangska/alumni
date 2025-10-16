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
        $status = $action === 'approve' ? 1 : 0;
        $stmt = $conn->prepare("UPDATE $table SET status=? WHERE id=?");
        $stmt->bind_param('ii', $status, $id);
        $stmt->execute();
    }
    header('Location: moderation.php');
    exit;
}

$pending_stories = $conn->query("SELECT ss.*, u.username FROM success_stories ss LEFT JOIN users u ON u.id=ss.user_id WHERE ss.status=0 ORDER BY ss.created DESC");
$pending_testimonials = $conn->query("SELECT t.*, u.username FROM testimonials t LEFT JOIN users u ON u.id=t.user_id WHERE t.status=0 ORDER BY t.created DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Moderation</title>
  <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
</head>
<body class="p-4">
  <h1 class="h3 mb-4">Moderation</h1>

  <h2 class="h5">Pending Success Stories</h2>
  <div class="table-responsive mb-5">
    <table class="table table-bordered table-sm bg-white">
      <thead><tr><th>ID</th><th>User</th><th>Title</th><th>Submitted</th><th>Action</th></tr></thead>
      <tbody>
      <?php if($pending_stories && $pending_stories->num_rows): while($row=$pending_stories->fetch_assoc()): ?>
        <tr>
          <td><?php echo $row['id']; ?></td>
          <td><?php echo htmlspecialchars($row['username']); ?></td>
          <td><?php echo htmlspecialchars($row['title']); ?></td>
          <td><?php echo htmlspecialchars($row['created']); ?></td>
          <td>
            <form method="post" class="d-inline">
              <input type="hidden" name="type" value="story">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <button class="btn btn-success btn-sm" name="action" value="approve">Approve</button>
              <button class="btn btn-outline-secondary btn-sm" name="action" value="reject">Reject</button>
            </form>
          </td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="5" class="text-center text-muted">No pending stories</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <h2 class="h5">Pending Testimonials</h2>
  <div class="table-responsive">
    <table class="table table-bordered table-sm bg-white">
      <thead><tr><th>ID</th><th>User</th><th>Quote</th><th>Submitted</th><th>Action</th></tr></thead>
      <tbody>
      <?php if($pending_testimonials && $pending_testimonials->num_rows): while($row=$pending_testimonials->fetch_assoc()): ?>
        <tr>
          <td><?php echo $row['id']; ?></td>
          <td><?php echo htmlspecialchars($row['username']); ?></td>
          <td class="text-wrap" style="max-width:400px;"><?php echo nl2br(htmlspecialchars($row['quote'])); ?></td>
          <td><?php echo htmlspecialchars($row['created']); ?></td>
          <td>
            <form method="post" class="d-inline">
              <input type="hidden" name="type" value="testimonial">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <button class="btn btn-success btn-sm" name="action" value="approve">Approve</button>
              <button class="btn btn-outline-secondary btn-sm" name="action" value="reject">Reject</button>
            </form>
          </td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="5" class="text-center text-muted">No pending testimonials</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>
</html>

