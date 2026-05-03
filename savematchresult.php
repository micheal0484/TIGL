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
    // Load round totals and round-specific handicap.
    $stmt = $conn->prepare("
        SELECT r.total_score, r.points, r.course_id, r.handicap_used
        FROM rounds r
        WHERE r.id = ? AND r.user_id = ?
    ");
    
    $stmt->bind_param("ii", $roundId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Round not found or access denied']);
        exit();
    }
    
    $roundData = $result->fetch_assoc();
    $stmt->close();
    
    // Debug logging
    error_log("Round $roundId - Current hole points: " . $roundData['points'] . ", Total score: " . $roundData['total_score'] . ", Match result: " . $matchResult);

    $handicapUsed = floatval($roundData['handicap_used']);

    // Calculate net score
    $netScore = $roundData['total_score'] - $handicapUsed;

    // Calculate final points using net-target round bonus and match result.
    $pointsCalculator = new PointsCalculator($conn);

    // Get current hole points from the round
    $currentHolePoints = floatval($roundData['points']);

    $pointsBreakdown = $pointsCalculator->calculateTotalRoundPoints(
        $currentHolePoints,
        $netScore,
        $matchResult,
        36.0
    );

    $roundBonusPoints = $pointsBreakdown['roundBonusPoints'];
    $matchPoints = $pointsBreakdown['matchPoints'];
    $totalPoints = $pointsBreakdown['totalPoints'];
    
    // Debug logging
    error_log("Points calculation: Hole: $currentHolePoints, Bonus (net36): $roundBonusPoints, Match: $matchPoints, Total: $totalPoints");
    
    // Update round with match result and final net-par points.
    $stmt = $conn->prepare("
        UPDATE rounds SET 
            match_result = ?, 
            points = ?,
            is_completed = 1
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("sdii", $matchResult, $totalPoints, $roundId, $userId);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to save match result: ' . $stmt->error]);
        exit();
    }
    $stmt->close();
    
    // Return detailed breakdown
    echo json_encode([
        'success' => true,
        'holePoints' => $currentHolePoints,
        'matchPoints' => $matchPoints,
        'roundBonusPoints' => $roundBonusPoints,
        'totalPoints' => $totalPoints,
        'grossScore' => $roundData['total_score'],
        'netScore' => $netScore,
        'handicapUsed' => $handicapUsed,
        'targetNetScore' => 36.0,
        'message' => "Round completed! Hole Points: +" . number_format($currentHolePoints, 1) . 
                     ", Match: " . ($matchPoints >= 0 ? "+" : "") . number_format($matchPoints, 1) . 
                     ", Round Bonus (Net < 36): " . ($roundBonusPoints >= 0 ? "+" : "") . number_format($roundBonusPoints, 1) . 
                     " = Total: " . number_format($totalPoints, 1) . " points"
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    error_log("savematchresult.php error: " . $e->getMessage());
}

$conn->close();
?>