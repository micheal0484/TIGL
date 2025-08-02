<?php
require_once './config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

try {
    // Get all complete courses (courses that have all their holes defined)
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.holes, COUNT(h.id) as holes_added 
        FROM courses c 
        LEFT JOIN holes h ON c.id = h.course_id 
        GROUP BY c.id, c.name, c.holes 
        HAVING COUNT(h.id) = c.holes
        ORDER BY c.name
    ");
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $courses = [];
        
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        echo json_encode($courses);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>