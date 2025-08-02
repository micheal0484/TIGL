<?php
require_once './config.php';
require_once './SessionManager.php';

header('Content-Type: application/json');

$sessionManager = new SessionManager($conn);
$sessionManager->destroySession();

echo json_encode(['success' => true]);

$conn->close();
?>