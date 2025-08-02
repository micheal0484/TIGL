<?php
require_once './config.php';
require_once './SessionManager.php';

header('Content-Type: application/json');

$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if ($session) {
    echo json_encode([
        'success' => true,
        'session_token' => isset($_COOKIE['session_token']) ? $_COOKIE['session_token'] : 'not set',
        'user_id' => $session['user_id'],
        'username' => $session['username'],
        'isAdmin' => $session['isAdmin']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'session_token' => isset($_COOKIE['session_token']) ? $_COOKIE['session_token'] : 'not set',
        'user_id' => 'not logged in',
        'username' => 'not logged in',
        'isAdmin' => 'not logged in'
    ]);
}

$conn->close();
?>