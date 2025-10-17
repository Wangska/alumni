<?php
// Debug version to identify upload issues
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$uploadDirUrl = 'assets/uploads/gallery';
$uploadDirFs = __DIR__ . '/assets/uploads/gallery';

echo "<h2>Gallery Upload Debug</h2>";

// Debug information
echo "<h3>System Information:</h3>";
echo "<p>Upload Directory: $uploadDirFs</p>";
echo "<p>Directory exists: " . (is_dir($uploadDirFs) ? 'Yes' : 'No') . "</p>";
echo "<p>Directory writable: " . (is_writable($uploadDirFs) ? 'Yes' : 'No') . "</p>";
echo "<p>PHP upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>PHP post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>PHP max_execution_time: " . ini_get('max_execution_time') . "</p>";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Form Submission Debug:</h3>";
    echo "<p>POST data: " . print_r($_POST, true) . "</p>";
    echo "<p>FILES data: " . print_r($_FILES, true) . "</p>";
    
    $id = isset($_POST['id']) && $_POST['id'] ? intval($_POST['id']) : null;
    $about = isset($_POST['about']) ? $_POST['about'] : '';
    $imgFile = isset($_FILES['img']) ? $_FILES['img'] : null;

    $errors = [];
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDirFs)) {
        echo "<p>Creating directory: $uploadDirFs</p>";
        if (mkdir($uploadDirFs, 0777, true)) {
            echo "<p style='color: green;'>✓ Directory created successfully</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create directory</p>";
            $errors[] = "Failed to create upload directory.";
        }
    }
    
    if ($imgFile && $imgFile['tmp_name'] && $imgFile['error'] === UPLOAD_ERR_OK) {
        echo "<p>File upload details:</p>";
        echo "<ul>";
        echo "<li>Name: " . $imgFile['name'] . "</li>";
        echo "<li>Size: " . $imgFile['size'] . " bytes</li>";
        echo "<li>Type: " . $imgFile['type'] . "</li>";
        echo "<li>Temp name: " . $imgFile['tmp_name'] . "</li>";
        echo "<li>Error code: " . $imgFile['error'] . "</li>";
        echo "</ul>";
        
        // Simple file validation
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($imgFile['type'], $allowedTypes)) {
            $errors[] = "Invalid file type. Only JPEG, PNG, and GIF images are allowed.";
            echo "<p style='color: red;'>✗ Invalid file type: " . $imgFile['type'] . "</p>";
        }
        
        if ($imgFile['size'] > 5 * 1024 * 1024) { // 5MB
            $errors[] = "File too large. Maximum size is 5MB.";
            echo "<p style='color: red;'>✗ File too large: " . $imgFile['size'] . " bytes</p>";
        }
        
        if (empty($errors)) {
            $ext = pathinfo($imgFile['name'], PATHINFO_EXTENSION);
            $imgName = uniqid() . '_' . time() . '.' . $ext;
            $targetPath = $uploadDirFs . '/' . $imgName;
            
            echo "<p>Target path: $targetPath</p>";
            echo "<p>Temp file exists: " . (file_exists($imgFile['tmp_name']) ? 'Yes' : 'No') . "</p>";
            echo "<p>Temp file readable: " . (is_readable($imgFile['tmp_name']) ? 'Yes' : 'No') . "</p>";
            
            if (move_uploaded_file($imgFile['tmp_name'], $targetPath)) {
                echo "<p style='color: green;'>✓ File moved successfully</p>";
                
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
                    if (rename($targetPath, $uploadDirFs . "/$finalName")) {
                        echo "<p style='color: green;'>✓ File renamed to: $finalName</p>";
                    } else {
                        echo "<p style='color: orange;'>⚠ Rename failed, keeping original name</p>";
                    }
                    
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
                    if (rename($targetPath, $uploadDirFs . "/$finalName")) {
                        echo "<p style='color: green;'>✓ File renamed to: $finalName</p>";
                    } else {
                        echo "<p style='color: orange;'>⚠ Rename failed, keeping original name</p>";
                    }
                    
                    $message = "Gallery added successfully!";
                }
                
                echo "<p style='color: green; font-weight: bold;'>SUCCESS: $message</p>";
            } else {
                echo "<p style='color: red;'>✗ move_uploaded_file() failed</p>";
                echo "<p>Error details:</p>";
                echo "<ul>";
                echo "<li>Source: " . $imgFile['tmp_name'] . "</li>";
                echo "<li>Destination: " . $targetPath . "</li>";
                echo "<li>Source exists: " . (file_exists($imgFile['tmp_name']) ? 'Yes' : 'No') . "</li>";
                echo "<li>Destination writable: " . (is_writable(dirname($targetPath)) ? 'Yes' : 'No') . "</li>";
                echo "</ul>";
                $errors[] = "Failed to upload file.";
            }
        }
    } else {
        if ($imgFile) {
            echo "<p style='color: red;'>✗ Upload error: " . $imgFile['error'] . "</p>";
            switch ($imgFile['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    echo "<p>Error: File exceeds upload_max_filesize</p>";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    echo "<p>Error: File exceeds MAX_FILE_SIZE</p>";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo "<p>Error: File was only partially uploaded</p>";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo "<p>Error: No file was uploaded</p>";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    echo "<p>Error: Missing temporary folder</p>";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    echo "<p>Error: Failed to write file to disk</p>";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    echo "<p>Error: File upload stopped by extension</p>";
                    break;
            }
        } else {
            echo "<p>No file uploaded</p>";
        }
    }
    
    if (!empty($errors)) {
        echo "<h3 style='color: red;'>Errors:</h3>";
        foreach ($errors as $error) {
            echo "<p style='color: red;'>✗ $error</p>";
        }
    }
}

echo "<h3>Test Upload Form:</h3>";
echo '<form method="POST" enctype="multipart/form-data">';
echo '<p><label>Description: <input type="text" name="about" value="Debug test" required></label></p>';
echo '<p><label>Image: <input type="file" name="img" accept="image/*" required></label></p>';
echo '<p><button type="submit">Test Upload</button></p>';
echo '</form>';

echo "<h3>Current Gallery Records:</h3>";
$result = $conn->query("SELECT * FROM gallery ORDER BY id DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>About</th><th>Files</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $files = glob($uploadDirFs . "/{$row['id']}_*");
        $fileList = $files ? implode(', ', array_map('basename', $files)) : 'No files';
        echo "<tr><td>{$row['id']}</td><td>{$row['about']}</td><td>$fileList</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No gallery records found.</p>";
}

echo "<p><a href='gallery.php'>← Back to Gallery</a></p>";
?>
