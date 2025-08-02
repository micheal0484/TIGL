<?php
session_start();
require_once './config.php';
require_once './SessionManager.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

// Check if user is logged in using SessionManager
$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if (!$session) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

$userId = $session['user_id'];
$roundId = isset($_POST['roundId']) ? intval($_POST['roundId']) : 0;
$matchResult = isset($_POST['matchResult']) ? $_POST['matchResult'] : '';

if ($roundId <= 0 || !in_array($matchResult, ['won', 'lost'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit();
}

try {
    // Verify round belongs to current user and update match result
    $stmt = $conn->prepare("UPDATE rounds SET match_result = ?, is_completed = TRUE WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $matchResult, $roundId, $userId);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Round not found or access denied']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>