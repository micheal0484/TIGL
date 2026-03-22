<?php
require_once './config.php';
require_once './SessionManager.php';

header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if (!$session) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

$userId = $session['user_id'];
$roundId = isset($_GET['roundId']) ? intval($_GET['roundId']) : 0;

if ($roundId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid round ID']);
    exit();
}

try {
    // Verify the round belongs to this user
    $stmt = $conn->prepare("
        SELECT r.id, r.course_id, c.name as course_name, c.holes, r.handicap_used, r.is_completed
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
    
    $round = $result->fetch_assoc();
    $stmt->close();
    
    // Get hole details with current scores
    $stmt = $conn->prepare("
        SELECT h.hole_number, h.par, h.yardage, h.mens_handicap, h.womens_handicap,
               COALESCE(hs.score, h.par) as current_score,
               COALESCE(hs.penalties, 0) as penalties,
               COALESCE(hs.ob_strokes, 0) as ob_strokes,
               COALESCE(hs.points, 0) as points
        FROM holes h
        LEFT JOIN hole_scores hs ON h.course_id = ? AND h.hole_number = hs.hole_number AND hs.round_id = ?
        WHERE h.course_id = ?
        ORDER BY h.hole_number
    ");
    $stmt->bind_param("iii", $round['course_id'], $roundId, $round['course_id']);
    $stmt->execute();
    $holesResult = $stmt->get_result();
    
    $holes = [];
    while ($hole = $holesResult->fetch_assoc()) {
        $holes[] = [
            'hole_number' => intval($hole['hole_number']),
            'par' => intval($hole['par']),
            'yardage' => $hole['yardage'] ? intval($hole['yardage']) : null,
            'mens_handicap' => $hole['mens_handicap'] ? intval($hole['mens_handicap']) : null,
            'womens_handicap' => $hole['womens_handicap'] ? intval($hole['womens_handicap']) : null,
            'current_score' => intval($hole['current_score']),
            'penalties' => intval($hole['penalties']),
            'ob_strokes' => intval($hole['ob_strokes']),
            'points' => floatval($hole['points'])
        ];
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'round' => [
            'id' => $roundId,
            'course_id' => intval($round['course_id']),
            'course_name' => $round['course_name'],
            'total_holes' => intval($round['holes']),
            'handicap_used' => floatval($round['handicap_used']),
            'is_completed' => (bool)$round['is_completed']
        ],
        'holes' => $holes
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    error_log("geteditableholes.php error: " . $e->getMessage());
}

$conn->close();
?>