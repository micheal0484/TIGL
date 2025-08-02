<?php
require_once './config.php';
require_once './SessionManager.php';

header('Content-Type: application/json');

// Check if user is admin
$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if (!$session || !$session['isAdmin']) {
    echo json_encode(['error' => 'Access denied. Admin privileges required.']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM points_config WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $points = $result->fetch_assoc();
        // Remove id and timestamp fields
        unset($points['id'], $points['created_at'], $points['updated_at']);
        echo json_encode($points);
    } else {
        // No configuration exists, return defaults
        echo json_encode([
            'points_albatross' => 3.0,
            'points_eagle' => 2.5,
            'points_birdie' => 1.5,
            'points_par' => 1.0,
            'points_bogey' => 0.5,
            'points_double_bogey' => 0.0,
            'points_triple_bogey' => -1.0,
            'points_worse' => -2.0,
            'points_penalty_stroke' => -0.5,
            'points_ob_stroke' => -2.0,
            'points_match_win' => 2.0,
            'points_match_loss' => 0.0,
            'points_under_par_round' => 3.0,
            'points_even_par_round' => 1.0
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>