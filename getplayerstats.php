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
    
    // Get player's rounds for this season including points
    $stmt = $conn->prepare("
        SELECT r.id, r.round_date, r.total_score, r.total_penalties, r.total_ob, r.points, r.match_result, r.is_completed,
               c.name as course_name, c.holes,
               (SELECT SUM(h.par) FROM holes h WHERE h.course_id = c.id) as course_par
        FROM rounds r
        JOIN courses c ON r.course_id = c.id
        WHERE r.user_id = ? AND r.season_id = ? AND r.is_completed = 1
        ORDER BY r.round_date DESC
    ");
    
    $stmt->bind_param("ii", $userId, $seasonId);
    $stmt->execute();
    $roundsResult = $stmt->get_result();
    
    echo '<h5>Your ' . $season['year'] . ' Season Statistics</h5>';
    
    if ($roundsResult->num_rows > 0) {
        $totalRounds = 0;
        $totalScore = 0;
        $totalPar = 0;
        $totalPoints = 0;
        $wins = 0;
        $losses = 0;
        $totalPenalties = 0;
        $totalOB = 0;
        $bestScore = null;
        $worstScore = null;
        $bestToPar = null;
        $worstToPar = null;
        $bestPoints = null;
        $worstPoints = null;
        
        echo '<h6>Recent Rounds</h6>';
        echo '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        echo '<tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ddd; padding: 8px;">Date</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Course</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Score</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Par</th>
                <th style="border: 1px solid #ddd; padding: 8px;">+/-</th>
                <th style="border: 1px solid #ddd; padding: 8px; color: #4CAF50;">Points</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Penalties</th>
                <th style="border: 1px solid #ddd; padding: 8px;">OB</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Result</th>
              </tr>';
        
        while ($round = $roundsResult->fetch_assoc()) {
            $totalRounds++;
            $totalScore += $round['total_score'];
            $totalPar += $round['course_par'];
            $totalPenalties += $round['total_penalties'];
            $totalOB += $round['total_ob'];
            $totalPoints += $round['points'] ? $round['points'] : 0;
            
            if ($round['match_result'] === 'won') $wins++;
            elseif ($round['match_result'] === 'lost') $losses++;
            
            $toPar = $round['total_score'] - $round['course_par'];
            $roundPoints = $round['points'] ? floatval($round['points']) : 0.0;
            
            // Track best/worst scores
            if ($bestScore === null || $round['total_score'] < $bestScore) {
                $bestScore = $round['total_score'];
            }
            if ($worstScore === null || $round['total_score'] > $worstScore) {
                $worstScore = $round['total_score'];
            }
            
            // Track best/worst to par
            if ($bestToPar === null || $toPar < $bestToPar) {
                $bestToPar = $toPar;
            }
            if ($worstToPar === null || $toPar > $worstToPar) {
                $worstToPar = $toPar;
            }
            
            // Track best/worst points
            if ($bestPoints === null || $roundPoints > $bestPoints) {
                $bestPoints = $roundPoints;
            }
            if ($worstPoints === null || $roundPoints < $worstPoints) {
                $worstPoints = $roundPoints;
            }
            
            $dateFormatted = date('M j, Y', strtotime($round['round_date']));
            $toParText = $toPar > 0 ? '+' . $toPar : ($toPar < 0 ? $toPar : 'E');
            $matchResult = $round['match_result'] ? ucfirst($round['match_result']) : '-';
            $resultColor = $round['match_result'] === 'won' ? 'green' : ($round['match_result'] === 'lost' ? 'red' : 'black');
            $pointsText = number_format($roundPoints, 1);
            $pointsColor = $roundPoints >= 0 ? '#4CAF50' : '#f44336';
            
            echo '<tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . $dateFormatted . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($round['course_name']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $round['total_score'] . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $round['course_par'] . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $toParText . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-weight: bold; color: ' . $pointsColor . ';">' . $pointsText . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $round['total_penalties'] . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $round['total_ob'] . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center; color: ' . $resultColor . ';">' . $matchResult . '</td>
                  </tr>';
        }
        
        echo '</table>';
        
        // Calculate statistics
        $avgScore = $totalScore / $totalRounds;
        $avgToPar = ($totalScore - $totalPar) / $totalRounds;
        $avgPoints = $totalPoints / $totalRounds;
        $winPercentage = $totalRounds > 0 ? ($wins / $totalRounds) * 100 : 0;
        
        $bestToParText = $bestToPar > 0 ? '+' . $bestToPar : ($bestToPar < 0 ? $bestToPar : 'E');
        $worstToParText = $worstToPar > 0 ? '+' . $worstToPar : ($worstToPar < 0 ? $worstToPar : 'E');
        $avgToParText = $avgToPar > 0 ? '+' . number_format($avgToPar, 1) : (number_format($avgToPar, 1));
        
        // Display summary statistics with points
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">';
        
        echo '<div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px;">';
        echo '<h6 style="margin-top: 0;">Round Summary</h6>';
        echo '<p><strong>Total Rounds:</strong> ' . $totalRounds . '</p>';
        echo '<p><strong>Wins:</strong> ' . $wins . '</p>';
        echo '<p><strong>Losses:</strong> ' . $losses . '</p>';
        echo '<p><strong>Win Rate:</strong> ' . number_format($winPercentage, 1) . '%</p>';
        echo '</div>';
        
        echo '<div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px;">';
        echo '<h6 style="margin-top: 0;">Scoring</h6>';
        echo '<p><strong>Average Score:</strong> ' . number_format($avgScore, 1) . '</p>';
        echo '<p><strong>Average to Par:</strong> ' . $avgToParText . '</p>';
        echo '<p><strong>Best Score:</strong> ' . $bestScore . '</p>';
        echo '<p><strong>Worst Score:</strong> ' . $worstScore . '</p>';
        echo '</div>';
        
        echo '<div style="background-color: #e8f5e9; padding: 15px; border-radius: 5px; border-left: 4px solid #4CAF50;">';
        echo '<h6 style="margin-top: 0; color: #4CAF50;">Points Performance</h6>';
        echo '<p><strong>Total Points:</strong> <span style="color: #4CAF50; font-weight: bold;">' . number_format($totalPoints, 1) . '</span></p>';
        echo '<p><strong>Average Points:</strong> <span style="color: #4CAF50; font-weight: bold;">' . number_format($avgPoints, 1) . '</span></p>';
        echo '<p><strong>Best Round:</strong> <span style="color: #4CAF50; font-weight: bold;">' . number_format($bestPoints, 1) . '</span></p>';
        echo '<p><strong>Worst Round:</strong> <span style="color: ' . ($worstPoints < 0 ? '#f44336' : '#4CAF50') . '; font-weight: bold;">' . number_format($worstPoints, 1) . '</span></p>';
        echo '</div>';
        
        echo '<div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px;">';
        echo '<h6 style="margin-top: 0;">Other Stats</h6>';
        echo '<p><strong>Best to Par:</strong> ' . $bestToParText . '</p>';
        echo '<p><strong>Worst to Par:</strong> ' . $worstToParText . '</p>';
        echo '<p><strong>Total Penalties:</strong> ' . $totalPenalties . '</p>';
        echo '<p><strong>Total OB Strokes:</strong> ' . $totalOB . '</p>';
        echo '</div>';
        
        echo '</div>';
        
        // Add points leaderboard position if we have multiple players
        echo '<div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border-radius: 5px; border-left: 4px solid #ffc107;">';
        echo '<h6 style="margin-top: 0; color: #856404;">Season Ranking</h6>';
        
        // Get season leaderboard
        $leaderStmt = $conn->prepare("
            SELECT u.username, SUM(r.points) as season_points, COUNT(r.id) as rounds_played
            FROM rounds r
            JOIN users u ON r.user_id = u.id
            WHERE r.season_id = ? AND r.is_completed = 1
            GROUP BY r.user_id, u.username
            ORDER BY season_points DESC
        ");
        $leaderStmt->bind_param("i", $seasonId);
        $leaderStmt->execute();
        $leaderResult = $leaderStmt->get_result();
        
        $position = 1;
        $currentUserPosition = null;
        while ($leader = $leaderResult->fetch_assoc()) {
            if ($leader['username'] === $session['username']) {
                $currentUserPosition = $position;
                break;
            }
            $position++;
        }
        
        if ($currentUserPosition) {
            echo '<p><strong>Your Position:</strong> #' . $currentUserPosition . ' with ' . number_format($totalPoints, 1) . ' points</p>';
        } else {
            echo '<p>No ranking available - complete some rounds to see your position!</p>';
        }
        
        $leaderStmt->close();
        echo '</div>';
        
    } else {
        echo '<p>No completed rounds found for this season.</p>';
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo '<p style="color: red;">Error loading statistics: ' . htmlspecialchars($e->getMessage()) . '</p>';
    error_log("getplayerstats.php error: " . $e->getMessage());
}

$conn->close();
?>