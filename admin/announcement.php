<?php
include 'db_connect.php';
if(isset($_POST['announcement'])){
    $announcement = $conn->real_escape_string($_POST['announcement']);
    $conn->query("INSERT INTO announcements (content, date_posted) VALUES ('$announcement', NOW())");
    echo '<script>window.location.href = "index.php?page=home&announcement=1";</script>';
    exit();
}
?>
<div class="container-fluid">
    <div class="card col-lg-8 offset-lg-2 mt-5">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Post Announcement</h4>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label for="announcement">Announcement</label>
                    <textarea name="announcement" id="announcement" class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post</button>
            </form>
        </div>
    </div>
</div>
