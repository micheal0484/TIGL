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
    
    // Get the handicap used for this round
    $stmt = $conn->prepare("SELECT handicap_used FROM rounds WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $roundId, $userId);
    $stmt->execute();
    $handicapResult = $stmt->get_result();

    if ($handicapResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Round not found or handicap not set']);
        exit();
    }

    $handicapUsed = $handicapResult->fetch_assoc()['handicap_used'];
    $stmt->close();

    // Calculate net score
    $netScore = $roundData['total_score'] - $handicapUsed;

    // Calculate total points including quota
    $pointsCalculator = new PointsCalculator($conn);
    
    // Get current hole points from the round
    $currentHolePoints = floatval($roundData['points']);
    
    // Calculate quota based on handicap used for this round
    $quota = $handicapUsed - 18.0;
    
    // Calculate round bonus using NET score, not gross score
    $roundBonusPoints = $pointsCalculator->calculateRoundBonusPoints($netScore, $coursePar);
    $matchPoints = $pointsCalculator->getMatchResultPoints($matchResult === 'won');
    
    // Calculate total points
    $totalPoints = $quota + $currentHolePoints + $roundBonusPoints + $matchPoints;
    
    // Debug logging
    error_log("Points calculation: Quota: $quota, Hole: $currentHolePoints, Bonus (net): $roundBonusPoints, Match: $matchPoints, Total: $totalPoints");
    
    // Update round with match result and total points (including quota)
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
        'quota' => $quota,
        'holePoints' => $currentHolePoints,
        'matchPoints' => $matchPoints,
        'roundBonusPoints' => $roundBonusPoints,
        'totalPoints' => $totalPoints,
        'grossScore' => $roundData['total_score'],
        'netScore' => $netScore,
        'handicapUsed' => $handicapUsed,
        'message' => "Round completed! Quota: " . number_format($quota, 1) . 
                     ", Hole Points: +" . number_format($currentHolePoints, 1) . 
                     ", Match: " . ($matchPoints >= 0 ? "+" : "") . number_format($matchPoints, 1) . 
                     ", Round Bonus (Net): " . ($roundBonusPoints >= 0 ? "+" : "") . number_format($roundBonusPoints, 1) . 
                     " = Total: " . number_format($totalPoints, 1) . " points"
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    error_log("savematchresult.php error: " . $e->getMessage());
}

$conn->close();
?>