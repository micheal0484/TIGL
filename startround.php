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
    echo json_encode([
        'success' => false, 
        'error' => 'User not logged in',
        'debug' => [
            'cookie_token' => isset($_COOKIE['session_token']) ? 'present' : 'missing',
            'post_data' => $_POST
        ]
    ]);
    exit();
}

$userId = $session['user_id'];
$courseId = isset($_POST['courseId']) ? intval($_POST['courseId']) : 0;
$seasonId = isset($_POST['seasonId']) ? intval($_POST['seasonId']) : 0;

if ($courseId <= 0 || $seasonId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid course or season selection']);
    exit();
}

try {
    // Verify course exists and is complete
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.holes, COUNT(h.id) as holes_added 
        FROM courses c 
        LEFT JOIN holes h ON c.id = h.course_id 
        WHERE c.id = ?
        GROUP BY c.id, c.name, c.holes
    ");
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $courseResult = $stmt->get_result();
    
    if ($courseResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Course not found']);
        exit();
    }
    
    $course = $courseResult->fetch_assoc();
    if ($course['holes_added'] != $course['holes']) {
        echo json_encode(['success' => false, 'error' => 'Course is not complete']);
        exit();
    }
    $stmt->close();
    
    // Verify season exists
    $stmt = $conn->prepare("SELECT id FROM seasons WHERE id = ?");
    $stmt->bind_param("i", $seasonId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Season not found']);
        exit();
    }
    $stmt->close();
    
    // Create new round
    $stmt = $conn->prepare("INSERT INTO rounds (user_id, course_id, season_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $userId, $courseId, $seasonId);
    
    if ($stmt->execute()) {
        $roundId = $conn->insert_id;
        echo json_encode(['success' => true, 'roundId' => $roundId]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error creating round: ' . $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>