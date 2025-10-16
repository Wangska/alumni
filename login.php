<?php
session_start();
include 'admin/db_connect.php';
$error = isset($_GET['error']) && $_GET['error'] == 1;
$unverified = isset($_GET['error']) && $_GET['error'] == 'unverified';
?>
<!DOCTYPE html>
<html lang="en" class="min-h-screen">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Alumni Network</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="relative flex items-center justify-center min-h-screen font-sans overflow-hidden">

    <!-- Beautiful animated background shapes -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-br from-red-500 via-rose-400 to-red-600 rounded-full opacity-30 blur-3xl animate-float"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-gradient-to-tr from-rose-600 via-red-400 to-pink-400 rounded-full opacity-40 blur-2xl animate-float2"></div>
        <div class="absolute top-1/2 left-1/3 w-56 h-56 bg-gradient-to-br from-pink-400 to-red-600 rounded-full opacity-25 blur-2xl animate-float3"></div>
    </div>

    <!-- Login Card -->
    <div class="w-full max-w-md z-10 glass-effect rounded-2xl shadow-2xl p-8 border border-red-200 backdrop-blur-lg">
        <div class="mb-8 text-center">
            <span class="inline-block bg-gradient-to-br from-red-600 via-rose-600 to-pink-500 p-3 rounded-full mb-2 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <circle cx="12" cy="8" r="4" />
                  <path d="M16 21v-2a4 4 0 0 0-8 0v2"/>
                </svg>
            </span>
            <h2 class="text-3xl font-bold text-red-700 mb-2 drop-shadow-lg">Alumni Network</h2>
            <p class="text-rose-500 font-medium">Sign in to your account</p>
        </div>
        <?php if($unverified): ?>
        <div class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 font-semibold rounded-lg px-4 py-3 shadow">
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
        <?php elseif($error): ?>
        <div class="mb-4 bg-red-50 border border-red-300 text-red-700 font-semibold rounded-lg px-4 py-3 text-center shadow">
            Incorrect username or password.
        </div>
        <?php endif; ?>
        <form action="authenticate.php" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-red-700 font-semibold mb-1">Username / Email</label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-3 rounded-lg border border-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 bg-red-50 text-gray-800 transition duration-200 shadow-sm" 
                    placeholder="Enter your username or email">
            </div>
            <div>
                <label for="password" class="block text-red-700 font-semibold mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 rounded-lg border border-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 bg-red-50 text-gray-800 transition duration-200 shadow-sm" 
                    placeholder="Enter your password">
            </div>
            <button type="submit"
                class="w-full bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold py-3 rounded-lg shadow-lg hover:from-red-700 hover:to-rose-700 transition-all duration-300">
                Login
            </button>
        </form>
        <div class="mt-6 flex justify-center">
            <span class="text-red-500">Don't have an account?</span>
            <a href="register.php" class="ml-2 text-red-700 font-bold hover:text-rose-800 underline transition-all">Create Account</a>
        </div>
    </div>
    <style>
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(239, 68, 68, 0.18);
        }
        @keyframes float {
            0%,100% { transform: translateY(0px) scale(1);}
            50% { transform: translateY(-24px) scale(1.04);}
        }
        @keyframes float2 {
            0%,100% { transform: translateY(0px) scale(1);}
            50% { transform: translateY(20px) scale(1.06);}
        }
        @keyframes float3 {
            0%,100% { transform: translateX(0px) scale(1);}
            50% { transform: translateX(-20px) scale(1.03);}
        }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float2 { animation: float2 11s ease-in-out infinite; }
        .animate-float3 { animation: float3 10s ease-in-out infinite; }
    </style>
</body>
</html>