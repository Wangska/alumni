<?php
session_start();
include 'admin/db_connect.php';

// Load system settings into session
if(!isset($_SESSION['system'])){
    $system = $conn->query("SELECT * FROM system_settings LIMIT 1");
    if($system && $system->num_rows > 0){
        foreach($system->fetch_assoc() as $k => $v){
            $_SESSION['system'][$k] = $v;
        }
    }
}

// Helper function to clean input
function clean($value) {
    global $conn;
    return htmlspecialchars(trim($conn->real_escape_string($value)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $hashed = md5($password); // For demo only

    // Check if this is an AJAX request
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    // Look up user (admin or alumni)
    $check_sql = "SELECT u.*, a.firstname, a.middlename, a.lastname, a.email, a.status
                  FROM users u
                  LEFT JOIN alumnus_bio a ON u.alumnus_id = a.id
                  WHERE (u.username = ? OR a.email = ?)
                  AND u.password = ?
                  LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("sss", $username, $username, $hashed);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result && $check_result->num_rows === 1) {
        $row = $check_result->fetch_assoc();

        // Common session data
        $_SESSION['login_id'] = $row['id'];
        $_SESSION['login_username'] = $row['username'];
        $_SESSION['login_name'] = $row['name'];
        $_SESSION['login_type'] = $row['type'];

        // Admin login
        if ((int)$row['type'] === 1) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'redirect' => 'admin/dashboard.php']);
                exit();
            } else {
                header("Location: admin/dashboard.php");
                exit();
            }
        }

        // Alumni login: check verification
        if ((int)$row['type'] === 3) {
            if ((int)$row['status'] === 0) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'type' => 'unverified', 'message' => 'Account not verified']);
                    exit();
                } else {
                    header("Location: login.php?error=unverified");
                    exit();
                }
            }

            // Alumni is verified
            $_SESSION['alumnus_id'] = $row['alumnus_id'];
            $_SESSION['fullname'] = trim(($row['firstname'] ?? '') . ' ' . ($row['middlename'] ?? '') . ' ' . ($row['lastname'] ?? ''));
            $_SESSION['is_verified'] = $row['status'];
            $_SESSION['email'] = $row['email'];

            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'redirect' => 'index.php']);
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        }

        // Any other type not permitted
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'type' => 'invalid', 'message' => 'Invalid credentials']);
            exit();
        } else {
            header("Location: login.php?error=1");
            exit();
        }
    } else {
        // Invalid credentials
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'type' => 'invalid', 'message' => 'Invalid credentials']);
            exit();
        } else {
            header("Location: login.php?error=1");
            exit();
        }
    }
}
?>