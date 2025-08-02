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
    // Verify round belongs to user and get round details
    $stmt = $conn->prepare("
        SELECT r.total_score, r.points, c.par_total 
        FROM rounds r 
        JOIN courses c ON r.course_id = c.id 
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
    
    // Calculate bonus points
    $pointsCalculator = new PointsCalculator($conn);
    $matchPoints = $pointsCalculator->getMatchResultPoints($matchResult === 'won');
    $roundBonusPoints = $pointsCalculator->calculateRoundBonusPoints($roundData['total_score'], $roundData['par_total']);
    
    $totalBonusPoints = $matchPoints + $roundBonusPoints;
    $newTotalPoints = $roundData['points'] + $totalBonusPoints;
    
    // Update round with match result and bonus points
    $stmt = $conn->prepare("
        UPDATE rounds SET 
            match_result = ?, 
            points = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("sdii", $matchResult, $newTotalPoints, $roundId, $userId);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to save match result']);
        exit();
    }
    $stmt->close();
    
    $message = "Round completed! ";
    if ($matchPoints > 0) {
        $message .= "Match win bonus: +" . number_format($matchPoints, 1) . " points. ";
    }
    if ($roundBonusPoints > 0) {
        $message .= "Round bonus: +" . number_format($roundBonusPoints, 1) . " points. ";
    }
    $message .= "Total round points: " . number_format($newTotalPoints, 1);
    
    echo json_encode([
        'success' => true,
        'matchPoints' => $matchPoints,
        'roundBonusPoints' => $roundBonusPoints,
        'totalPoints' => $newTotalPoints,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>