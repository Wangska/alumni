<?php
session_start();
include 'admin/db_connect.php';

// Helper: sanitize input
function clean($value) {
    global $conn;
    return htmlspecialchars(trim($conn->real_escape_string($value)));
}

// Tailwind CSS CDN for beautiful messages
echo '<script src="https://cdn.tailwindcss.com"></script>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = clean($_POST['firstname'] ?? '');
    $middlename = clean($_POST['middlename'] ?? '');
    $lastname = clean($_POST['lastname'] ?? '');
    $gender = clean($_POST['gender'] ?? '');
    $batch = clean($_POST['batch'] ?? '');
    $course_id = intval($_POST['course_id'] ?? 0);
    $email = clean($_POST['email'] ?? '');
  $contact = clean($_POST['contact'] ?? '');
    $connected_to = clean($_POST['connected_to'] ?? '');
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $avatar = null;
    $status = 0; // By default, unverified

    // Validate required fields
    $errors = [];
    if (!$firstname) $errors[] = "First name is required.";
    if (!$lastname) $errors[] = "Last name is required.";
    if (!$gender) $errors[] = "Gender is required.";
    if (!$batch) $errors[] = "Batch is required.";
    if (!$course_id) $errors[] = "Course is required.";
    if (!$email) $errors[] = "Email is required.";
  if (!$contact) $errors[] = "Contact number is required.";
    if (!$username) $errors[] = "Username is required.";
    if (!$password) $errors[] = "Password is required.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
  // Enforce minimum password length
  if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters long.";
  
  // Validate batch year - should not exceed current year
  $currentYear = date('Y');
  if ($batch && intval($batch) > intval($currentYear)) {
      $errors[] = "Batch year cannot be in the future. Maximum allowed year is {$currentYear}.";
  }
  if ($batch && intval($batch) < 1950) {
      $errors[] = "Batch year must be 1950 or later.";
  }

    // Check if email or username already exists in alumnus_bio or users
    $check = $conn->query("SELECT id FROM alumnus_bio WHERE email='$email' OR id IN (SELECT alumnus_id FROM users WHERE username='$username') LIMIT 1");
    if ($check && $check->num_rows > 0) {
        $errors[] = "Email or username already exists.";
    }

    // Optional: handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $avatar_file = uniqid('avatar_').'.'.$ext;
            $upload_dir = 'admin/assets/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir.$avatar_file)) {
                $avatar = $avatar_file;
            } else {
                $errors[] = "Failed to upload avatar.";
            }
        } else {
            $errors[] = "Invalid image type for avatar.";
        }
    }

    // If errors found, show message and stop
    if ($errors) {
        echo '
        <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-red-50 via-rose-100 to-red-200">
          <div class="max-w-lg w-full mx-auto p-8 rounded-2xl shadow-2xl border border-red-200 bg-white/90 backdrop-blur-lg">
            <div class="flex flex-col items-center mb-6">
              <span class="inline-block bg-red-100 p-3 rounded-full shadow">
                <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </span>
              <h2 class="text-2xl font-bold text-red-700 mt-2">Account creation failed</h2>
            </div>
            <ul class="list-disc pl-6 text-red-500 mb-4">';
        foreach ($errors as $err) echo '<li class="mb-2">'.$err.'</li>';
        echo '</ul>
            <a href="register.php" class="inline-block mt-4 px-6 py-2 rounded-lg bg-gradient-to-r from-red-500 to-rose-500 text-white font-semibold shadow hover:from-red-600 hover:to-rose-600 transition">Go Back</a>
          </div>
        </div>';
        exit();
    }

  // Ensure `contact` column exists in alumnus_bio. If not, try to add it.
  $colCheck = $conn->query("SHOW COLUMNS FROM `alumnus_bio` LIKE 'contact'");
  if ($colCheck && $colCheck->num_rows == 0) {
    // Attempt to add the column (best-effort). If this fails, we'll still proceed but contact will not be saved.
    $conn->query("ALTER TABLE `alumnus_bio` ADD COLUMN `contact` VARCHAR(20) NOT NULL DEFAULT '' AFTER `email`");
  }

  // Save to alumnus_bio
    $date_created = date('Y-m-d');
  // Insert including contact (if column exists it will be saved; if ALTER failed, this may error)
  $stmt = $conn->prepare("INSERT INTO alumnus_bio 
    (firstname, middlename, lastname, gender, batch, course_id, email, contact, connected_to, avatar, status, date_created)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  if ($stmt) {
        $stmt->bind_param(
      "sssssissssis",
      $firstname,
      $middlename,
      $lastname,
      $gender,
      $batch,
      $course_id,
      $email,
      $contact,
      $connected_to,
      $avatar,
      $status,
      $date_created
    );
  } else {
    // Fallback: try inserting without contact if prepare failed (e.g., column doesn't exist and ALTER failed)
    $stmt = $conn->prepare("INSERT INTO alumnus_bio 
      (firstname, middlename, lastname, gender, batch, course_id, email, connected_to, avatar, status, date_created)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
      "ssssissssis",
      $firstname,
      $middlename,
      $lastname,
      $gender,
      $batch,
      $course_id,
      $email,
      $connected_to,
      $avatar,
      $status,
      $date_created
    );
  }
    $success = $stmt->execute();
    $alumnus_id = $conn->insert_id;
    $stmt->close();

    if ($success && $alumnus_id) {
        // Insert into users table
        $fullname = $firstname . " " . $middlename . " " . $lastname;
        $hashed = md5($password); // For demo; use password_hash in production!
        $type = 3; // 3 = alumnus
        $auto_generated_pass = ''; // You may store original or generated password if needed

        $stmt2 = $conn->prepare("INSERT INTO users (name, username, password, type, auto_generated_pass, alumnus_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("sssssi", $fullname, $username, $hashed, $type, $auto_generated_pass, $alumnus_id);
        $success2 = $stmt2->execute();
        $stmt2->close();

        if ($success2) {
            echo '
            <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-blue-50 via-indigo-100 to-blue-200">
              <div class="max-w-lg w-full mx-auto p-8 rounded-2xl shadow-2xl border border-blue-200 bg-white/90 backdrop-blur-lg">
                <div class="flex flex-col items-center mb-6">
                  <span class="inline-block bg-blue-100 p-3 rounded-full shadow">
                    <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  </span>
                  <h2 class="text-2xl font-bold text-blue-700 mt-2">Registration Submitted!</h2>
                </div>
                <div class="text-center text-blue-600 mb-6">
                  <p class="mb-2">Your account has been created and is <strong>awaiting admin approval</strong>.</p>
                  <p class="text-sm">You will be able to login once an administrator verifies your account. This usually takes 1-2 business days.</p>
                </div>
                <a href="index.php" class="inline-block px-6 py-2 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-500 text-white font-semibold shadow hover:from-blue-600 hover:to-indigo-600 transition">Go to Homepage</a>
              </div>
            </div>';
            exit();
        } else {
            // Rollback: you may want to delete the bio entry if user creation failed
            $conn->query("DELETE FROM alumnus_bio WHERE id = $alumnus_id");
            echo '
            <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-red-50 via-rose-100 to-red-200">
              <div class="max-w-lg w-full mx-auto p-8 rounded-2xl shadow-2xl border border-red-200 bg-white/90 backdrop-blur-lg">
                <div class="flex flex-col items-center mb-6">
                  <span class="inline-block bg-red-100 p-3 rounded-full shadow">
                    <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </span>
                  <h2 class="text-2xl font-bold text-red-700 mt-2">Account creation failed during user registration</h2>
                </div>
                <div class="text-center text-red-600 mb-6">'.$conn->error.'</div>
                <a href="register.php" class="inline-block px-6 py-2 rounded-lg bg-gradient-to-r from-red-500 to-rose-500 text-white font-semibold shadow hover:from-red-600 hover:to-rose-600 transition">Go Back</a>
              </div>
            </div>';
            exit();
        }
    } else {
        echo '
        <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-red-50 via-rose-100 to-red-200">
          <div class="max-w-lg w-full mx-auto p-8 rounded-2xl shadow-2xl border border-red-200 bg-white/90 backdrop-blur-lg">
            <div class="flex flex-col items-center mb-6">
              <span class="inline-block bg-red-100 p-3 rounded-full shadow">
                <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </span>
              <h2 class="text-2xl font-bold text-red-700 mt-2">Database Error</h2>
            </div>
            <div class="text-center text-red-600 mb-6">'.$conn->error.'</div>
            <a href="register.php" class="inline-block px-6 py-2 rounded-lg bg-gradient-to-r from-red-500 to-rose-500 text-white font-semibold shadow hover:from-red-600 hover:to-rose-600 transition">Go Back</a>
          </div>
        </div>';
        exit();
    }
}
?>