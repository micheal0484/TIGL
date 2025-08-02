<?php
require_once './config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

try {
    $courseName = isset($_POST['courseName']) ? trim($_POST['courseName']) : '';
    $courseHoles = isset($_POST['courseHoles']) ? intval($_POST['courseHoles']) : 0;

    if (empty($courseName)) {
        echo json_encode(['success' => false, 'error' => 'Course name is required']);
        exit();
    }

    if (!in_array($courseHoles, [9, 18])) {
        echo json_encode(['success' => false, 'error' => 'Course must have 9 or 18 holes']);
        exit();
    }

    // Check if courses table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'courses'");
    if ($tableCheck->num_rows == 0) {
        echo json_encode(['success' => false, 'error' => 'Courses table does not exist. Please create the database tables first.']);
        exit();
    }

    // Check if course name already exists
    $stmt = $conn->prepare("SELECT id FROM courses WHERE name = ?");
    $stmt->bind_param("s", $courseName);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Course name already exists']);
        $stmt->close();
        exit();
    }

    // Create course
    $stmt = $conn->prepare("INSERT INTO courses (name, holes) VALUES (?, ?)");
    $stmt->bind_param("si", $courseName, $courseHoles);

    if ($stmt->execute()) {
        $courseId = $conn->insert_id;
        echo json_encode(['success' => true, 'courseId' => $courseId]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error creating course: ' . $stmt->error]);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
    error_log("addcourse.php error: " . $e->getMessage());
}

$conn->close();
?>