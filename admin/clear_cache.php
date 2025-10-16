<?php
// Temporary file to clear PHP OpCache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OpCache cleared successfully!<br>";
} else {
    echo "ℹ️ OpCache is not enabled.<br>";
}

// Clear session and redirect
session_start();
session_destroy();
echo "✅ Session cleared!<br>";
echo "<br><a href='login.php'>→ Go to Login Page</a>";
?>

