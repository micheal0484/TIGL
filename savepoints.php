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
            1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        ) ON DUPLICATE KEY UPDATE
            points_albatross = VALUES(points_albatross),
            points_eagle = VALUES(points_eagle),
            points_birdie = VALUES(points_birdie),
            points_par = VALUES(points_par),
            points_bogey = VALUES(points_bogey),
            points_double_bogey = VALUES(points_double_bogey),
            points_triple_bogey = VALUES(points_triple_bogey),
            points_worse = VALUES(points_worse),
            points_penalty_stroke = VALUES(points_penalty_stroke),
            points_ob_stroke = VALUES(points_ob_stroke),
            points_match_win = VALUES(points_match_win),
            points_match_loss = VALUES(points_match_loss),
            points_under_par_round = VALUES(points_under_par_round),
            points_even_par_round = VALUES(points_even_par_round),
            updated_at = CURRENT_TIMESTAMP
    ");
    
    $stmt->bind_param("dddddddddddddd",
        $_POST['points_albatross'],
        $_POST['points_eagle'],
        $_POST['points_birdie'],
        $_POST['points_par'],
        $_POST['points_bogey'],
        $_POST['points_double_bogey'],
        $_POST['points_triple_bogey'],
        $_POST['points_worse'],
        $_POST['points_penalty_stroke'],
        $_POST['points_ob_stroke'],
        $_POST['points_match_win'],
        $_POST['points_match_loss'],
        $_POST['points_under_par_round'],
        $_POST['points_even_par_round']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save points configuration']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>