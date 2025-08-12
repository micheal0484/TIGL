<?php
require_once './config.php';

header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if (empty($username)) {
    echo json_encode(['success' => false, 'error' => 'Username is required']);
    exit();
}

try {
    // Get player details
    $stmt = $conn->prepare("SELECT username, pin, handicap, isAdmin FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $player = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'username' => $player['username'],
            'pin' => $player['pin'],
            'handicap' => floatval($player['handicap']),
            'isAdmin' => (bool)$player['isAdmin']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Player not found']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    error_log("getplayerdetails.php error: " . $e->getMessage());
}

$conn->close();
?>