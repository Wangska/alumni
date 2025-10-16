<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

include './db_connect.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
    exit;
}

// Legacy MD5 compare to match existing data
$hashed = md5($password);

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND type = 1 LIMIT 1");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: failed to prepare statement.']);
    exit;
}
$stmt->bind_param('ss', $username, $hashed);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $_SESSION['login_id'] = $row['id'];
    $_SESSION['login_username'] = $row['username'];
    $_SESSION['login_name'] = $row['name'];
    $_SESSION['login_type'] = $row['type'];

    // Load system settings to session
    $system = $conn->query("SELECT * FROM system_settings LIMIT 1");
    if ($system && $system->num_rows > 0) {
        foreach ($system->fetch_assoc() as $k => $v) {
            $_SESSION['system'][$k] = $v;
        }
    }

    echo json_encode(['status' => 'success', 'redirect' => 'dashboard.php']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid admin credentials.']);
exit;
?>

