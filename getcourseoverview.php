<?php
require_once './config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$courseId = isset($_GET['courseId']) ? intval($_GET['courseId']) : 0;

if ($courseId <= 0) {
    echo '<p style="color: red;">Invalid course ID</p>';
    exit();
}

try {
    // Get course details
    $stmt = $conn->prepare("SELECT name, holes FROM courses WHERE id = ?");
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $courseResult = $stmt->get_result();
    
    if ($courseResult->num_rows == 0) {
        echo '<p style="color: red;">Course not found</p>';
        exit();
    }
    
    $course = $courseResult->fetch_assoc();
    $stmt->close();
    
    // Get hole details
    $stmt = $conn->prepare("SELECT hole_number, yardage, par, mens_handicap, womens_handicap FROM holes WHERE course_id = ? ORDER BY hole_number");
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $holesResult = $stmt->get_result();
    
    echo '<div style="margin-bottom: 20px;">';
    echo '<h5>Course Information</h5>';
    echo '<p><strong>Course Name:</strong> ' . htmlspecialchars($course['name']) . '</p>';
    echo '<p><strong>Number of Holes:</strong> ' . $course['holes'] . '</p>';
    echo '</div>';
    
    if ($holesResult->num_rows > 0) {
        echo '<h5>Hole Details</h5>';
        echo '<table style="width: 100%; border-collapse: collapse;">';
        echo '<tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ddd; padding: 8px;">Hole</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Yardage</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Par</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Men\'s Handicap</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Women\'s Handicap</th>
              </tr>';
        
        $totalYardage = 0;
        $totalPar = 0;
        
        while ($hole = $holesResult->fetch_assoc()) {
            $totalYardage += $hole['yardage'];
            $totalPar += $hole['par'];
            
            echo '<tr>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $hole['hole_number'] . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $hole['yardage'] . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $hole['par'] . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $hole['mens_handicap'] . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $hole['womens_handicap'] . '</td>
                  </tr>';
        }
        
        // Add totals row
        echo '<tr style="background-color: #e9ecef; font-weight: bold;">
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">Total</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $totalYardage . '</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $totalPar . '</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">-</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">-</td>
              </tr>';
        
        echo '</table>';
    } else {
        echo '<p>No hole details found for this course.</p>';
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo '<p style="color: red;">Error loading course overview: ' . htmlspecialchars($e->getMessage()) . '</p>';
    error_log("getcourseoverview.php error: " . $e->getMessage());
}

$conn->close();
?>