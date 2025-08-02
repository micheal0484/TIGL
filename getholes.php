<?php
require_once './config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

$courseId = isset($_GET['courseId']) ? intval($_GET['courseId']) : 0;

if ($courseId <= 0) {
    echo json_encode(['error' => 'Invalid course ID']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT hole_number, yardage, par, mens_handicap, womens_handicap FROM holes WHERE course_id = ? ORDER BY hole_number");
    $stmt->bind_param("i", $courseId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $holes = [];
        
        while ($row = $result->fetch_assoc()) {
            $holes[] = $row;
        }
        
        echo json_encode($holes);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>