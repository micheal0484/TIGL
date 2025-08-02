<?php
require_once './config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

$courseId = isset($_GET['courseId']) ? intval($_GET['courseId']) : 0;
$holeNumber = isset($_GET['holeNumber']) ? intval($_GET['holeNumber']) : 0;

if ($courseId <= 0 || $holeNumber <= 0) {
    echo json_encode(['error' => 'Invalid course ID or hole number']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT hole_number, yardage, par, mens_handicap, womens_handicap FROM holes WHERE course_id = ? AND hole_number = ?");
    $stmt->bind_param("ii", $courseId, $holeNumber);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $hole = $result->fetch_assoc();
            echo json_encode($hole);
        } else {
            echo json_encode(['error' => 'Hole not found']);
        }
    } else {
        echo json_encode(['error' => 'Query failed: ' . $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>