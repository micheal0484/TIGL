<?php
require_once './config.php';
require_once './SessionManager.php';

header('Content-Type: application/json');

// Check if user is admin
$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if (!$session || !$session['isAdmin']) {
    echo json_encode(['success' => false, 'error' => 'Access denied. Admin privileges required.']);
    exit();
}

try {
    $stmt = $conn->prepare("
        INSERT INTO points_config (
            id, points_albatross, points_eagle, points_birdie, points_par, points_bogey,
            points_double_bogey, points_triple_bogey, points_worse, points_penalty_stroke,
            points_ob_stroke, points_match_win, points_match_loss, points_under_par_round,
            points_even_par_round
        ) VALUES (
            1, 3.0, 2.5, 1.5, 1.0, 0.5, 0.0, -1.0, -2.0, -0.5, -2.0, 2.0, 0.0, 3.0, 1.0
        ) ON DUPLICATE KEY UPDATE
            points_albatross = 3.0,
            points_eagle = 2.5,
            points_birdie = 1.5,
            points_par = 1.0,
            points_bogey = 0.5,
            points_double_bogey = 0.0,
            points_triple_bogey = -1.0,
            points_worse = -2.0,
            points_penalty_stroke = -0.5,
            points_ob_stroke = -2.0,
            points_match_win = 2.0,
            points_match_loss = 0.0,
            points_under_par_round = 3.0,
            points_even_par_round = 1.0,
            updated_at = CURRENT_TIMESTAMP
    ");
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to reset points to defaults']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>