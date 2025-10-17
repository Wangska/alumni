<?php
// Script to fix upload directory permissions
echo "<h2>Fixing Upload Directory Permissions</h2>";

$uploadDir = __DIR__ . '/assets/uploads/gallery';
$parentDir = __DIR__ . '/assets/uploads';

echo "<h3>Current Status:</h3>";
echo "<p>Upload Directory: $uploadDir</p>";
echo "<p>Directory exists: " . (is_dir($uploadDir) ? 'Yes' : 'No') . "</p>";
echo "<p>Directory writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "</p>";

// Create parent directory if it doesn't exist
if (!is_dir($parentDir)) {
    echo "<p>Creating parent directory: $parentDir</p>";
    if (mkdir($parentDir, 0777, true)) {
        echo "<p style='color: green;'>✓ Parent directory created</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create parent directory</p>";
    }
}

// Create gallery directory if it doesn't exist
if (!is_dir($uploadDir)) {
    echo "<p>Creating gallery directory: $uploadDir</p>";
    if (mkdir($uploadDir, 0777, true)) {
        echo "<p style='color: green;'>✓ Gallery directory created</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create gallery directory</p>";
    }
}

// Try to fix permissions
echo "<h3>Fixing Permissions:</h3>";

// Method 1: Try chmod
if (is_dir($uploadDir)) {
    if (@chmod($uploadDir, 0777)) {
        echo "<p style='color: green;'>✓ Set permissions to 0777</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Could not change permissions (this is normal in some hosting environments)</p>";
    }
    
    // Check if writable now
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✓ Directory is now writable</p>";
    } else {
        echo "<p style='color: red;'>✗ Directory is still not writable</p>";
        
        // Try alternative approach - create a test file
        $testFile = $uploadDir . '/test_write.txt';
        if (file_put_contents($testFile, 'test') !== false) {
            echo "<p style='color: green;'>✓ Can write files to directory</p>";
            unlink($testFile);
        } else {
            echo "<p style='color: red;'>✗ Cannot write files to directory</p>";
        }
    }
}

// Show manual fix commands
echo "<h3>Manual Fix Commands:</h3>";
echo "<p>If the above didn't work, run these commands on your server:</p>";
echo "<pre>";
echo "chmod -R 0777 " . dirname($uploadDir) . "\n";
echo "chown -R www-data:www-data " . dirname($uploadDir) . "\n";
echo "chown -R apache:apache " . dirname($uploadDir) . "\n";
echo "</pre>";

// Test upload functionality
echo "<h3>Test Upload:</h3>";
echo '<form method="POST" enctype="multipart/form-data">';
echo '<p><label>Test File: <input type="file" name="testfile" accept="image/*"></label></p>';
echo '<p><button type="submit">Test Upload</button></p>';
echo '</form>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['testfile'])) {
    $testFile = $_FILES['testfile'];
    if ($testFile['error'] === UPLOAD_ERR_OK) {
        $testPath = $uploadDir . '/test_' . time() . '.jpg';
        if (move_uploaded_file($testFile['tmp_name'], $testPath)) {
            echo "<p style='color: green;'>✓ Test upload successful!</p>";
            unlink($testPath); // Clean up
        } else {
            echo "<p style='color: red;'>✗ Test upload failed</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Upload error: " . $testFile['error'] . "</p>";
    }
}

echo "<p><a href='gallery.php'>← Back to Gallery</a></p>";
?>
