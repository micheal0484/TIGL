<?php
session_start();
require_once './config.php';
require_once './SessionManager.php';

header('Content-Type: application/json');

$pin = isset($_POST['pin']) ? trim($_POST['pin']) : '';

if (empty($pin)) {
    echo json_encode(['success' => false, 'error' => 'PIN is required']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, username, pin, handicap, isAdmin FROM users WHERE pin = ?");
    $stmt->bind_param("s", $pin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();            
            // Create session using SessionManager
            $sessionManager = new SessionManager($conn);
            $sessionToken = $sessionManager->createSession($user['id'], $user['username'], $user['isAdmin']);
            
            if ($sessionToken) {
                echo json_encode([
                    'success' => true,
                    'username' => $user['username'],
                    'handicap' => $user['handicap'],
                    'isAdmin' => (bool)$user['isAdmin'],
                    'session_token' => $sessionToken
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to create session']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid PIN']);
        }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>