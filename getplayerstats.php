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
    
    echo '<h5>Your ' . $season['year'] . ' Season Statistics</h5>';
    
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
    
    // Check if player has any rounds for this season
    if ($roundsResult->num_rows > 0) {
        // Player has rounds - show full stats
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
        echo '<div class="table-responsive">';
        echo '<table class="table">';
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
        echo '</div>'; // Close table-responsive div
        
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
        
    } else {
        // Player has no rounds - show message
        echo '<div style="background-color: #e3f2fd; padding: 20px; border-radius: 5px; border-left: 4px solid #2196F3; margin-bottom: 20px;">';
        echo '<h6 style="margin-top: 0; color: #1976D2;">No Rounds Yet</h6>';
        echo '<p style="margin-bottom: 0; color: #1976D2;">You haven\'t completed any rounds for the ' . $season['year'] . ' season yet. Start playing to see your statistics and track your progress!</p>';
        echo '</div>';
        
        // Set default values for leaderboard calculation
        $totalRounds = 0;
        $totalPoints = 0;
        $avgPoints = 0;
    }
    
    $stmt->close();
    
    // ALWAYS show season leaderboard regardless of whether current user has rounds
    echo '<div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border-radius: 5px; border-left: 4px solid #ffc107;">';
    echo '<h6 style="margin-top: 0; color: #856404;">' . $season['year'] . ' Season Leaderboard</h6>';
    
    // Get season leaderboard with all players
    $leaderStmt = $conn->prepare("
        SELECT u.username, SUM(IFNULL(r.points, 0)) as season_points, COUNT(r.id) as rounds_played, u.id as user_id
        FROM users u
        LEFT JOIN rounds r ON u.id = r.user_id AND r.season_id = ? AND r.is_completed = 1
        WHERE u.isAdmin = 0
        GROUP BY u.id, u.username
        ORDER BY season_points DESC, rounds_played DESC
    ");
    $leaderStmt->bind_param("i", $seasonId);
    $leaderStmt->execute();
    $leaderResult = $leaderStmt->get_result();
    
    if ($leaderResult->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table">';
        echo '<tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Rank</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Player</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: center; color: #4CAF50;">Total Points</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Rounds Played</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Avg Points/Round</th>
              </tr>';
        
        $position = 1;
        $currentUserPosition = null;
        $lastPoints = null;
        $actualPosition = 1;
        
        while ($leader = $leaderResult->fetch_assoc()) {
            // Handle ties - same points get same rank
            if ($lastPoints !== null && $leader['season_points'] != $lastPoints) {
                $position = $actualPosition;
            }
            
            $isCurrentUser = ($leader['user_id'] == $userId);
            $rowStyle = $isCurrentUser ? 'background-color: #e8f5e9; font-weight: bold;' : '';
            
            if ($isCurrentUser) {
                $currentUserPosition = $position;
            }
            
            // Calculate average points for THIS leader
            $leaderAvgPoints = $leader['rounds_played'] > 0 ? $leader['season_points'] / $leader['rounds_played'] : 0;
            $pointsColor = $leader['season_points'] >= 0 ? '#4CAF50' : '#f44336';
            
            // Medal icons for top 3 (only if they have points)
            $rankDisplay = $position;
            if ($position == 1 && $leader['season_points'] > 0) $rankDisplay = '🥇 1st';
            elseif ($position == 2 && $leader['season_points'] > 0) $rankDisplay = '🥈 2nd';
            elseif ($position == 3 && $leader['season_points'] > 0) $rankDisplay = '🥉 3rd';
            
            // Show average points or "-" if no rounds
            $avgDisplay = $leader['rounds_played'] > 0 ? number_format($leaderAvgPoints, 1) : '-';
            
            echo '<tr style="' . $rowStyle . '">
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $rankDisplay . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($leader['username']) . ($isCurrentUser ? ' (You)' : '') . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center; color: ' . $pointsColor . '; font-weight: bold;">' . number_format($leader['season_points'], 1) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $leader['rounds_played'] . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $avgDisplay . '</td>
                  </tr>';
            
            $lastPoints = $leader['season_points'];
            $actualPosition++;
        }
        
        echo '</table>';
        echo '</div>'; // Close table-responsive div
        
        // Add summary info
        echo '<div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 3px;">';
        if ($currentUserPosition) {
            echo '<p style="margin: 5px 0;"><strong>Your Current Position:</strong> #' . $currentUserPosition . ' with ' . number_format($totalPoints, 1) . ' points from ' . $totalRounds . ' rounds</p>';
            
            if ($totalRounds > 0) {
                echo '<p style="margin: 5px 0;"><strong>Your Average:</strong> ' . number_format($avgPoints, 1) . ' points per round</p>';
            } else {
                echo '<p style="margin: 5px 0;">Complete some rounds to see your average!</p>';
            }
        } else {
            echo '<p style="margin: 5px 0;">You are not currently ranked. Complete some rounds to see your position!</p>';
        }
        echo '</div>';
        
    } else {
        echo '<p>No players found for this season.</p>';
    }

    $leaderStmt->close();
    echo '</div>';
    
} catch (Exception $e) {
    echo '<p style="color: red;">Error loading statistics: ' . htmlspecialchars($e->getMessage()) . '</p>';
    error_log("getplayerstats.php error: " . $e->getMessage());
}

$conn->close();
?>