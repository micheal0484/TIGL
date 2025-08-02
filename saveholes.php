<?php
require_once './config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['courseId']) || !isset($input['holes'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit();
}

$courseId = intval($input['courseId']);
$holes = $input['holes'];

if (empty($holes)) {
    echo json_encode(['success' => false, 'error' => 'No hole data provided']);
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    // Delete existing holes for this course
    $deleteStmt = $conn->prepare("DELETE FROM holes WHERE course_id = ?");
    $deleteStmt->bind_param("i", $courseId);
    $deleteStmt->execute();
    $deleteStmt->close();

    // Insert holes
    $stmt = $conn->prepare("INSERT INTO holes (course_id, hole_number, yardage, par, mens_handicap, womens_handicap) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($holes as $hole) {
        $stmt->bind_param("iiiiii", 
            $courseId, 
            $hole['holeNumber'], 
            $hole['yardage'], 
            $hole['par'], 
            $hole['mensHandicap'], 
            $hole['womensHandicap']
        );
        $stmt->execute();
    }
    
    $conn->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Error saving holes: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>