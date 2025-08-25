<?php
session_start();
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
$courseId = isset($_POST['courseId']) ? intval($_POST['courseId']) : 0;
$seasonId = isset($_POST['seasonId']) ? intval($_POST['seasonId']) : 0;
$handicap = isset($_POST['handicap']) ? floatval($_POST['handicap']) : null;

if ($courseId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid course ID']);
    exit();
}

if ($seasonId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid season ID']);
    exit();
}

if ($handicap === null || $handicap < 0 || $handicap > 36) {
    echo json_encode(['success' => false, 'error' => 'Invalid handicap value. Must be between 0 and 36.']);
    exit();
}

try {
    // Verify the course exists and is complete
    $stmt = $conn->prepare("
        SELECT c.name, c.holes, 
               (SELECT COUNT(*) FROM holes h WHERE h.course_id = c.id) as hole_count
        FROM courses c 
        WHERE c.id = ?
    ");
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $courseResult = $stmt->get_result();
    
    if ($courseResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Course not found']);
        exit();
    }
    
    $course = $courseResult->fetch_assoc();
    $stmt->close();
    
    if ($course['hole_count'] != $course['holes']) {
        echo json_encode(['success' => false, 'error' => 'Course is not complete. Missing hole details.']);
        exit();
    }
    
    // Verify the season exists
    $stmt = $conn->prepare("SELECT year FROM seasons WHERE id = ?");
    $stmt->bind_param("i", $seasonId);
    $stmt->execute();
    $seasonResult = $stmt->get_result();
    
    if ($seasonResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Season not found']);
        exit();
    }
    
    $season = $seasonResult->fetch_assoc();
    $stmt->close();
    
    // Check if user already has an incomplete round for this course/season
    $stmt = $conn->prepare("
        SELECT id FROM rounds 
        WHERE user_id = ? AND course_id = ? AND season_id = ? AND is_completed = 0
    ");
    $stmt->bind_param("iii", $userId, $courseId, $seasonId);
    $stmt->execute();
    $existingRound = $stmt->get_result();
    
    if ($existingRound->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'You already have an incomplete round for this course and season. Please complete or cancel it first.']);
        exit();
    }
    $stmt->close();
    
    // Create new round with the selected handicap
    $stmt = $conn->prepare("
        INSERT INTO rounds (user_id, course_id, season_id, round_date, handicap_used, is_completed) 
        VALUES (?, ?, ?, NOW(), ?, 0)
    ");
    $stmt->bind_param("iiid", $userId, $courseId, $seasonId, $handicap);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to create round: ' . $stmt->error]);
        exit();
    }
    
    $roundId = $conn->insert_id;
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'roundId' => $roundId,
        'courseName' => $course['name'],
        'courseHoles' => $course['holes'],
        'seasonYear' => $season['year'],
        'handicapUsed' => $handicap,
        'message' => "Round started successfully for {$course['name']} ({$season['year']} season) with handicap {$handicap}"
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    error_log("startround.php error: " . $e->getMessage());
}

$conn->close();
?>