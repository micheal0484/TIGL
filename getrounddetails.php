<?php
require_once './config.php';
require_once './SessionManager.php';

header('Content-Type: application/json');

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
    // Get round details
    $stmt = $conn->prepare("
        SELECT r.course_id, c.name as course_name, c.holes,
               (SELECT COUNT(*) FROM hole_scores WHERE round_id = r.id) as holes_played
        FROM rounds r
        JOIN courses c ON r.course_id = c.id
        WHERE r.id = ? AND r.user_id = ? AND r.is_completed = 0
    ");
    $stmt->bind_param("ii", $roundId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Round not found or already completed']);
        exit();
    }
    
    $round = $result->fetch_assoc();
    $nextHole = $round['holes_played'] + 1;
    
    if ($nextHole > $round['holes']) {
        echo json_encode(['success' => false, 'error' => 'All holes completed, round ready for finalization']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'courseId' => $round['course_id'],
        'courseName' => $round['course_name'],
        'holes' => intval($round['holes']),
        'nextHole' => $nextHole,
        'holesPlayed' => intval($round['holes_played'])
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>