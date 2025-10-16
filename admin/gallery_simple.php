<?php
// Simple gallery upload without optimization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$uploadDirUrl = 'assets/uploads/gallery';
$uploadDirFs = __DIR__ . '/assets/uploads/gallery';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) && $_POST['id'] ? intval($_POST['id']) : null;
    $about = isset($_POST['about']) ? $_POST['about'] : '';
    $imgFile = isset($_FILES['img']) ? $_FILES['img'] : null;

    $errors = [];
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDirFs)) {
        mkdir($uploadDirFs, 0777, true);
    }
    
    if ($imgFile && $imgFile['tmp_name'] && $imgFile['error'] === UPLOAD_ERR_OK) {
        // Simple file validation
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($imgFile['type'], $allowedTypes)) {
            $errors[] = "Invalid file type. Only JPEG, PNG, and GIF images are allowed.";
        }
        
        if ($imgFile['size'] > 5 * 1024 * 1024) { // 5MB
            $errors[] = "File too large. Maximum size is 5MB.";
        }
        
        if (empty($errors)) {
            $ext = pathinfo($imgFile['name'], PATHINFO_EXTENSION);
            $imgName = uniqid() . '_' . time() . '.' . $ext;
            $targetPath = $uploadDirFs . '/' . $imgName;
            
            if (move_uploaded_file($imgFile['tmp_name'], $targetPath)) {
                if ($id) {
                    // Update existing record
                    $sql = "UPDATE gallery SET about = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $about, $id);
                    $stmt->execute();
                    
                    // Remove old image
                    $oldFiles = glob($uploadDirFs . "/{$id}_*");
                    foreach ($oldFiles as $file) {
                        @unlink($file);
                    }
                    
                    // Rename new image
                    $finalName = $id . '_' . $imgName;
                    @rename($targetPath, $uploadDirFs . "/$finalName");
                    
                    $message = "Gallery updated successfully!";
                } else {
                    // Insert new record
                    $sql = "INSERT INTO gallery (about) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $about);
                    $stmt->execute();
                    $newId = $stmt->insert_id;
                    
                    // Rename new image
                    $finalName = $newId . '_' . $imgName;
                    @rename($targetPath, $uploadDirFs . "/$finalName");
                    
                    $message = "Gallery added successfully!";
                }
                
                // Redirect with success message
                header("Location: gallery.php?success=" . urlencode($message));
                exit;
            } else {
                $errors[] = "Failed to upload file.";
            }
        }
    } else {
        // No new image, just update description
        if ($id) {
            $sql = "UPDATE gallery SET about = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $about, $id);
            $stmt->execute();
            $message = "Gallery updated successfully!";
            header("Location: gallery.php?success=" . urlencode($message));
            exit;
        }
    }
    
    // If there are errors, show them
    if (!empty($errors)) {
        $errorMessage = implode('<br>', $errors);
        echo "<script>alert('$errorMessage'); window.history.back();</script>";
        exit;
    }
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
        @unlink($file);
    }
    
    header("Location: gallery.php?success=" . urlencode("Gallery deleted successfully!"));
    exit;
}

// For edit
$edit = false;
$edit_row = null;
$img = '';
if (isset($_GET['edit']) && $_GET['edit']) {
    $edit = true;
    $edit_id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM gallery WHERE id = {$edit_id}");
    $edit_row = $res->fetch_assoc();
    $files = glob($uploadDirFs . "/{$edit_id}_*");
    if ($files && count($files)) {
        $img = $uploadDirUrl . '/' . basename($files[0]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Gallery Management</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Upload Form -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">
                    <?php echo $edit ? 'Edit Image' : 'Upload Image'; ?>
                </h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $edit ? $edit_row['id'] : '' ?>">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Image</label>
                        <input type="file" name="img" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                        <img src="<?php echo $edit && $img ? $img : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5YTNhZiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='; ?>" 
                             id="preview" class="w-full h-48 object-cover rounded border">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="about" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2"><?php echo $edit ? htmlspecialchars($edit_row['about']) : '' ?></textarea>
                    </div>
                    
                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <?php echo $edit ? 'Update' : 'Upload'; ?>
                        </button>
                        <a href="gallery.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</a>
                    </div>
                </form>
            </div>
            
            <!-- Gallery List -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Gallery Collection</h2>
                
                <div class="space-y-4">
                    <?php
                    $gallery = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
                    while ($row = $gallery->fetch_assoc()):
                        $files = glob($uploadDirFs . "/{$row['id']}_*");
                        $img = $files && count($files) ? $uploadDirUrl . '/' . basename($files[0]) : '';
                    ?>
                    <div class="flex items-center gap-4 p-4 border rounded">
                        <div class="w-16 h-16 flex-shrink-0">
                            <?php if ($img): ?>
                                <img src="<?php echo $img ?>" class="w-full h-full object-cover rounded">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-200 rounded flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium"><?php echo htmlspecialchars($row['about']); ?></p>
                            <p class="text-sm text-gray-500">ID: <?php echo $row['id']; ?></p>
                        </div>
                        <div class="flex gap-2">
                            <a href="gallery.php?edit=<?php echo $row['id'] ?>" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">Edit</a>
                            <a href="gallery.php?delete=<?php echo $row['id'] ?>" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600" onclick="return confirm('Delete this image?')">Delete</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Simple image preview
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>
