<?php
require_once './config.php';

echo "Testing database connection...<br>";

try {
    // Test connection
    echo "Connected to database successfully.<br>";
    
    // Check if courses table exists
    $result = $conn->query("SHOW TABLES LIKE 'courses'");
    if ($result->num_rows > 0) {
        echo "Courses table exists.<br>";
    } else {
        echo "Courses table does NOT exist. You need to create it.<br>";
    }
    
    // Check if holes table exists
    $result = $conn->query("SHOW TABLES LIKE 'holes'");
    if ($result->num_rows > 0) {
        echo "Holes table exists.<br>";
    } else {
        echo "Holes table does NOT exist. You need to create it.<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>