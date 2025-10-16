<?php
/**
 * Database Connection Configuration
 * 
 * This file supports both:
 * 1. Environment variables (for Coolify/Docker deployment)
 * 2. Local development (fallback values)
 */

// Get database credentials from environment variables or use defaults
$db_host     = getenv('DB_HOST')     ?: 'localhost';
$db_username = getenv('DB_USERNAME') ?: 'root';
$db_password = getenv('DB_PASSWORD') ?: '';
$db_name     = getenv('DB_DATABASE') ?: 'alumni_db';
$db_port     = getenv('DB_PORT')     ?: 3306;

// Create database connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("Could not connect to MySQL: " . $conn->connect_error);
}

// Set character set to UTF8
$conn->set_charset("utf8mb4");
?>