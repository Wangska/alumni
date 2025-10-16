<?php
// Script to fix upload directory permissions
echo "<h2>Fixing Upload Directory Permissions</h2>";

$uploadDir = __DIR__ . '/assets/uploads/gallery';
$parentDir = __DIR__ . '/assets/uploads';

echo "<p>Checking directory: " . $uploadDir . "</p>";

// Create parent directory if it doesn't exist
if (!is_dir($parentDir)) {
    if (mkdir($parentDir, 0777, true)) {
        echo "<p style='color: green;'>✓ Created parent directory: $parentDir</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create parent directory: $parentDir</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Parent directory exists: $parentDir</p>";
}

// Create gallery directory if it doesn't exist
if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0777, true)) {
        echo "<p style='color: green;'>✓ Created gallery directory: $uploadDir</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create gallery directory: $uploadDir</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Gallery directory exists: $uploadDir</p>";
}

// Set permissions
if (is_dir($uploadDir)) {
    if (chmod($uploadDir, 0777)) {
        echo "<p style='color: green;'>✓ Set permissions on gallery directory</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to set permissions on gallery directory</p>";
    }
    
    // Check if writable
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✓ Gallery directory is writable</p>";
    } else {
        echo "<p style='color: red;'>✗ Gallery directory is NOT writable</p>";
    }
    
    // Test file creation
    $testFile = $uploadDir . '/test_write.txt';
    if (file_put_contents($testFile, 'test') !== false) {
        echo "<p style='color: green;'>✓ Can write files to gallery directory</p>";
        unlink($testFile); // Clean up test file
    } else {
        echo "<p style='color: red;'>✗ Cannot write files to gallery directory</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Gallery directory does not exist</p>";
}

echo "<h3>Directory Information:</h3>";
echo "<p>Current user: " . get_current_user() . "</p>";
echo "<p>PHP user: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'Unknown') . "</p>";
echo "<p>Directory owner: " . (function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($uploadDir))['name'] : 'Unknown') . "</p>";

echo "<h3>Manual Fix Commands:</h3>";
echo "<p>If the above didn't work, run these commands in your terminal:</p>";
echo "<pre>";
echo "chmod -R 0777 " . dirname($uploadDir) . "\n";
echo "chown -R www-data:www-data " . dirname($uploadDir) . "\n";
echo "</pre>";

echo "<p><a href='gallery.php'>← Back to Gallery</a></p>";
?>
