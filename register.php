<?php
session_start();
include 'admin/db_connect.php';

// Fetch all courses from the courses table
$courses = [];
$res_courses = $conn->query("SELECT id, course, about FROM courses ORDER BY course ASC");
if ($res_courses) {
    while ($row = $res_courses->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="min-h-screen">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Alumni Network</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/4d7b8f4f8f.js" crossorigin="anonymous"></script>
</head>
<body class="relative flex items-center justify-center min-h-screen font-sans overflow-hidden">

        <!-- Beautiful animated background shapes -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-gradient-to-br from-red-500 via-rose-400 to-red-600 rounded-full opacity-30 blur-3xl animate-float"></div>
            <div class="absolute bottom-0 right-0 w-[480px] h-[480px] bg-gradient-to-tr from-rose-600 via-red-400 to-pink-400 rounded-full opacity-40 blur-2xl animate-float2"></div>
            <div class="absolute top-1/2 left-1/3 w-[320px] h-[320px] bg-gradient-to-br from-pink-400 to-red-600 rounded-full opacity-25 blur-2xl animate-float3"></div>
        </div>

        <!-- Create Account Card -->
        <div class="w-full max-w-4xl z-10 glass-effect rounded-3xl shadow-2xl p-12 border border-red-200 backdrop-blur-lg overflow-y-auto max-h-[92vh]">

            <div class="text-center shrink-0 mb-8">
                <span class="inline-block bg-gradient-to-r from-red-600 to-rose-600 p-4 rounded-full mb-4 shadow-lg">
                    <i class="fas fa-user-plus text-white text-4xl"></i>
                </span>
                <h2 class="text-4xl font-extrabold text-red-700 mb-2 drop-shadow-lg">Create New Account</h2>
                <p class="text-rose-500 font-medium mb-8 text-lg">Sign up to get started and join the alumni network</p>
            </div>
            <form action="register_save.php" method="POST" id="create-account-frm" enctype="multipart/form-data"
                class="space-y-8">
                <script>
                // Populate Batch Year Dropdown with School Years
                function populateBatchYears() {
                    const batchSelect = document.getElementById('batch_year');
                    const currentYear = new Date().getFullYear();
                    const startYear = 1950; // Starting from 1950 or adjust as needed
                    
                    // Clear existing options except the first one
                    batchSelect.innerHTML = '<option value="">-- Select Batch Year --</option>';
                    
                    // Generate school years from current year down to start year
                    for (let year = currentYear; year >= startYear; year--) {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year + ' - ' + (year + 1); // Display as "2024 - 2025"
                        batchSelect.appendChild(option);
                    }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    // Populate batch years on load
                    populateBatchYears();

                    // Form validation
                    document.getElementById('create-account-frm').addEventListener('submit', function(e) {
                            var password = this.password.value;
                            var confirm = this.confirm_password.value;
                            var uppercase = /[A-Z]/.test(password);
                            var number = /[0-9]/.test(password);
                            if (password.length < 8) {
                                alert('Password must be at least 8 characters long.');
                                e.preventDefault();
                                return false;
                            }
                            if (!uppercase || !number) {
                                alert('Password must contain at least one uppercase letter and one number.');
                                e.preventDefault();
                                return false;
                            }
                            if (password !== confirm) {
                                alert('Passwords do not match.');
                                e.preventDefault();
                                return false;
                            }
                        });
                });
                </script>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="firstname" required class="w-full px-6 py-4 border border-red-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-red-50 text-lg" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Middle Name</label>
                        <input type="text" name="middlename" class="w-full px-6 py-4 border border-gray-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-red-50 text-lg" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="lastname" required class="w-full px-6 py-4 border border-red-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-red-50 text-lg" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Gender <span class="text-red-500">*</span></label>
                        <select name="gender" required class="w-full px-6 py-4 border border-red-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-white text-lg">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Batch / School Year <span class="text-red-500">*</span></label>
                        <select name="batch" id="batch_year" required class="w-full px-6 py-4 border border-red-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-white text-lg">
                            <option value="">-- Select Batch Year --</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Select your graduation year</p>
                    </div>
                    <div>
                        <label for="course_dropdown" class="block text-gray-700 font-semibold mb-2">Course <span class="text-red-500">*</span></label>
                        <select id="course_dropdown" name="course_id" required class="w-full px-6 py-4 border border-red-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-white text-lg">
                            <option value="">-- Choose Course --</option>
                            <?php foreach($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>">
                                    <?php echo htmlspecialchars($course['course']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if(count($courses) === 0): ?>
                            <div class="text-center py-4 text-gray-400">
                                <i class="fas fa-graduation-cap text-2xl mb-2"></i>
                                <p>No courses available at the moment.</p>
                            </div>
                        <?php endif; ?>
                        <div id="course_desc" class="hidden mt-2 p-2 rounded-lg bg-red-50 border-l-4 border-red-500 text-gray-700 shadow"></div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('course_dropdown').addEventListener('change', function() {
                                    var selectedId = this.value;
                                    var courses = <?php echo json_encode($courses); ?>;
                                    var descDiv = document.getElementById('course_desc');
                                    var selectedCourse = courses.find(function(c){ return c.id == selectedId; });
                                    if (selectedCourse) {
                                        descDiv.innerHTML = "<strong>Description:</strong> " + (selectedCourse.about ? selectedCourse.about : "No description available.");
                                        descDiv.classList.remove('hidden');
                                    } else {
                                        descDiv.innerHTML = "";
                                        descDiv.classList.add('hidden');
                                    }
                                });
                            });
                        </script>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="w-full px-6 py-4 border border-red-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-red-50 text-lg" placeholder="Enter your email address" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required class="w-full px-6 py-4 border border-red-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-red-50 text-lg" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Contact Number <span class="text-red-500">*</span></label>
                        <input type="tel" name="contact" id="contact" required pattern="[0-9+()\-\s]{7,20}" title="Enter a valid phone number" class="w-full px-6 py-4 border border-red-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-red-50 text-lg" placeholder="e.g. +63 912 345 6789" />
                    </div>
                    <div>
                        <!-- placeholder column to keep layout balanced -->
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Current Company Connected To / Current School Connected To</label>
                    <input type="text" name="connected_to" placeholder="e.g. Saint Ce Celia" class="w-full px-6 py-4 border border-gray-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-red-50 text-lg" />
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Avatar/Image</label>
                    <div class="flex items-center gap-4">
                        <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-red-100 file:to-rose-100 file:text-red-700 hover:file:bg-red-200" />
                        <img id="avatarPreview" src="" alt="" class="hidden w-16 h-16 rounded-full object-cover border border-red-200 shadow" />
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            document.querySelector('input[name="avatar"]').addEventListener('change', function(e) {
                                var file = e.target.files[0];
                                var reader = new FileReader();
                                reader.onload = function(ev) {
                                    var preview = document.getElementById('avatarPreview');
                                    preview.src = ev.target.result;
                                    preview.classList.remove('hidden');
                                };
                                if (file) reader.readAsDataURL(file);
                            });
                        });
                    </script>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-2">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required class="w-full px-6 py-4 border border-red-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-red-50 text-lg" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="confirm_password" required class="w-full px-6 py-4 border border-red-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow bg-red-50 text-lg" />
                    </div>
                </div>
                <div class="flex space-x-6 pt-8">
                    <button type="submit"
                        class="flex-1 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold py-4 rounded-xl shadow-md transition-all duration-300 transform hover:scale-105 flex items-center justify-center relative group text-lg">
                        <span class="btn-text">Create Account</span>
                        <span class="absolute right-4 hidden loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                    <a href="login.php" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-4 rounded-xl border border-gray-300 transition-all duration-300 text-center flex items-center justify-center text-lg">
                        Cancel
                    </a>
                </div>
                <div class="text-center pt-8 text-gray-600 text-base">
                    Already have an account?
                    <a href="login.php" class="text-red-600 hover:underline font-semibold">Sign In</a>
                </div>
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