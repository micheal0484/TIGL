<?php
require_once './config.php';
require_once './SessionManager.php';
require_once './PointsCalculator.php';

header('Content-Type: application/json');

// Check if user is logged in
$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if (!$session) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

$userId = $session['user_id'];
$roundId = isset($_POST['roundId']) ? intval($_POST['roundId']) : 0;
$matchResult = isset($_POST['matchResult']) ? trim($_POST['matchResult']) : '';

if ($roundId <= 0 || empty($matchResult)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit();
}

if (!in_array($matchResult, ['won', 'lost'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid match result']);
    exit();
}

try {
    // First check if par_total column exists in courses table
    $stmt = $conn->prepare("SHOW COLUMNS FROM courses LIKE 'par_total'");
    $stmt->execute();
    $result = $stmt->get_result();
    $parTotalExists = $result->num_rows > 0;
    $stmt->close();
    
    // Get round details with calculated par total
    if ($parTotalExists) {
        $stmt = $conn->prepare("
            SELECT r.total_score, r.points, r.course_id, c.par_total, 
                   (SELECT SUM(h.par) FROM holes h WHERE h.course_id = r.course_id) as calculated_par
            FROM rounds r 
            JOIN courses c ON r.course_id = c.id 
            WHERE r.id = ? AND r.user_id = ?
        ");
    } else {
        $stmt = $conn->prepare("
            SELECT r.total_score, r.points, r.course_id,
                   (SELECT SUM(h.par) FROM holes h WHERE h.course_id = r.course_id) as calculated_par
            FROM rounds r 
            WHERE r.id = ? AND r.user_id = ?
        ");
    }
    
    $stmt->bind_param("ii", $roundId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Round not found or access denied']);
        exit();
    }
    
    $roundData = $result->fetch_assoc();
    $stmt->close();
    
    // Use calculated par or par_total
    $coursePar = $parTotalExists ? $roundData['par_total'] : $roundData['calculated_par'];
    
    // Debug logging
    error_log("Round $roundId - Current points: " . $roundData['points'] . ", Total score: " . $roundData['total_score'] . ", Course par: " . $coursePar . ", Match result: " . $matchResult);
    
    // Calculate total points including quota for this specific round
    $pointsCalculator = new PointsCalculator($conn);
    
    // Get current hole points from the round
    $currentHolePoints = floatval($roundData['points']);
    
    // Calculate total round points with round-specific quota
    $pointsBreakdown = $pointsCalculator->calculateTotalRoundPointsForRound(
        $roundId, 
        $currentHolePoints, 
        $roundData['total_score'], 
        $coursePar, 
        $matchResult
    );
    
    // Debug logging
    error_log("Points breakdown: " . json_encode($pointsBreakdown));
    
    // Update round with match result and total points (including quota)
    $stmt = $conn->prepare("
        UPDATE rounds SET 
            match_result = ?, 
            points = ?,
            is_completed = 1
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("sdii", $matchResult, $pointsBreakdown['totalPoints'], $roundId, $userId);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to save match result: ' . $stmt->error]);
        exit();
    }
    $stmt->close();
    
    // Return detailed breakdown
    echo json_encode([
        'success' => true,
        'quota' => $pointsBreakdown['quota'],
        'holePoints' => $pointsBreakdown['holePoints'],
        'matchPoints' => $pointsBreakdown['matchPoints'],
        'roundBonusPoints' => $pointsBreakdown['roundBonusPoints'],
        'totalPoints' => $pointsBreakdown['totalPoints'],
        'message' => "Round completed! Quota: " . number_format($pointsBreakdown['quota'], 1) . 
                     ", Hole Points: +" . number_format($pointsBreakdown['holePoints'], 1) . 
                     ", Match: " . ($pointsBreakdown['matchPoints'] >= 0 ? "+" : "") . number_format($pointsBreakdown['matchPoints'], 1) . 
                     ", Round Bonus: " . ($pointsBreakdown['roundBonusPoints'] >= 0 ? "+" : "") . number_format($pointsBreakdown['roundBonusPoints'], 1) . 
                     " = Total: " . number_format($pointsBreakdown['totalPoints'], 1) . " points"
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    error_log("savematchresult.php error: " . $e->getMessage());
}

$conn->close();
?>