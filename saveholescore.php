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
    
    // Update hole score with points - ensure decimal storage
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
        echo json_encode(['success' => false, 'error' => 'Failed to save hole score: ' . $stmt->error]);
        exit();
    }
    $stmt->close();
    
    // Recalculate total round points (this will now include quota when round is completed)
    $stmt = $conn->prepare("
        SELECT SUM(points) as total_hole_points, 
               SUM(score) as total_score, 
               SUM(penalties) as total_penalties, 
               SUM(ob_strokes) as total_ob,
               COUNT(*) as holes_played
        FROM hole_scores 
        WHERE round_id = ?
    ");
    $stmt->bind_param("i", $roundId);
    $stmt->execute();
    $totals = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Ensure hole points is always a float
    $totalHolePoints = floatval($totals['total_hole_points'] ?? 0);

    // For incomplete rounds, just store the hole points (no quota yet)
    // Quota will be added when the round is completed with match result
    $stmt = $conn->prepare("
        UPDATE rounds SET 
            total_score = ?, 
            total_penalties = ?, 
            total_ob = ?, 
            points = ?
        WHERE id = ? AND user_id = ?
    ");

    $stmt->bind_param("iiidii", 
        $totals['total_score'], 
        $totals['total_penalties'], 
        $totals['total_ob'], 
        $totalHolePoints, // Use the float-converted value
        $roundId, 
        $userId
    );
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'holePoints' => floatval($holePoints), // Ensure this is also a float
        'totalHolePoints' => $totalHolePoints, // Include for debugging
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