<?php
session_start();
include 'admin/db_connect.php';

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch ALL upcoming events
$events = [];
$res = $conn->query("SELECT * FROM events WHERE schedule >= NOW() ORDER BY schedule ASC");
while($row = $res->fetch_assoc()) $events[] = $row;

// If the user clicks "Commit", handle AJAX commit request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'commit_event') {
    if (!isset($_SESSION['login_id'])) {
        echo json_encode(['status'=>'error', 'msg'=>'You must be logged in to commit.']);
        exit;
    }
    $event_id = intval($_POST['event_id']);
    $user_id = intval($_SESSION['login_id']);

    // Check for duplicate commit
    $exists = $conn->query("SELECT * FROM event_commits WHERE event_id=$event_id AND user_id=$user_id");
    if ($exists && $exists->num_rows > 0) {
        echo json_encode(['status'=>'error', 'msg'=>'You have already committed to this event!']);
        exit;
    }

    // Try insert, check for errors
    if ($conn->query("INSERT INTO event_commits (event_id, user_id) VALUES ($event_id, $user_id)")) {
        echo json_encode(['status'=>'success', 'msg'=>'Successfully committed to event!']);
    } else {
        echo json_encode(['status'=>'error', 'msg'=>'Database error: '.$conn->error]);
    }
    exit;
}

// Helper function to get commit count and if user is committed
function event_commit_info($conn, $event_id, $user_id = null) {
    $row = ['count'=>0,'committed'=>false];
    $res = $conn->query("SELECT user_id FROM event_commits WHERE event_id=$event_id");
    $row['count'] = $res ? $res->num_rows : 0;
    if ($user_id) {
        $found = false;
        if ($res) {
            $res->data_seek(0); // Reset pointer for multiple fetch_assoc
            while ($r = $res->fetch_assoc()) {
                if ($r['user_id'] == $user_id) $found = true;
            }
        }
        $row['committed'] = $found;
    }
    return $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Upcoming Events</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    .event-card {
      position: relative;
      overflow: hidden;
    }
    .event-bg-circle {
      pointer-events: none;
      position: absolute;
      z-index: 0;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-rose-100 via-white to-red-100 min-h-screen font-sans">
  <section class="py-16 relative min-h-screen">
    <div class="container mx-auto px-4">
      <div class="max-w-6xl mx-auto">
        <h2 class="text-4xl md:text-5xl font-extrabold bg-gradient-to-r from-red-600 via-red-700 to-rose-600 inline-block text-transparent bg-clip-text mb-12 text-center drop-shadow-lg">
          All Upcoming Events
        </h2>
        <div class="grid md:grid-cols-2 gap-10">
          <?php if(count($events) === 0): ?>
            <div class="col-span-2 text-center py-12 text-gray-400">
              <div class="w-20 h-20 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow">
                <i class="fas fa-calendar-times text-red-400 text-4xl"></i>
              </div>
              <span class="block text-xl font-semibold">No upcoming events.</span>
            </div>
          <?php endif; ?>
          <?php foreach ($events as $e):
            $commit_info = event_commit_info($conn, $e['id'], $_SESSION['login_id'] ?? null);
          ?>
            <div class="event-card rounded-3xl bg-white/90 backdrop-blur-lg shadow-[0_8px_32px_0_rgba(229,70,86,0.10)] border border-red-100 p-8 hover:scale-[1.02] transition-all duration-300 flex flex-col mb-4 relative group">
              <!-- Decorative Circles -->
              <span class="event-bg-circle top-0 right-0 w-24 h-24 bg-rose-100 rounded-full translate-x-8 -translate-y-8 opacity-40"></span>
              <span class="event-bg-circle bottom-0 left-0 w-16 h-16 bg-red-100 rounded-full -translate-x-6 translate-y-6 opacity-30"></span>
              <?php if(!empty($e['banner'])): ?>
                <div class="relative mb-6">
                  <img src="admin/assets/uploads/<?php echo htmlspecialchars($e['banner']); ?>"
                      alt="Banner for <?php echo htmlspecialchars($e['title']); ?>"
                      class="event-banner rounded-2xl border-2 border-red-200 shadow-lg w-full max-h-52 object-cover group-hover:shadow-xl transition-all duration-300" />
                  <div class="absolute inset-0 bg-gradient-to-t from-red-200/30 to-transparent rounded-2xl"></div>
                </div>
              <?php endif; ?>
              <div class="flex items-center mb-4 z-10">
                <div class="bg-gradient-to-r from-red-600 to-rose-600 text-white p-3 rounded-full mr-4 shadow-lg border-2 border-rose-200">
                  <i class="fas fa-calendar-alt text-xl"></i>
                </div>
                <div>
                  <h3 class="text-2xl font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($e['title']) ?></h3>
                  <span class="inline-block text-xs font-semibold bg-red-50 text-red-700 px-3 py-1 rounded-xl border border-red-100 mb-1">
                    <i class="fas fa-clock mr-1"></i>
                    <?php echo date('D, M j, Y', strtotime($e['schedule'])) ?>
                  </span>
                </div>
              </div>
              <p class="text-red-900/90 text-base leading-relaxed mb-4 bg-white/60 rounded-xl p-4 border border-red-100 shadow-sm">
                <?php echo nl2br(htmlspecialchars($e['content'])) ?>
              </p>
              <?php if(!empty($e['venue'])): ?>
                <div class="inline-flex items-center text-xs font-medium bg-red-100 text-red-800 rounded-full px-4 py-1 border border-red-200 shadow-sm mb-2">
                  <i class="fas fa-map-marker-alt mr-2 text-red-600"></i><?php echo htmlspecialchars($e['venue']) ?>
                </div>
              <?php endif; ?>
              <?php if(!empty($e['link'])): ?>
                <div class="mt-4">
                  <a href="<?php echo htmlspecialchars($e['link']) ?>" target="_blank"
                     class="inline-flex items-center text-base font-semibold text-red-600 hover:text-rose-700 transition-colors bg-red-50 hover:bg-red-100 px-5 py-2 rounded-full shadow border border-red-100">
                    More Info <i class="fas fa-arrow-right ml-2"></i>
                  </a>
                </div>
              <?php endif; ?>

              <!-- Commit Button and Info -->
              <div class="mt-8 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="bg-gradient-to-r from-red-200 to-rose-200 text-red-700 px-3 py-1 rounded-full font-semibold text-sm shadow">
                    <i class="fas fa-user-check mr-2"></i>
                    <?php echo $commit_info['count']; ?> committed
                  </span>
                </div>
                <?php if(isset($_SESSION['login_id'])): ?>
                  <?php if($commit_info['committed']): ?>
                    <button class="bg-green-600 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-green-700 transition" disabled>
                      <i class="fas fa-check mr-2"></i>Committed
                    </button>
                  <?php else: ?>
                    <button class="bg-gradient-to-r from-red-600 to-rose-600 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-red-700 transition commit-btn"
                      data-event-id="<?php echo $e['id']; ?>">
                      <i class="fas fa-user-plus mr-2"></i>Commit to Event
                    </button>
                  <?php endif; ?>
                <?php else: ?>
                  <a href="login.php" class="bg-gradient-to-r from-red-400 to-rose-400 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-red-600 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login to Commit
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="flex justify-center mt-14">
          <a href="index.php" class="bg-gradient-to-r from-red-100 to-gray-200 text-red-700 px-10 py-4 rounded-full shadow-lg font-bold text-lg hover:from-red-200 hover:to-gray-100 transition-all flex items-center gap-3 border border-red-200">
            <i class="fas fa-arrow-left"></i> Back to Home
          </a>
        </div>
      </div>
    </div>
  </section>
  <script>
    $(document).on('click', '.commit-btn', function(e){
      e.preventDefault();
      var btn = $(this);
      var event_id = btn.data('event-id');
      btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Committing...');
      $.post('events.php', {action: 'commit_event', event_id: event_id}, function(res){
        var data = {};
        try { data = JSON.parse(res); } catch(e){}
        if(data.status && data.status === 'success'){
          btn.removeClass('bg-gradient-to-r from-red-600 to-rose-600')
             .addClass('bg-green-600')
             .html('<i class="fas fa-check mr-2"></i>Committed');
          btn.prop('disabled', true);
        }else{
          alert(data.msg || res || 'Error committing to event.');
          btn.prop('disabled', false).html('<i class="fas fa-user-plus mr-2"></i>Commit to Event');
        }
      });
    });
  </script>
</body>
</html>