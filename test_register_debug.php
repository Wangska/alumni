<?php
// Debug registration issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'admin/db_connect.php';

echo "<h1>Registration Debug Test</h1>";
echo "<pre>";

// Test database connection
if ($conn) {
    echo "✅ Database connected successfully!\n";
    echo "Database: " . $conn->host_info . "\n\n";
} else {
    echo "❌ Database connection failed!\n";
    exit;
}

// Test if tables exist
$tables = ['alumnus_bio', 'users', 'courses'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ Table '$table' exists\n";
    } else {
        echo "❌ Table '$table' NOT found!\n";
    }
}

echo "\n--- Check alumnus_bio columns ---\n";
$result = $conn->query("SHOW COLUMNS FROM alumnus_bio");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Column: " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

echo "\n--- Check courses ---\n";
$courses = $conn->query("SELECT id, course FROM courses LIMIT 5");
if ($courses && $courses->num_rows > 0) {
    echo "Available courses:\n";
    while ($course = $courses->fetch_assoc()) {
        echo "  " . $course['id'] . ": " . $course['course'] . "\n";
    }
} else {
    echo "❌ No courses found in database!\n";
}

echo "\n--- Test Sample Data ---\n";
$test_data = [
    'firstname' => 'Test',
    'lastname' => 'User',
    'email' => 'test' . time() . '@example.com',
    'batch' => '2020',
    'course_id' => 1,
    'gender' => 'Male'
];

echo "Test data:\n";
print_r($test_data);

// Try to check if email exists (this is the validation from register_save.php)
$check_email = $conn->query("SELECT id FROM alumnus_bio WHERE email='{$test_data['email']}' LIMIT 1");
if ($check_email === false) {
    echo "\n❌ Error checking email: " . $conn->error . "\n";
} else {
    echo "\n✅ Email check query works\n";
}

echo "</pre>";

echo "<hr>";
echo "<h2>Now try to register with the form:</h2>";
echo "<p><a href='register.php'>Go to Registration Page</a></p>";
?>

