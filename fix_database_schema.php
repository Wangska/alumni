<?php
// Fix database schema - add missing columns
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'admin/db_connect.php';

echo "<h1>Database Schema Fix</h1>";
echo "<pre>";

if (!$conn) {
    die("❌ Database connection failed!");
}

echo "✅ Connected to database\n\n";

// Check and add contact column
echo "Checking 'contact' column...\n";
$result = $conn->query("SHOW COLUMNS FROM `alumnus_bio` LIKE 'contact'");
if ($result && $result->num_rows == 0) {
    echo "❌ 'contact' column missing. Adding it...\n";
    $sql = "ALTER TABLE `alumnus_bio` ADD COLUMN `contact` VARCHAR(20) NOT NULL DEFAULT '' AFTER `email`";
    if ($conn->query($sql)) {
        echo "✅ 'contact' column added successfully!\n";
    } else {
        echo "❌ Error adding 'contact' column: " . $conn->error . "\n";
    }
} else {
    echo "✅ 'contact' column already exists\n";
}

// Check and add address column
echo "\nChecking 'address' column...\n";
$result = $conn->query("SHOW COLUMNS FROM `alumnus_bio` LIKE 'address'");
if ($result && $result->num_rows == 0) {
    echo "❌ 'address' column missing. Adding it...\n";
    $sql = "ALTER TABLE `alumnus_bio` ADD COLUMN `address` TEXT NOT NULL DEFAULT '' AFTER `contact`";
    if ($conn->query($sql)) {
        echo "✅ 'address' column added successfully!\n";
    } else {
        echo "❌ Error adding 'address' column: " . $conn->error . "\n";
    }
} else {
    echo "✅ 'address' column already exists\n";
}

// Show current structure
echo "\n--- Current alumnus_bio structure ---\n";
$result = $conn->query("SHOW COLUMNS FROM `alumnus_bio`");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo sprintf("%-20s %-20s %s\n", $row['Field'], $row['Type'], $row['Null'] == 'NO' ? 'NOT NULL' : 'NULL');
    }
}

echo "\n✅ Database schema fix completed!\n";
echo "</pre>";

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li><a href='test_register_debug.php'>Test Registration System</a></li>";
echo "<li><a href='register.php'>Go to Registration Page</a></li>";
echo "<li><a href='index.php'>Go to Homepage</a></li>";
echo "</ul>";
?>

