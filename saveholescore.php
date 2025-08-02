<?php
// Add this at the very beginning to catch any early errors
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once './config.php';
require_once './SessionManager.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

// Immediate error check
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

try {
    // Check if PointsCalculator exists
    if (!file_exists('./PointsCalculator.php')) {
        echo json_encode(['success' => false, 'error' => 'PointsCalculator.php file not found']);
        exit();
    }
    
    require_once './PointsCalculator.php';

    // Test if we can create the class
    try {
        $testCalculator = new PointsCalculator($conn);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'PointsCalculator error: ' . $e->getMessage()]);
        exit();
    }

    // Check if user is logged in using SessionManager
    $sessionManager = new SessionManager($conn);
    $session = $sessionManager->getSession();

    if (!$session) {
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit();
    }

    $userId = $session['user_id'];
    $roundId = isset($_POST['roundId']) ? intval($_POST['roundId']) : 0;
    $holeNumber = isset($_POST['holeNumber']) ? intval($_POST['holeNumber']) : 0;
    $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
    $penalties = isset($_POST['penalties']) ? intval($_POST['penalties']) : 0;
    $obStrokes = isset($_POST['obStrokes']) ? intval($_POST['obStrokes']) : 0;

    // Debug the received data
    error_log("Received data: roundId=$roundId, holeNumber=$holeNumber, score=$score, penalties=$penalties, obStrokes=$obStrokes");

    if ($roundId <= 0 || $holeNumber <= 0 || $score <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid input data']);
        exit();
    }

    // Verify round belongs to current user
    $stmt = $conn->prepare("SELECT course_id FROM rounds WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $roundId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Round not found or access denied']);
        exit();
    }
    
    $courseId = $result->fetch_assoc()['course_id'];
    $stmt->close();
    
    // Get hole par for points calculation
    $stmt = $conn->prepare("SELECT par FROM holes WHERE course_id = ? AND hole_number = ?");
    $stmt->bind_param("ii", $courseId, $holeNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Hole not found']);
        exit();
    }
    
    $par = $result->fetch_assoc()['par'];
    $stmt->close();
    
    // Calculate points for this hole
    $pointsCalculator = new PointsCalculator($conn);
    $holePoints = $pointsCalculator->calculateHolePoints($score, $par, $penalties, $obStrokes);
    
    // Check if hole_scores table has points column, if not add it
    $stmt = $conn->prepare("SHOW COLUMNS FROM hole_scores LIKE 'points'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Add points column if it doesn't exist
        $conn->query("ALTER TABLE hole_scores ADD COLUMN points DECIMAL(4,1) DEFAULT 0.0 AFTER ob_strokes");
    }
    $stmt->close();
    
    // Check if rounds table has points column, if not add it
    $stmt = $conn->prepare("SHOW COLUMNS FROM rounds LIKE 'points'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Add points column if it doesn't exist
        $conn->query("ALTER TABLE rounds ADD COLUMN points DECIMAL(6,1) DEFAULT 0.0 AFTER total_ob");
    }
    $stmt->close();
    
    // Save hole score with points
    $stmt = $conn->prepare("
        INSERT INTO hole_scores (round_id, hole_number, score, penalties, ob_strokes, points) 
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            score = VALUES(score), 
            penalties = VALUES(penalties), 
            ob_strokes = VALUES(ob_strokes),
            points = VALUES(points)
    ");
    $stmt->bind_param("iiiiid", $roundId, $holeNumber, $score, $penalties, $obStrokes, $holePoints);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save hole score: ' . $stmt->error);
    }
    $stmt->close();
    
    // Update round totals including points
    $stmt = $conn->prepare("
        UPDATE rounds r SET 
            total_score = (SELECT IFNULL(SUM(score), 0) FROM hole_scores WHERE round_id = r.id),
            total_penalties = (SELECT IFNULL(SUM(penalties), 0) FROM hole_scores WHERE round_id = r.id),
            total_ob = (SELECT IFNULL(SUM(ob_strokes), 0) FROM hole_scores WHERE round_id = r.id),
            points = (SELECT IFNULL(SUM(points), 0) FROM hole_scores WHERE round_id = r.id)
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $roundId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update round totals: ' . $stmt->error);
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'holePoints' => $holePoints,
        'message' => "Hole $holeNumber saved successfully. Points earned: " . number_format($holePoints, 1)
    ]);
    
} catch (Exception $e) {
    $errorMsg = 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
    echo json_encode(['success' => false, 'error' => $errorMsg]);
    error_log("saveholescore.php error: " . $errorMsg);
} catch (Error $e) {
    $errorMsg = 'Fatal Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
    echo json_encode(['success' => false, 'error' => $errorMsg]);
    error_log("saveholescore.php fatal error: " . $errorMsg);
}

$conn->close();
?>