<?php
require_once './config.php';
require_once './SessionManager.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

// Check if user is admin
$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if (!$session || !$session['isAdmin']) {
    echo json_encode(['success' => false, 'error' => 'Access denied. Admin privileges required.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$roundId = isset($input['roundId']) ? intval($input['roundId']) : 0;

if ($roundId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid round ID']);
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    // First delete hole scores (due to foreign key constraint)
    $stmt = $conn->prepare("DELETE FROM hole_scores WHERE round_id = ?");
    $stmt->bind_param("i", $roundId);
    $stmt->execute();
    $stmt->close();
    
    // Then delete the round
    $stmt = $conn->prepare("DELETE FROM rounds WHERE id = ?");
    $stmt->bind_param("i", $roundId);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $conn->commit();
        echo json_encode(['success' => true]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Round not found']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>