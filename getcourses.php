<?php
require_once './config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

try {
    // Check if tables exist first
    $tableCheck = $conn->query("SHOW TABLES LIKE 'courses'");
    if ($tableCheck->num_rows == 0) {
        echo '<p style="color: red;">Error: Courses table does not exist. Please create the database tables first.</p>';
        exit();
    }

    // Get all courses with hole counts
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.holes, COUNT(h.id) as holes_added 
        FROM courses c 
        LEFT JOIN holes h ON c.id = h.course_id 
        GROUP BY c.id, c.name, c.holes 
        ORDER BY c.name
    ");

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo '<div class="table-responsive">';
            echo '<table class="table">';
            echo '<tr style="background-color: #f2f2f2;">
                    <th style="border: 1px solid #ddd; padding: 8px;">Course Name</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Holes</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Status</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Actions</th>
                  </tr>';
            
            while ($row = $result->fetch_assoc()) {
                $isComplete = ($row['holes_added'] == $row['holes']);
                $status = $isComplete ? 'Complete' : 'Incomplete (' . $row['holes_added'] . '/' . $row['holes'] . ')';
                
                if ($isComplete) {
                    $action = '<button data-action="view-course" data-course-id="' . $row['id'] . '" data-course-name="' . htmlspecialchars($row['name'], ENT_QUOTES) . '" style="background-color: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-right: 5px;">View Course</button>';
                    $action .= '<button data-action="edit-course" data-course-id="' . $row['id'] . '" data-course-name="' . htmlspecialchars($row['name'], ENT_QUOTES) . '" data-course-holes="' . $row['holes'] . '" style="background-color: #ffc107; color: black; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Edit Course</button>';
                } else {
                    $action = '<button data-action="complete-holes" data-course-id="' . $row['id'] . '" data-course-name="' . htmlspecialchars($row['name'], ENT_QUOTES) . '" data-course-holes="' . $row['holes'] . '" style="background-color: #007cba; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Complete Holes</button>';
                }
                
                echo '<tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($row['name']) . '</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">' . $row['holes'] . '</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">' . $status . '</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">' . $action . '</td>
                      </tr>';
            }
            echo '</table>';
            echo '</div>'; // Close table-responsive div
        } else {
            echo '<p>No courses found. Create your first course using the "Add Course" button.</p>';
        }
    } else {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo '<p style="color: red;">Error retrieving courses: ' . htmlspecialchars($e->getMessage()) . '</p>';
    error_log("getcourses.php error: " . $e->getMessage());
}

$conn->close();
?>