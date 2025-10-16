<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session already started in index.php, don't start again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// Helper functions for image processing and validation
function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "File too large.";
        case UPLOAD_ERR_PARTIAL:
            return "File upload was incomplete.";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing temporary folder.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk.";
        case UPLOAD_ERR_EXTENSION:
            return "File upload stopped by extension.";
        default:
            return "Unknown upload error.";
    }
}

function optimizeImage($sourcePath, $targetPath) {
    if (!function_exists('imagecreatefromjpeg')) {
        return false; // GD extension not available
    }
    
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return false;
    }
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $mimeType = $imageInfo['mime'];
    
    // Create image resource based on type
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            $sourceImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    if (!$sourceImage) {
        return false;
    }
    
    // Calculate new dimensions (max 1920x1080, maintain aspect ratio)
    $maxWidth = 1920;
    $maxHeight = 1080;
    
    if ($width <= $maxWidth && $height <= $maxHeight) {
        // Image is already small enough, just copy
        $newWidth = $width;
        $newHeight = $height;
    } else {
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = intval($width * $ratio);
        $newHeight = intval($height * $ratio);
    }
    
    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Resize image
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save optimized image
    $result = false;
    switch ($mimeType) {
        case 'image/jpeg':
            $result = imagejpeg($newImage, $targetPath, 85); // 85% quality
            break;
        case 'image/png':
            $result = imagepng($newImage, $targetPath, 8); // 8 compression level
            break;
        case 'image/gif':
            $result = imagegif($newImage, $targetPath);
            break;
        case 'image/webp':
            $result = imagewebp($newImage, $targetPath, 85); // 85% quality
            break;
    }
    
    // Clean up memory
    imagedestroy($sourceImage);
    imagedestroy($newImage);
    
    return $result;
}

// Use filesystem-safe absolute path for saving, and URL path for displaying
$uploadDirUrl = 'assets/uploads/gallery';
$uploadDirFs  = __DIR__ . '/assets/uploads/gallery';

// Handle form submission for insert/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) && $_POST['id'] ? intval($_POST['id']) : null;
    $about = isset($_POST['about']) ? $_POST['about'] : '';
    $imgFile = isset($_FILES['img']) ? $_FILES['img'] : null;

    // Validate and upload image
    $imgName = '';
    $uploadErrors = [];
    
    if ($imgFile && $imgFile['tmp_name']) {
        // File validation
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Check file type
        if (!in_array($imgFile['type'], $allowedTypes)) {
            $uploadErrors[] = "Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.";
        }
        
        // Check file size
        if ($imgFile['size'] > $maxSize) {
            $uploadErrors[] = "File too large. Maximum size is 5MB.";
        }
        
        // Check for upload errors
        if ($imgFile['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors[] = "Upload error: " . getUploadErrorMessage($imgFile['error']);
        }
        
        // If no errors, process the image
        if (empty($uploadErrors)) {
            $ext = pathinfo($imgFile['name'], PATHINFO_EXTENSION);
            $imgName = uniqid() . '_' . time() . '.' . $ext;
            
            // Ensure directory exists
            if (!is_dir($uploadDirFs)) {
                if (!mkdir($uploadDirFs, 0777, true)) {
                    $uploadErrors[] = "Failed to create upload directory.";
                }
            }
            
            $targetPath = $uploadDirFs . '/' . $imgName;
            
            // Check if we can write to the directory
            if (!is_writable($uploadDirFs)) {
                $uploadErrors[] = "Upload directory is not writable. Please run <a href='fix_permissions.php' target='_blank'>fix_permissions.php</a> to fix this issue.";
            }
            
            if (empty($uploadErrors)) {
                $uploadSuccess = false;
                
                // Try multiple upload methods
                if (move_uploaded_file($imgFile['tmp_name'], $targetPath)) {
                    $uploadSuccess = true;
                } else {
                    // Try copy method
                    if (copy($imgFile['tmp_name'], $targetPath)) {
                        unlink($imgFile['tmp_name']); // Clean up temp file
                        $uploadSuccess = true;
                    } else {
                        // Try file_get_contents method
                        $tempContent = file_get_contents($imgFile['tmp_name']);
                        if ($tempContent !== false && file_put_contents($targetPath, $tempContent) !== false) {
                            unlink($imgFile['tmp_name']); // Clean up temp file
                            $uploadSuccess = true;
                        }
                    }
                }
                
                if ($uploadSuccess) {
                    // Resize and optimize image
                    $optimized = optimizeImage($targetPath, $targetPath);
                    if (!$optimized) {
                        $uploadErrors[] = "Failed to optimize image.";
                    }
                } else {
                    $uploadErrors[] = "Failed to save uploaded file. Please check directory permissions or run <a href='fix_permissions.php' target='_blank'>fix_permissions.php</a>.";
                }
            }
        }
    }

    // Handle errors
    if (!empty($uploadErrors)) {
        $errorMessage = implode('<br>', $uploadErrors);
        echo "<script>
            window.onload = function() { 
                showModal('$errorMessage'); 
                setTimeout(function(){ 
                    window.location.href = 'gallery.php'; 
                }, 5000); 
            }
        </script>";
        exit;
    }

    if ($id) {
        // Update
        $sql = "UPDATE gallery SET about = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $about, $id);
        $stmt->execute();
        if ($imgName) {
            // Remove previous image
            $files = glob($uploadDirFs . "/{$id}_*");
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }
            // Rename to include ID prefix
            $finalName = $id . '_' . $imgName;
            rename($targetPath, $uploadDirFs . "/$finalName");
        }
        $message = "Gallery updated successfully!";
    } else {
        // Insert
        $sql = "INSERT INTO gallery (about) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $about);
        $stmt->execute();
        $newId = $stmt->insert_id;
        if ($imgName) {
            // Rename to include ID prefix
            $finalName = $newId . '_' . $imgName;
            rename($targetPath, $uploadDirFs . "/$finalName");
        }
        $message = "Gallery added successfully!";
    }
    echo "<script>
        window.onload = function() { 
            showModal('$message'); 
            setTimeout(function(){ 
                window.location.href = 'gallery.php'; 
            }, 2000); 
        }
    </script>";
    echo "<noscript><meta http-equiv='refresh' content='3;url=gallery.php'></noscript>";
}

// Handle delete
if (isset($_GET['delete']) && $_GET['delete']) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    // Remove image files
    $files = glob($uploadDirFs . "/{$id}_*");
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
    echo "<script>
        window.onload = function() { 
            showModal('Gallery deleted successfully!'); 
            setTimeout(function(){ 
                window.location.href = 'gallery.php'; 
            }, 2000); 
        }
    </script>";
    echo "<noscript><meta http-equiv='refresh' content='3;url=gallery.php'></noscript>";
}

// For edit
$edit = false;
if (isset($_GET['edit']) && $_GET['edit']) {
    $edit = true;
    $edit_id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM gallery WHERE id = {$edit_id}");
    $edit_row = $res->fetch_assoc();
    $img = '';
    $files = glob($uploadDirFs . "/{$edit_id}_*");
    if ($files && count($files)) $img = $uploadDirUrl . '/' . basename($files[0]);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alumni Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d'
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                        'pulse-red': 'pulseRed 2s infinite',
                        'float': 'float 3s ease-in-out infinite'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(40px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        bounceGentle: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' }
                        },
                        pulseRed: {
                            '0%, 100%': { boxShadow: '0 0 0 0 rgba(239, 68, 68, 0.4)' },
                            '50%': { boxShadow: '0 0 0 15px rgba(239, 68, 68, 0)' }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans pt-24 px-4 md:px-8 min-h-screen">

    <!-- Header/Navbar -->
    <header class="fixed top-0 left-0 w-full h-16 z-50 px-4 md:px-6 flex items-center bg-gradient-to-r from-primary-900 via-primary-700 to-primary-500 shadow-lg backdrop-blur-lg">
        <div class="max-w-7xl w-full mx-auto flex items-center justify-between">
            <!-- Logo Section -->
            <div class="flex items-center space-x-4">
                <div class="bg-white shadow w-10 h-10 rounded-full flex items-center justify-center text-xl text-primary-600 border-2 border-primary-100 hover:scale-105 transition-transform">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="hidden md:block">
                    <h1 class="bg-gradient-to-r from-primary-500 to-primary-700 bg-clip-text text-transparent text-xl font-bold drop-shadow">
                        <?php echo isset($_SESSION['system']['name']) ? $_SESSION['system']['name'] : 'Alumni Management System' ?>
                    </h1>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="bg-green-500 w-2 h-2 rounded-full inline-block animate-pulse"></span>
                        <span class="text-primary-50 text-xs font-medium">System Online</span>
                    </div>
                </div>
            </div>
            <!-- Quick Stats -->
            <div class="hidden lg:flex items-center space-x-4 text-white">
                <div class="flex items-center space-x-2 bg-white/10 rounded-full px-2 py-1 backdrop-blur-sm">
                    <i class="fas fa-users text-primary-200"></i>
                    <span class="text-xs font-medium">
                        <?php echo isset($conn) ? $conn->query("SELECT * FROM alumnus_bio where status = 1")->num_rows : '0' ?> Alumni
                    </span>
                </div>
                <div class="flex items-center space-x-2 bg-white/10 rounded-full px-2 py-1 backdrop-blur-sm">
                    <i class="fas fa-calendar text-primary-200"></i>
                    <span class="text-xs font-medium">
                        <?php echo isset($conn) ? $conn->query("SELECT * FROM events where date_format(schedule,'%Y-%m-%d') >= '".date('Y-m-d')."' ")->num_rows : '0' ?> Events
                    </span>
                </div>
            </div>
            <!-- User Section -->
            <div class="flex items-center space-x-4">
                <div class="hidden md:flex items-center space-x-3 px-4 py-2 bg-white bg-opacity-10 rounded-lg backdrop-blur">
                    <i class="fas fa-user-circle text-white text-2xl"></i>
                    <div class="text-left">
                        <p class="text-white text-sm font-semibold"><?php echo isset($_SESSION['login_name']) ? $_SESSION['login_name'] : 'Admin' ?></p>
                        <p class="text-primary-100 text-xs">Administrator</p>
                    </div>
                </div>
                <a href="ajax.php?action=logout" class="flex items-center gap-2 px-4 py-2 bg-white bg-opacity-10 hover:bg-opacity-20 rounded-lg text-white transition">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="hidden md:inline">Logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-64 bg-white shadow-xl border-r border-primary-100 z-40 flex flex-col px-4 py-8 space-y-6 overflow-y-auto">

        <div class="text-center pb-6 border-b border-primary-100">
            <h2 class="text-primary-600 text-xl font-bold">Dashboard</h2>
            <p class="text-gray-500 text-sm">Admin Panel</p>
        </div>
        <nav class="flex flex-col gap-2">
            <a href="dashboard.php" class="nav-item nav-courses flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-dashboard"></i> 
                <span>Dashboard</span>
            </a>
            <a href="gallery.php" class="nav-item nav-home active flex items-center gap-3 px-4 py-3 rounded-lg text-primary-700 bg-gradient-to-r from-primary-100 to-primary-50 font-medium hover:bg-primary-200 transition">
                <i class="fas fa-images"></i> 
                <span>Gallery</span>
            </a>
            <a href="courses.php" class="nav-item nav-courses flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-graduation-cap"></i> 
                <span>Course List</span>
            </a>
            <a href="alumni.php" class="nav-item nav-alumni flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-user-friends"></i> 
                <span>Alumni List</span>
            </a>
              <a href="jobs.php" class="nav-item nav-jobs flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-briefcase"></i> 
                <span>Jobs</span>
            </a>
            <a href="events.php" class="nav-item nav-events flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-calendar-alt"></i> 
                <span>Events</span>
            </a>
            <a href="index.php?page=forums" class="nav-item nav-forums flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-comments"></i> 
                <span>Forum</span>
            </a>
            <a href="index.php?page=users" class="nav-item nav-users flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:text-primary-700 hover:bg-primary-100 transition">
                <i class="fas fa-users-cog"></i> 
                <span>Users</span>
            </a>
        </nav>
    </aside>

<!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
      <div class="bg-white rounded-2xl shadow-2xl border border-red-200 px-8 py-6 flex flex-col items-center animate-fade-in">
        <i class="fas fa-check-circle text-5xl text-red-500 mb-4 animate-pulse"></i>
        <h3 class="text-2xl font-bold text-red-700 mb-2">Success</h3>
        <div id="successMessage" class="text-lg text-gray-700 mb-3"></div>
        <button onclick="closeModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2 rounded-lg shadow">Close</button>
      </div>
    </div>

    <main class="ml-0 md:ml-64 pt-10 transition-all min-h-screen">
    <div class="w-full px-2 md:px-6 lg:px-10">
            <div class="mb-10">
                <div class="flex items-center gap-4 mb-3">
                    <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-images text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-red-900">Gallery Management</h1>
                        <p class="text-red-600">Upload and manage your gallery images</p>
                    </div>
                </div>
            </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
            <!-- Upload/Edit Form -->
            <div class="xl:col-span-1">
                <form method="POST" enctype="multipart/form-data"  class="bg-white shadow-lg rounded-3xl overflow-hidden border border-red-200 hover:shadow-2xl transition-all duration-300 flex flex-col h-full">
                    <input type="hidden" name="id" value="<?php echo $edit ? $edit_row['id'] : '' ?>">
                     <!-- Header -->
                    <div class="bg-gradient-to-r from-red-500 to-red-600 px-8 py-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3">
                            <i class="fas fa-upload text-red-100"></i>
                            <?php echo $edit ? 'Edit Image' : 'Upload Image'; ?>
                        </h3>
                    </div>

                    <!-- Body -->
                    <div class="px-8 py-8 space-y-8 flex-1">
                        <div>
                            <label class="block font-semibold text-red-900 mb-1">Select Image</label>
                            <input type="file" 
                                class="block w-full text-sm border-2 border-red-200 rounded-2xl cursor-pointer bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-100" 
                                name="img" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                                onchange="validateAndDisplayImage(this)">
                            <div id="fileValidation" class="mt-2 text-sm hidden"></div>
                        </div>
                        <div>
                            <label class="block font-semibold text-red-900 mb-1">Preview</label>
                            <img src="<?php echo ($edit && $img) ? $img : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5YTNhZiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==' ?>" 
                                id="cimg" 
                                class="w-full h-48 object-cover rounded-2xl border-2 border-red-200 bg-red-50 shadow-sm" 
                                alt="Image Preview">
                        </div>
                        <div>
                            <label class="block font-semibold text-red-900 mb-1">Description</label>
                            <textarea 
                                class="w-full border-2 border-red-200 rounded-2xl px-5 py-4 bg-red-50/30" 
                                name="about" rows="4"><?php echo $edit ? $edit_row['about'] : '' ?></textarea>
                        </div>
                    </div>
                    <!-- Footer -->
                    <div class="px-8 py-6 bg-red-50 border-t-2 border-red-100 flex justify-end gap-4">
                        <button class="bg-gradient-to-r from-red-600 to-red-700 text-white font-bold px-8 py-3 rounded-2xl shadow-lg" type="submit">
                            <i class="fas fa-save"></i> <?php echo $edit ? 'Update' : 'Save'; ?>
                        </button>
                        <a href="gallery.php" 
                        class="bg-gray-100 text-gray-700 font-semibold px-8 py-3 rounded-2xl border-2 border-gray-200 hover:bg-gray-200 transition-all duration-300 flex items-center gap-3">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            <!-- Gallery Table Panel -->
            <div class="xl:col-span-2">
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-red-100 hover:shadow-2xl transition-all duration-300">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-red-500 to-red-600 px-8 py-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3">
                            <i class="fas fa-images text-red-100"></i>
                            Gallery Collection
                        </h3>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-6">
                        <div class="overflow-hidden rounded-2xl border border-red-100 shadow-sm">
                            <table class="min-w-full text-sm">
                                <!-- Table Head -->
                                <thead class="bg-gradient-to-r from-red-50 to-rose-50">
                                    <tr>
                                        <th class="px-6 py-4 font-semibold text-red-900 text-center">#</th>
                                        <th class="px-6 py-4 font-semibold text-red-900 text-center">Image</th>
                                        <th class="px-6 py-4 font-semibold text-red-900 text-center">Description</th>
                                        <th class="px-6 py-4 font-semibold text-red-900 text-center">Actions</th>
                                    </tr>
                                </thead>

                                <!-- Table Body -->
                                <tbody class="divide-y divide-red-50 bg-white">
                                    <?php 
                                    $limit = 5;
                                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    $offset = ($page - 1) * $limit;

                                    $result = $conn->query("SELECT COUNT(*) as total FROM gallery");
                                    $total_records = $result->fetch_assoc()['total'];
                                    $total_pages = ceil($total_records / $limit);

                                    $i = $offset + 1;
                                    $gallery = $conn->query("SELECT * FROM gallery ORDER BY id ASC LIMIT $limit OFFSET $offset");
                                    while ($row = $gallery->fetch_assoc()):
                                        $img = '';
                                        $files = glob($uploadDirFs . "/{$row['id']}_*");
                                        if ($files && count($files)) {
                                            $img = $uploadDirUrl . '/' . basename($files[0]);
                                        }
                                    ?>
                                    <tr class="hover:bg-red-50/40 transition-colors">
                                        <td class="px-6 py-5 text-center font-bold text-red-900"><?php echo $i++ ?></td>
                                        <td class="px-6 py-5 text-center">
                                            <?php if ($img): ?>
                                                <img src="<?php echo $img ?>" class="w-16 h-16 object-cover rounded-xl border border-red-200 bg-red-50 shadow-sm mx-auto" alt="">
                                            <?php else: ?>
                                                <span class="text-gray-400 italic">No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-5 text-gray-700 max-w-xs truncate"><?php echo $row['about'] ?></td>
                                        <td class="px-6 py-5 text-center space-x-2">
                                            <a href="gallery.php?edit=<?php echo $row['id'] ?>" 
                                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow hover:from-red-600 hover:to-red-700 transition-all">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button 
                                                type="button"
                                                onclick="openDeleteModal(<?php echo $row['id'] ?>)"
                                                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-gradient-to-r from-rose-500 to-rose-600 rounded-lg shadow hover:from-rose-600 hover:to-rose-700 transition-all">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="flex justify-center mt-8 space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1 ?>" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-200 transition">
                                Previous
                                </a>
                            <?php endif; ?>

                            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                <a href="?page=<?php echo $p ?>" 
                                class="px-4 py-2 border rounded-lg transition <?php echo $p == $page ? 'bg-red-600 text-white border-red-600' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                                    <?php echo $p ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1 ?>" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-200 transition">
                                Next
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <div id="deleteModal" class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center hidden">
            <div class="bg-white rounded-2xl shadow-2xl border border-red-200 px-8 py-6 flex flex-col items-center animate-fade-in">
                <i class="fas fa-exclamation-triangle text-5xl text-rose-600 mb-4 animate-pulse"></i>
                <h3 class="text-2xl font-bold text-rose-700 mb-2">Delete Image?</h3>
                <div class="text-lg text-gray-700 mb-3 text-center">Are you sure you want to delete this image? This action cannot be undone.</div>
                <div class="flex gap-4 mt-4">
                <button onclick="closeDeleteModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-6 py-2 rounded-lg shadow">Cancel</button>
                <a id="deleteBtn" href="#" class="bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-bold px-6 py-2 rounded-lg shadow">Delete</a>
                </div>
            </div>
        </div>
    </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        function openDeleteModal(id) {
        document.getElementById('deleteBtn').href = 'gallery.php?delete=' + id;
        document.getElementById('deleteModal').classList.remove('hidden');
        }
        function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        }



        function showModal(msg) {
            document.getElementById('successMessage').textContent = msg;
            document.getElementById('successModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('successModal').classList.add('hidden');
        }
        function validateAndDisplayImage(input) {
            const validationDiv = document.getElementById('fileValidation');
            const previewImg = document.getElementById('cimg');
            
            // Clear previous validation
            validationDiv.classList.add('hidden');
            validationDiv.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                
                // Validate file type
                if (!allowedTypes.includes(file.type)) {
                    validationDiv.innerHTML = '<span class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.</span>';
                    validationDiv.classList.remove('hidden');
                    return;
                }
                
                // Validate file size
                if (file.size > maxSize) {
                    validationDiv.innerHTML = '<span class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>File too large. Maximum size is 5MB.</span>';
                    validationDiv.classList.remove('hidden');
                    return;
                }
                
                // Show success message
                validationDiv.innerHTML = '<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>File is valid and will be optimized on upload.</span>';
                validationDiv.classList.remove('hidden');
                
                // Display preview
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }
        
        function displayImg(input) {
            if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('cimg').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
            }
        }

        // Dropdown toggle logic
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdown_menu');
            if (dropdown.classList.contains('opacity-0')) {
                dropdown.classList.remove('opacity-0', 'invisible');
                dropdown.classList.add('opacity-100', 'visible');
            } else {
                dropdown.classList.add('opacity-0', 'invisible');
                dropdown.classList.remove('opacity-100', 'visible');
            }
        }
        document.addEventListener('click', function(event){
            const btn = document.getElementById('account_settings');
            const menu = document.getElementById('dropdown_menu');
            if (!btn.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('opacity-0', 'invisible');
                menu.classList.remove('opacity-100', 'visible');
            }
        });
        function closeAlert() {
            const alert = document.getElementById('successAlert');
            if (alert) {
                alert.classList.add('opacity-0');
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        }
        setTimeout(() => { closeAlert(); }, 5000);
        document.getElementById('manage_my_account')?.addEventListener('click', function() {
            uni_modal("Manage Account", "manage_user.php?id=<?php echo isset($_SESSION['login_id']) ? $_SESSION['login_id'] : '1' ?>&mtype=own");
            toggleDropdown();
        });
        // Dummy modal
        function uni_modal(title, url) {
            alert(`Would open modal: ${title}`);
        }

        // Remove duplicate function - using the one above
	$('#manage-gallery').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=save_gallery',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully added",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
				else if(resp==2){
					alert_toast("Data successfully updated",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	})
	$('.edit_gallery').click(function(){
		start_load()
		var cat = $('#manage-gallery')
		cat.get(0).reset()
		cat.find("[name='id']").val($(this).attr('data-id'))
		cat.find("[name='about']").val($(this).attr('data-about'))
		cat.find("img").attr('src',$(this).attr('data-src'))
		end_load()
	})
	$('.delete_gallery').click(function(){
		_conf("Are you sure to delete this data?","delete_gallery",[$(this).attr('data-id')])
	})
	function delete_gallery($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_gallery',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
	$('table').dataTable()
    </script>
</body>
</html>