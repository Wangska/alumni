<?php
session_start();
echo "<h2>Session Debug Information</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n\n";
echo "All Session Variables:\n";
print_r($_SESSION);
echo "</pre>";

echo "<hr>";
echo "<h3>Login Variables Check:</h3>";
echo "<ul>";
echo "<li>login_id: " . (isset($_SESSION['login_id']) ? $_SESSION['login_id'] : 'NOT SET') . "</li>";
echo "<li>login_username: " . (isset($_SESSION['login_username']) ? $_SESSION['login_username'] : 'NOT SET') . "</li>";
echo "<li>login_name: " . (isset($_SESSION['login_name']) ? $_SESSION['login_name'] : 'NOT SET') . "</li>";
echo "<li>login_type: " . (isset($_SESSION['login_type']) ? $_SESSION['login_type'] : 'NOT SET') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<a href='dashboard.php'>Go to Dashboard</a> | <a href='login.php'>Logout & Login Again</a>";
?>

