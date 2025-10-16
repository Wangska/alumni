<?php 
session_start();
include('./db_connect.php');
ob_start();

// Load system settings into session
$system = $conn->query("SELECT * FROM system_settings LIMIT 1");
if($system && $system->num_rows > 0){
    foreach($system->fetch_assoc() as $k => $v){
        $_SESSION['system'][$k] = $v;
    }
}

// Handle login submission
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $hashed = md5($password); // Use password_hash in production
    // Only allow admin type (type=1)
    $q = $conn->prepare("SELECT * FROM users WHERE username=? AND password=? AND type=1 LIMIT 1");
    $q->bind_param("ss", $username, $hashed);
    $q->execute();
    $res = $q->get_result();
    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $_SESSION['login_id'] = $row['id'];
        $_SESSION['login_username'] = $row['username'];
        $_SESSION['login_name'] = $row['name'];
        $_SESSION['login_type'] = $row['type'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error_message = "Incorrect username or password.";
    }
    $q->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Admin Login | Alumni Network</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="relative min-h-screen flex items-center justify-center font-sans overflow-hidden bg-gradient-to-br from-red-50 via-rose-100 to-red-200">

  <!-- Beautiful animated background shapes -->
  <div class="absolute inset-0 z-0 pointer-events-none">
      <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-gradient-to-br from-red-500 via-rose-400 to-red-600 rounded-full opacity-30 blur-3xl animate-float"></div>
      <div class="absolute bottom-0 right-0 w-[480px] h-[480px] bg-gradient-to-tr from-rose-600 via-red-400 to-pink-400 rounded-full opacity-40 blur-2xl animate-float2"></div>
      <div class="absolute top-1/2 left-1/3 w-[320px] h-[320px] bg-gradient-to-br from-pink-400 to-red-600 rounded-full opacity-25 blur-2xl animate-float3"></div>
  </div>

  <!-- Login Card -->
  <div class="w-full max-w-2xl z-10 glass-effect rounded-3xl shadow-2xl p-12 border border-red-200 backdrop-blur-lg flex flex-col items-center">

    <div class="flex flex-col items-center mb-8">
      <span class="inline-block bg-gradient-to-r from-red-600 to-rose-600 p-4 rounded-full mb-4 shadow-lg">
        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <circle cx="12" cy="8" r="4" />
          <path d="M16 21v-2a4 4 0 0 0-8 0v2"/>
        </svg>
      </span>
      <h2 class="text-4xl font-extrabold text-red-700 mt-2 mb-1 drop-shadow-lg">Admin Login</h2>
      <div class="h-1 w-16 bg-red-700 rounded opacity-40 mb-2"></div>
      <p class="text-rose-500 font-medium text-lg">Sign in to manage the alumni system</p>
    </div>

    <?php if ($error_message): ?>
      <div class="w-full mb-4 px-4 py-3 bg-red-50 border border-red-300 text-red-700 font-semibold rounded-lg text-center shadow flex items-center justify-center gap-2">
        <span>
          <svg class="w-6 h-6 text-red-500 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </span>
        <?php echo $error_message; ?>
      </div>
    <?php endif; ?>

    <form id="login-form" method="POST" autocomplete="off" class="w-full space-y-8">
      <div>
        <label for="username" class="block text-red-700 font-semibold mb-2">Username</label>
        <input type="text" id="username" name="username"
          class="w-full px-6 py-4 rounded-xl border border-red-200 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100 bg-red-50 text-gray-800 text-lg shadow"
          placeholder="Enter your username" required autofocus value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES); ?>">
      </div>
      <div>
        <label for="password" class="block text-red-700 font-semibold mb-2">Password</label>
        <input type="password" id="password" name="password"
          class="w-full px-6 py-4 rounded-xl border border-red-200 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100 bg-red-50 text-gray-800 text-lg shadow"
          placeholder="Enter your password" required>
      </div>
      <button class="w-full bg-gradient-to-r from-red-700 to-rose-600 text-white font-bold py-4 rounded-xl shadow-lg hover:from-rose-700 hover:to-red-800 transition-all duration-300 text-lg" type="submit">
        Login
      </button>
    </form>
  </div>
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
  </style>
</body>
</html>