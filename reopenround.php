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
$roundId = isset($_POST['roundId']) ? intval($_POST['roundId']) : 0;

if ($roundId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid round ID']);
    exit();
}

try {
    // Verify the round belongs to this user and is completed
    $stmt = $conn->prepare("
        SELECT id, is_completed, match_result, total_score, points 
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
    
    if (!$round['is_completed']) {
        echo json_encode(['success' => false, 'error' => 'Round is not completed, cannot reopen for editing']);
        exit();
    }
    
    // Store original values for potential restoration
    $originalData = [
        'match_result' => $round['match_result'],
        'total_score' => $round['total_score'],
        'points' => $round['points']
    ];
    
    // Reopen the round by setting is_completed = 0 and clearing match result
    $stmt = $conn->prepare("
        UPDATE rounds SET 
            is_completed = 0,
            match_result = NULL,
            total_score = NULL,
            points = NULL
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $roundId, $userId);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to reopen round: ' . $stmt->error]);
        exit();
    }
    $stmt->close();
    
    // Log the action for potential audit trail
    error_log("Round $roundId reopened for editing by user $userId. Original: " . json_encode($originalData));
    
    echo json_encode([
        'success' => true,
        'message' => 'Round reopened for editing. You can now modify your hole scores.',
        'originalData' => $originalData
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    error_log("reopenround.php error: " . $e->getMessage());
}

$conn->close();
?>