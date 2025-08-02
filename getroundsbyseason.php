<?php
require_once './config.php';
require_once './SessionManager.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is admin
$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if (!$session || !$session['isAdmin']) {
    echo '<p style="color: red;">Access denied. Admin privileges required.</p>';
    exit();
}

$seasonId = isset($_GET['seasonId']) ? intval($_GET['seasonId']) : 0;

if ($seasonId <= 0) {
    echo '<p style="color: red;">Invalid season ID</p>';
    exit();
}

try {
    // Get rounds for the selected season including points
    $stmt = $conn->prepare("
        SELECT r.id, r.round_date, r.total_score, r.total_penalties, r.total_ob, r.points, r.match_result, r.is_completed,
               u.username, c.name as course_name, s.year as season_year
        FROM rounds r
        JOIN users u ON r.user_id = u.id
        JOIN courses c ON r.course_id = c.id
        JOIN seasons s ON r.season_id = s.id
        WHERE r.season_id = ?
        ORDER BY r.round_date DESC, u.username
    ");
    
    $stmt->bind_param("i", $seasonId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<h5>Rounds for Season ' . htmlspecialchars($result->fetch_assoc()['season_year']) . '</h5>';
        
        // Reset result pointer
        $result->data_seek(0);
        
        echo '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
        echo '<tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ddd; padding: 8px;">Date</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Player</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Course</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Score</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Penalties</th>
                <th style="border: 1px solid #ddd; padding: 8px;">OB</th>
                <th style="border: 1px solid #ddd; padding: 8px; color: #4CAF50;">Points</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Result</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Status</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Actions</th>
              </tr>';
        
        while ($row = $result->fetch_assoc()) {
            $dateFormatted = date('M j, Y g:i A', strtotime($row['round_date']));
            $matchResult = $row['match_result'] ? ucfirst($row['match_result']) : 'Not Set';
            $status = $row['is_completed'] ? 'Complete' : 'In Progress';
            $statusColor = $row['is_completed'] ? 'green' : 'orange';
            $points = $row['points'] ? number_format($row['points'], 1) : '0.0';
            
            echo '<tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . $dateFormatted . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($row['username']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($row['course_name']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . ($row['total_score'] ?: '-') . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . ($row['total_penalties'] ?: '0') . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . ($row['total_ob'] ?: '0') . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-weight: bold; color: #4CAF50;">' . $points . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $matchResult . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center; color: ' . $statusColor . ';">' . $status . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                        <button onclick="viewRoundDetails(' . $row['id'] . ')" style="background-color: #007bff; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; margin-right: 5px; font-size: 12px;">View</button>
                        <button onclick="deleteRound(' . $row['id'] . ')" style="background-color: #dc3545; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; font-size: 12px;">Delete</button>
                    </td>
                  </tr>';
        }
        
        echo '</table>';
        
        // Add summary statistics including points
        $stmt->close();
        
        // Get summary stats including points
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_rounds,
                COUNT(CASE WHEN is_completed = 1 THEN 1 END) as completed_rounds,
                COUNT(CASE WHEN match_result = 'won' THEN 1 END) as wins,
                COUNT(CASE WHEN match_result = 'lost' THEN 1 END) as losses,
                AVG(CASE WHEN is_completed = 1 THEN total_score END) as avg_score,
                SUM(CASE WHEN is_completed = 1 THEN points END) as total_points,
                AVG(CASE WHEN is_completed = 1 THEN points END) as avg_points
            FROM rounds 
            WHERE season_id = ?
        ");
        $stmt->bind_param("i", $seasonId);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();
        
        echo '<div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">';
        echo '<h6>Season Summary</h6>';
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">';
        echo '<div><strong>Total Rounds:</strong> ' . $stats['total_rounds'] . '</div>';
        echo '<div><strong>Completed Rounds:</strong> ' . $stats['completed_rounds'] . '</div>';
        echo '<div><strong>Wins:</strong> ' . $stats['wins'] . '</div>';
        echo '<div><strong>Losses:</strong> ' . $stats['losses'] . '</div>';
        echo '<div><strong>Average Score:</strong> ' . ($stats['avg_score'] ? number_format($stats['avg_score'], 1) : 'N/A') . '</div>';
        echo '<div style="color: #4CAF50; font-weight: bold;"><strong>Total Points:</strong> ' . ($stats['total_points'] ? number_format($stats['total_points'], 1) : '0.0') . '</div>';
        echo '<div style="color: #4CAF50; font-weight: bold;"><strong>Average Points:</strong> ' . ($stats['avg_points'] ? number_format($stats['avg_points'], 1) : '0.0') . '</div>';
        echo '</div>';
        echo '</div>';
        
    } else {
        echo '<p>No rounds found for this season.</p>';
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo '<p style="color: red;">Error loading rounds: ' . htmlspecialchars($e->getMessage()) . '</p>';
    error_log("getroundsbyseason.php error: " . $e->getMessage());
}

$conn->close();
?>