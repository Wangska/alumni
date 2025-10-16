<?php
// Simple test script to verify gallery upload works
include 'db_connect.php';

echo "<h2>Gallery Upload Test</h2>";

$uploadDirFs = __DIR__ . '/assets/uploads/gallery';
$uploadDirUrl = 'assets/uploads/gallery';

echo "<p>Upload Directory: $uploadDirFs</p>";
echo "<p>Directory exists: " . (is_dir($uploadDirFs) ? 'Yes' : 'No') . "</p>";
echo "<p>Directory writable: " . (is_writable($uploadDirFs) ? 'Yes' : 'No') . "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['img'])) {
    $imgFile = $_FILES['img'];
    $about = $_POST['about'] ?? 'Test upload';
    
    echo "<h3>Upload Test Results:</h3>";
    echo "<p>File name: " . $imgFile['name'] . "</p>";
    echo "<p>File size: " . $imgFile['size'] . " bytes</p>";
    echo "<p>File type: " . $imgFile['type'] . "</p>";
    echo "<p>Upload error: " . $imgFile['error'] . "</p>";
    
    if ($imgFile['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($imgFile['name'], PATHINFO_EXTENSION);
        $imgName = uniqid() . '_' . time() . '.' . $ext;
        $targetPath = $uploadDirFs . '/' . $imgName;
        
        echo "<p>Target path: $targetPath</p>";
        
        if (move_uploaded_file($imgFile['tmp_name'], $targetPath)) {
            echo "<p style='color: green;'>✓ File uploaded successfully!</p>";
            
            // Insert into database
            $sql = "INSERT INTO gallery (about) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $about);
            $stmt->execute();
            $newId = $stmt->insert_id;
            
            // Rename file
            $finalName = $newId . '_' . $imgName;
            if (rename($targetPath, $uploadDirFs . "/$finalName")) {
                echo "<p style='color: green;'>✓ File renamed to: $finalName</p>";
                echo "<p style='color: green;'>✓ Database record created with ID: $newId</p>";
            } else {
                echo "<p style='color: orange;'>⚠ File uploaded but rename failed</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Upload failed</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Upload error: " . $imgFile['error'] . "</p>";
    }
}

echo "<h3>Test Upload Form:</h3>";
echo '<form method="POST" enctype="multipart/form-data">';
echo '<p><label>Description: <input type="text" name="about" value="Test upload" required></label></p>';
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
