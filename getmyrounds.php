<?php
require_once './config.php';
require_once './SessionManager.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if (!$session) {
    echo '<p style="color: red;">User not logged in</p>';
    exit();
}

$userId = $session['user_id'];
$seasonId = isset($_GET['seasonId']) ? intval($_GET['seasonId']) : 0;

if ($seasonId <= 0) {
    echo '<p style="color: red;">Invalid season ID</p>';
    exit();
}

try {
    // Get season info
    $stmt = $conn->prepare("SELECT year FROM seasons WHERE id = ?");
    $stmt->bind_param("i", $seasonId);
    $stmt->execute();
    $seasonResult = $stmt->get_result();
    
    if ($seasonResult->num_rows === 0) {
        echo '<p style="color: red;">Season not found</p>';
        exit();
    }
    
    $season = $seasonResult->fetch_assoc();
    $stmt->close();
    
    // Get player's rounds for this season
    $stmt = $conn->prepare("
        SELECT r.id, r.round_date, r.total_score, r.total_penalties, r.total_ob, r.points, r.match_result, r.is_completed,
               c.name as course_name, c.holes,
               (SELECT SUM(h.par) FROM holes h WHERE h.course_id = c.id) as course_par
        FROM rounds r
        JOIN courses c ON r.course_id = c.id
        WHERE r.user_id = ? AND r.season_id = ?
        ORDER BY r.round_date DESC
    ");
    
    $stmt->bind_param("ii", $userId, $seasonId);
    $stmt->execute();
    $roundsResult = $stmt->get_result();
    
    echo '<h5>My Rounds - ' . $season['year'] . ' Season</h5>';
    
    if ($roundsResult->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table">';
        echo '<tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ddd; padding: 8px;">Date</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Course</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Score</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Par</th>
                <th style="border: 1px solid #ddd; padding: 8px;">+/-</th>
                <th style="border: 1px solid #ddd; padding: 8px; color: #4CAF50;">Points</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Result</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Status</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Actions</th>
              </tr>';
        
        $totalRounds = 0;
        $completedRounds = 0;
        $totalPoints = 0;
        $wins = 0;
        $losses = 0;
        
        while ($round = $roundsResult->fetch_assoc()) {
            $totalRounds++;
            if ($round['is_completed']) {
                $completedRounds++;
                $totalPoints += $round['points'] ? $round['points'] : 0;
                if ($round['match_result'] === 'won') $wins++;
                elseif ($round['match_result'] === 'lost') $losses++;
            }
            
            $dateFormatted = date('M j, Y g:i A', strtotime($round['round_date']));
            $toPar = $round['course_par'] ? ($round['total_score'] - $round['course_par']) : null;
            $toParText = $toPar !== null ? ($toPar > 0 ? '+' . $toPar : ($toPar < 0 ? $toPar : 'E')) : '-';
            $matchResult = $round['match_result'] ? ucfirst($round['match_result']) : '-';
            $status = $round['is_completed'] ? 'Complete' : 'In Progress';
            $statusColor = $round['is_completed'] ? '#28a745' : '#ffc107';
            $resultColor = $round['match_result'] === 'won' ? '#28a745' : ($round['match_result'] === 'lost' ? '#dc3545' : '#6c757d');
            $points = $round['points'] ? number_format($round['points'], 1) : '0.0';
            $pointsColor = $round['points'] >= 0 ? '#4CAF50' : '#f44336';
            
            echo '<tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . $dateFormatted . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($round['course_name']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . ($round['total_score'] ?: '-') . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . ($round['course_par'] ?: '-') . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $toParText . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-weight: bold; color: ' . $pointsColor . ';">' . $points . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center; color: ' . $resultColor . ';">' . $matchResult . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center; color: ' . $statusColor . ';">' . $status . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                        <button onclick="viewMyRoundDetails(' . $round['id'] . ')" style="background-color: #007bff; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; font-size: 12px;">View</button>';
            
            // Show resume button for incomplete rounds
            if (!$round['is_completed']) {
                echo '<button onclick="resumeRound(' . $round['id'] . ')" style="background-color: #28a745; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; font-size: 12px; margin-left: 3px;">Resume</button>';
            }
            
            echo '</td>
                  </tr>';
        }
        
        echo '</table>';
        echo '</div>'; // Close table-responsive div
        
        // Add summary statistics
        echo '<div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">';
        echo '<h6>Season Summary</h6>';
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">';
        echo '<div><strong>Total Rounds:</strong> ' . $totalRounds . '</div>';
        echo '<div><strong>Completed:</strong> ' . $completedRounds . '</div>';
        echo '<div><strong>In Progress:</strong> ' . ($totalRounds - $completedRounds) . '</div>';
        echo '<div><strong>Wins:</strong> ' . $wins . '</div>';
        echo '<div><strong>Losses:</strong> ' . $losses . '</div>';
        echo '<div style="color: #4CAF50; font-weight: bold;"><strong>Total Points:</strong> ' . number_format($totalPoints, 1) . '</div>';
        echo '</div>';
        echo '</div>';
        
    } else {
        echo '<p>No rounds found for this season.</p>';
        echo '<p style="color: #6c757d; font-style: italic;">Start playing rounds to see them appear here!</p>';
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo '<p style="color: red;">Error loading rounds: ' . htmlspecialchars($e->getMessage()) . '</p>';
    error_log("getmyrounds.php error: " . $e->getMessage());
}

$conn->close();
?>