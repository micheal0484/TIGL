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
    // Get round status
    $stmt = $conn->prepare("
        SELECT is_completed, match_result
        FROM rounds 
        WHERE id = ? AND user_id = ?
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
    
    echo json_encode([
        'success' => true,
        'isCompleted' => (bool)$round['is_completed'],
        'hasMatchResult' => !empty($round['match_result']),
        'matchResult' => $round['match_result']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>