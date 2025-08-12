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

try {
    // Get user's current handicap
    $stmt = $conn->prepare("SELECT handicap FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'handicap' => floatval($user['handicap']),
            'username' => $session['username']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'User not found']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    error_log("getuserhandicap.php error: " . $e->getMessage());
}

$conn->close();
?>