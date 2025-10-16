<?php
session_start();
include 'db_connect.php';

$action = $_GET['action'] ?? '';


// Signup logic (full version with avatar and connections)
if ($action == 'signup') {
    // Collect and validate fields
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $batch = intval($_POST['batch'] ?? 0);
    $course_id = intval($_POST['course_id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $connected_to = trim($_POST['connected_to'] ?? '');

    // Handle avatar upload
    $avatar_path = '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['tmp_name']) {
        $target_dir = "../admin/assets/uploads/";
        $filename = time() . '_' . basename($_FILES['avatar']['name']);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
            // store relative path for DB
            $avatar_path = "admin/assets/uploads/" . $filename;
        }
    }

    if (!$firstname || !$lastname || !$gender || !$batch || !$course_id || !$email || !$username || !$password) {
        echo '0'; // Missing required fields
        exit;
    }
    if ($password !== $confirm_password) {
        echo '0'; // Passwords do not match
        exit;
    }

    // Check for duplicate email or username
    $checkUser = $conn->query("SELECT id FROM users WHERE username='$username'");
    $checkEmail = $conn->query("SELECT id FROM alumnus_bio WHERE email='$email'");
    if (($checkUser && $checkUser->num_rows > 0) || ($checkEmail && $checkEmail->num_rows > 0)) {
        echo '0';
        exit;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert into alumnus_bio
    $stmt = $conn->prepare("INSERT INTO alumnus_bio (firstname, middlename, lastname, gender, batch, course_id, email, connected_to, avatar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("ssssiiiss", $firstname, $middlename, $lastname, $gender, $batch, $course_id, $email, $connected_to, $avatar_path);
    $stmt->execute();
    $alumnus_id = $conn->insert_id;
    $stmt->close();

    // Insert into users
    $name = $firstname . ' ' . $lastname;
    $stmt2 = $conn->prepare("INSERT INTO users (name, username, password, type, alumnus_id) VALUES (?, ?, ?, 3, ?)");
    $stmt2->bind_param("sssi", $name, $username, $password_hash, $alumnus_id);
    $stmt2->execute();
    $stmt2->close();

    echo '1';
    exit;
}


if ($action == 'signup') {
    // Collect and validate fields
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $batch = intval($_POST['batch'] ?? 0);
    $course_id = intval($_POST['course_id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $connected_to = trim($_POST['connected_to'] ?? '');

    // Handle avatar upload
    $avatar_path = '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['tmp_name']) {
        $target_dir = "../admin/assets/uploads/";
        $target_file = $target_dir . time() . '_' . basename($_FILES['avatar']['name']);
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
            // store relative path for DB
            $avatar_path = "admin/assets/uploads/" . time() . '_' . basename($_FILES['avatar']['name']);
        }
    }

    if (!$firstname || !$lastname || !$gender || !$batch || !$course_id || !$email || !$username || !$password) {
        echo '0'; // Missing required fields
        exit;
    }
    if ($password !== $confirm_password) {
        echo '0'; // Passwords do not match
        exit;
    }

    // Check for duplicate email or username
    $checkUser = $conn->query("SELECT id FROM users WHERE username='$username'");
    $checkEmail = $conn->query("SELECT id FROM alumnus_bio WHERE email='$email'");
    if (($checkUser && $checkUser->num_rows > 0) || ($checkEmail && $checkEmail->num_rows > 0)) {
        echo '0';
        exit;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert into alumnus_bio
    $stmt = $conn->prepare("INSERT INTO alumnus_bio (firstname, middlename, lastname, gender, batch, course_id, email, connected_to, avatar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("ssssiiiss", $firstname, $middlename, $lastname, $gender, $batch, $course_id, $email, $connected_to, $avatar_path);
    $stmt->execute();
    $alumnus_id = $conn->insert_id;
    $stmt->close();

    // Insert into users
    $name = $firstname . ' ' . $lastname;
    $stmt2 = $conn->prepare("INSERT INTO users (name, username, password, type, alumnus_id) VALUES (?, ?, ?, 3, ?)");
    $stmt2->bind_param("sssi", $name, $username, $password_hash, $alumnus_id);
    $stmt2->execute();
    $stmt2->close();

    echo '1';
    exit;
}


// --- GET FORUM DATA FOR VIEW/EDIT MODAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_forum' && isset($_POST['id'])) {
    include 'db_connect.php';
    $id = intval($_POST['id']);
    $res = $conn->query("SELECT f.*, u.name as created_by FROM forum_topics f INNER JOIN users u ON u.id = f.user_id WHERE f.id = $id");
    header('Content-Type: application/json');
    if ($res && $row = $res->fetch_assoc()) {
        // Fetch comments
        $comments = [];
        $comments_res = $conn->query("SELECT fc.*, u.name as user_name FROM forum_comments fc INNER JOIN users u ON u.id = fc.user_id WHERE fc.topic_id = $id ORDER BY fc.id ASC");
        while ($comments_res && $c = $comments_res->fetch_assoc()) {
            $comments[] = [
                "comment" => $c['comment'],
                "user_name" => $c['user_name'],
                "date_created" => $c['date_created']
            ];
        }
        $response = [
            "id" => $row['id'],
            "title" => $row['title'],
            "description" => $row['description'],
            "created_by" => $row['created_by'],
            "comments" => $comments
        ];
        echo json_encode($response);
    } else {
        echo json_encode((object)[]);
    }
    exit;
}

// --- GET CAREER DATA FOR EDIT MODAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_career' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $qry = $conn->query("SELECT * FROM careers WHERE id = $id");
    $career = $qry ? $qry->fetch_assoc() : null;
    header('Content-Type: application/json');
    echo json_encode($career);
    exit;
}

// --- GET EVENT DATA FOR EDIT/VIEW MODAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_event' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $qry = $conn->query("SELECT * FROM events WHERE id = $id");
    $event = $qry ? $qry->fetch_assoc() : null;
    header('Content-Type: application/json');
    echo json_encode($event);
    exit;
}




// Add this at the top, before admin_class.php, so it works for AJAX POST requests!
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_career' && isset($_POST['id'])) {
    include 'db_connect.php';
    $id = intval($_POST['id']);
    $qry = $conn->query("SELECT * FROM careers WHERE id = $id");
    $career = $qry ? $qry->fetch_assoc() : null;
    header('Content-Type: application/json');
    echo json_encode($career);
    exit;
}

include 'admin_class.php';
$crud = new Action();

if($action == 'login'){
	$login = $crud->login();
	if($login)
		echo $login;
}
if($action == 'login2'){
	$login = $crud->login2();
	if($login)
		echo $login;
}
if($action == 'logout'){
	$logout = $crud->logout();
	if($logout)
		echo $logout;
}
if($action == 'logout2'){
	$logout = $crud->logout2();
	if($logout)
		echo $logout;
}
if($action == 'save_user'){
	$save = $crud->save_user();
	if($save)
		echo $save;
}
if($action == 'delete_user'){
	$save = $crud->delete_user();
	if($save)
		echo $save;
}
if($action == 'signup'){
	$save = $crud->signup();
	if($save)
		echo $save;
}
if($action == 'update_account'){
	$save = $crud->update_account();
	if($save)
		echo $save;
}
if($action == "save_settings"){
	$save = $crud->save_settings();
	if($save)
		echo $save;
}
if($action == "save_course"){
	$save = $crud->save_course();
	if($save)
		echo $save;
}

if($action == "delete_course"){
	$delete = $crud->delete_course();
	if($delete)
		echo $delete;
}
if($action == "update_alumni_acc"){
	$save = $crud->update_alumni_acc();
	if($save)
		echo $save;
}
if($action == "save_gallery"){
	$save = $crud->save_gallery();
	if($save)
		echo $save;
}
if($action == "delete_gallery"){
	$save = $crud->delete_gallery();
	if($save)
		echo $save;
}

if($action == "save_career"){
	$save = $crud->save_career();
	if($save)
		echo $save;
}
if($action == "delete_career"){
	$save = $crud->delete_career();
	if($save)
		echo $save;
}
if($action == "save_forum"){
	$save = $crud->save_forum();
	if($save)
		echo $save;
}
if($action == "delete_forum"){
	$save = $crud->delete_forum();
	if($save)
		echo $save;
}


// --- COMMENT SECTION: ENHANCED FOR ADD, EDIT, DELETE ---

// Delete alumni handler
if ($action == "delete_alumni") {
    $save = $crud->delete_alumni();
    if ($save)
        echo $save;
    exit;
}

if($action == "save_comment"){
	$save = $crud->save_comment();
	if($save)
		echo $save;
}
if($action == "edit_comment"){
    session_start();
    include 'db_connect.php';
    $user_id = $_SESSION['login_id'] ?? 0;
    $id = $_POST['id'] ?? 0;
    $comment = $conn->real_escape_string($_POST['comment'] ?? '');
    if($user_id && $id && $comment !== ''){
        $sql = "UPDATE forum_comments SET comment='$comment' WHERE id=$id AND user_id=$user_id";
        $update = $conn->query($sql);
        if($update) {
            echo "1";
        } else {
            echo "0";
        }
    } else {
        echo "0";
    }
    exit;
}

if($action == "delete_comment"){
    session_start();
    include 'db_connect.php';
    $user_id = $_SESSION['login_id'] ?? 0;
    $id = $_POST['id'] ?? 0;
    if($user_id && $id){
        $sql = "DELETE FROM forum_comments WHERE id=$id AND user_id=$user_id";
        $delete = $conn->query($sql);
        if($delete) {
            echo "1";
        } else {
            echo "0";
        }
    } else {
        echo "0";
    }
    exit;
}

// --- END COMMENT SECTION ---

if($action == "save_event"){
	$save = $crud->save_event();
	if($save)
		echo $save;
}
if ($action == "delete_event") {
    include 'db_connect.php';
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $delete = $conn->query("DELETE FROM events WHERE id = $id");
        echo $delete ? "1" : "0";
    } else {
        echo "0";
    }
    exit;
}
if($action == "participate"){
	$save = $crud->participate();
	if($save)
		echo $save;
}
if($action == "get_venue_report"){
	$get = $crud->get_venue_report();
	if($get)
		echo $get;
}
if($action == "save_art_fs"){
	$save = $crud->save_art_fs();
	if($save)
		echo $save;
}
if($action == "delete_art_fs"){
	$save = $crud->delete_art_fs();
	if($save)
		echo $save;
}
if($action == "get_pdetails"){
	$get = $crud->get_pdetails();
	if($get)
		echo $get;
}
ob_end_flush();




?>