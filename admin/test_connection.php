<?php
/**
 * Database Connection Test
 * Use this to verify your database connection works
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
            <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Database Connection Test
        </h1>

        <?php
        // Display environment variables (masked)
        echo '<div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">';
        echo '<h2 class="font-bold text-blue-800 mb-3">Environment Variables:</h2>';
        echo '<div class="space-y-2 font-mono text-sm">';
        
        $db_host = getenv('DB_HOST') ?: 'localhost';
        $db_username = getenv('DB_USERNAME') ?: 'root';
        $db_password = getenv('DB_PASSWORD') ?: '';
        $db_name = getenv('DB_DATABASE') ?: 'alumni_db';
        $db_port = getenv('DB_PORT') ?: 3306;
        
        echo '<p><strong>DB_HOST:</strong> ' . htmlspecialchars($db_host) . '</p>';
        echo '<p><strong>DB_USERNAME:</strong> ' . htmlspecialchars($db_username) . '</p>';
        echo '<p><strong>DB_PASSWORD:</strong> ' . (empty($db_password) ? '<span class="text-yellow-600">Empty (local dev)</span>' : '<span class="text-green-600">Set (' . str_repeat('*', min(20, strlen($db_password))) . ')</span>') . '</p>';
        echo '<p><strong>DB_DATABASE:</strong> ' . htmlspecialchars($db_name) . '</p>';
        echo '<p><strong>DB_PORT:</strong> ' . htmlspecialchars($db_port) . '</p>';
        echo '</div>';
        echo '</div>';

        // Test connection
        echo '<div class="space-y-4">';
        
        try {
            include('db_connect.php');
            
            if ($conn) {
                echo '<div class="p-4 bg-green-50 border border-green-300 rounded-lg">';
                echo '<div class="flex items-start">';
                echo '<svg class="w-6 h-6 text-green-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                echo '</svg>';
                echo '<div>';
                echo '<h3 class="font-bold text-green-800 text-lg">✅ Connection Successful!</h3>';
                echo '<p class="text-green-700 mt-1">Successfully connected to the database.</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                
                // Test if we can query the database
                $result = $conn->query("SELECT COUNT(*) as count FROM users");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo '<div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">';
                    echo '<h3 class="font-bold text-blue-800">Database Query Test:</h3>';
                    echo '<p class="text-blue-700 mt-1">Found <strong>' . $row['count'] . '</strong> users in the database.</p>';
                    echo '</div>';
                } else {
                    echo '<div class="p-4 bg-yellow-50 border border-yellow-300 rounded-lg">';
                    echo '<h3 class="font-bold text-yellow-800">⚠️ Warning:</h3>';
                    echo '<p class="text-yellow-700 mt-1">Connected but cannot query tables. Database might not be imported yet.</p>';
                    echo '</div>';
                }
                
                // Server info
                echo '<div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">';
                echo '<h3 class="font-bold text-gray-800 mb-2">Server Information:</h3>';
                echo '<div class="text-sm font-mono space-y-1 text-gray-700">';
                echo '<p><strong>MySQL Version:</strong> ' . $conn->server_info . '</p>';
                echo '<p><strong>Host Info:</strong> ' . $conn->host_info . '</p>';
                echo '<p><strong>Character Set:</strong> ' . $conn->character_set_name() . '</p>';
                echo '</div>';
                echo '</div>';
                
                $conn->close();
            }
        } catch (Exception $e) {
            echo '<div class="p-4 bg-red-50 border border-red-300 rounded-lg">';
            echo '<div class="flex items-start">';
            echo '<svg class="w-6 h-6 text-red-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
            echo '</svg>';
            echo '<div>';
            echo '<h3 class="font-bold text-red-800 text-lg">❌ Connection Failed!</h3>';
            echo '<p class="text-red-700 mt-1">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="p-4 bg-yellow-50 border border-yellow-300 rounded-lg mt-4">';
            echo '<h3 class="font-bold text-yellow-800">Troubleshooting Tips:</h3>';
            echo '<ul class="list-disc list-inside text-yellow-700 mt-2 space-y-1">';
            echo '<li>Check if MySQL service is running</li>';
            echo '<li>Verify environment variables are set correctly</li>';
            echo '<li>Ensure database exists and is accessible</li>';
            echo '<li>Check network connectivity to database host</li>';
            echo '</ul>';
            echo '</div>';
        }
        
        echo '</div>';
        ?>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <a href="login.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                ← Back to Login
            </a>
        </div>
    </div>
</body>
</html>

