<?php
// Script to add image_data column to gallery table
include 'db_connect.php';

echo "<h2>Adding image_data column to gallery table</h2>";

// Check if column already exists
$result = $conn->query("SHOW COLUMNS FROM gallery LIKE 'image_data'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ image_data column already exists</p>";
} else {
    // Add the column
    $sql = "ALTER TABLE gallery ADD COLUMN image_data LONGTEXT NULL AFTER about";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ image_data column added successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to add image_data column: " . $conn->error . "</p>";
    }
}

// Show current table structure
echo "<h3>Current gallery table structure:</h3>";
$result = $conn->query("DESCRIBE gallery");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<p><a href='gallery_working.php'>← Go to Working Gallery</a></p>";
?>
