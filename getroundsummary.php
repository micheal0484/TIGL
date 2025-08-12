<?php
session_start();
require_once './config.php';
require_once './SessionManager.php';
require_once './PointsCalculator.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in using SessionManager
$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if (!$session) {
    echo '<p style="color: red;">User not logged in</p>';
    exit();
}

$userId = $session['user_id'];
$isAdmin = $session['isAdmin'];
$roundId = isset($_GET['roundId']) ? intval($_GET['roundId']) : 0;

if ($roundId <= 0) {
    echo '<p style="color: red;">Invalid round ID</p>';
    exit();
}

try {
    // Get round details - admins can view any round, players can only view their own
    if ($isAdmin) {
        // Admin can view any round
        $stmt = $conn->prepare("
            SELECT r.id, r.total_score, r.total_penalties, r.total_ob, r.points, r.user_id,
                   c.name as course_name, c.holes, s.year, u.username as player_name, u.handicap
            FROM rounds r
            JOIN courses c ON r.course_id = c.id
            JOIN seasons s ON r.season_id = s.id
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->bind_param("i", $roundId);
    } else {
        // Player can only view their own rounds
        $stmt = $conn->prepare("
            SELECT r.id, r.total_score, r.total_penalties, r.total_ob, r.points, r.user_id,
                   c.name as course_name, c.holes, s.year, u.username as player_name, u.handicap
            FROM rounds r
            JOIN courses c ON r.course_id = c.id
            JOIN seasons s ON r.season_id = s.id
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ? AND r.user_id = ?
        ");
        $stmt->bind_param("ii", $roundId, $userId);
    }
    
    $stmt->execute();
    $roundResult = $stmt->get_result();
    
    if ($roundResult->num_rows === 0) {
        echo '<p style="color: red;">Round not found or access denied</p>';
        exit();
    }
    
    $round = $roundResult->fetch_assoc();
    $stmt->close();
    
    // Calculate net score
    $netScore = $round['total_score'] - $round['handicap'];
    
    // Get hole scores with points
    $stmt = $conn->prepare("
        SELECT hs.hole_number, hs.score, hs.penalties, hs.ob_strokes, hs.points, h.par, h.yardage
        FROM hole_scores hs
        JOIN holes h ON h.course_id = (SELECT course_id FROM rounds WHERE id = ?) AND h.hole_number = hs.hole_number
        WHERE hs.round_id = ?
        ORDER BY hs.hole_number
    ");
    $stmt->bind_param("ii", $roundId, $roundId);
    $stmt->execute();
    $scoresResult = $stmt->get_result();
    
    echo '<div style="margin-bottom: 20px;">';
    echo '<h5>Round Summary</h5>';
    
    // Show player name if admin is viewing
    if ($isAdmin) {
        echo '<p><strong>Player:</strong> ' . htmlspecialchars($round['player_name']) . ' (Handicap: ' . $round['handicap'] . ')</p>';
    }
    
    echo '<p><strong>Course:</strong> ' . htmlspecialchars($round['course_name']) . '</p>';
    echo '<p><strong>Season:</strong> ' . $round['year'] . '</p>';
    echo '<p><strong>Total Score:</strong> ' . $round['total_score'] . '</p>';
    echo '<p><strong>Net Score:</strong> ' . $netScore . ' (Total - Handicap)</p>';
    echo '<p><strong>Total Penalties:</strong> ' . $round['total_penalties'] . '</p>';
    echo '<p><strong>Total OB Strokes:</strong> ' . $round['total_ob'] . '</p>';
    echo '<p style="color: #4CAF50; font-weight: bold; font-size: 18px;"><strong>Total Points:</strong> ' . number_format($round['points'], 1) . '</p>';
    echo '</div>';
    
    if ($scoresResult->num_rows > 0) {
        echo '<h5>Hole-by-Hole Scores</h5>';
        echo '<table class="table table-responsive">';
        echo '<tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ddd; padding: 8px;">Hole</th>
                <th>Par</th>
                <th>Score</th>
                <th>+/-</th>
                <th>Penalties</th>
                <th>OB</th>
                <th>Points</th>
              </tr>';
        
        $totalPar = 0;
        while ($score = $scoresResult->fetch_assoc()) {
            $totalPar += $score['par'];
            $plusMinus = $score['score'] - $score['par'];
            $plusMinusText = $plusMinus > 0 ? '+' . $plusMinus : ($plusMinus < 0 ? $plusMinus : 'E');
            
            echo '<tr>
                    <td>' . $score['hole_number'] . '</td>
                    <td>' . $score['par'] . '</td>
                    <td>' . $score['score'] . '</td>
                    <td>' . $plusMinusText . '</td>
                    <td>' . $score['penalties'] . '</td>
                    <td>' . $score['ob_strokes'] . '</td>
                    <td class="text-center text-success font-weight-bold">' . number_format($score['points'], 1) . '</td>
                  </tr>';
        }
        
        // Add totals row
        $totalPlusMinus = $round['total_score'] - $totalPar;
        $totalPlusMinusText = $totalPlusMinus > 0 ? '+' . $totalPlusMinus : ($totalPlusMinus < 0 ? $totalPlusMinus : 'E');
        
        // Calculate net to par
        $netToPar = $netScore - $totalPar;
        $netToParText = $netToPar > 0 ? '+' . $netToPar : ($netToPar < 0 ? $netToPar : 'E');
        
        echo '<tr>
                <td>Total</td>
                <td>' . $totalPar . '</td>
                <td>' . $round['total_score'] . '</td>
                <td>' . $totalPlusMinusText . '</td>
                <td>' . $round['total_penalties'] . '</td>
                <td>' . $round['total_ob'] . '</td>
                <td class="text-center text-success font-weight-bold">' . number_format($round['points'], 1) . '</td>
              </tr>';
        
        // Add net score row
        echo '<tr class="bg-info font-weight-bold text-dark">
                <td>Net</td>
                <td>' . $totalPar . '</td>
                <td>' . $netScore . '</td>
                <td>' . $netToParText . '</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
              </tr>';
        $stmt->close();

        // Add points breakdown row if the round has match result or bonus points
        $stmt = $conn->prepare("SELECT match_result FROM rounds WHERE id = ?");
        $stmt->bind_param("i", $roundId);
        $stmt->execute();
        $matchResult = $stmt->get_result()->fetch_assoc()['match_result'];
        $stmt->close();

        if ($matchResult) {
            // Calculate bonus points using PointsCalculator
            $pointsCalculator = new PointsCalculator($conn);
            $quota = $pointsCalculator->getUserQuota($session['user_id']);
            $matchPoints = $pointsCalculator->getMatchResultPoints($matchResult === 'won');
            $roundBonusPoints = $pointsCalculator->calculateRoundBonusPoints($round['total_score'], $totalPar);
            
            // Calculate hole points (total points minus quota and bonuses)
            $holePoints = $round['points'] - $quota - $matchPoints - $roundBonusPoints;

            echo '<tr class="bg-light font-weight-bold text-dark">
                    <td>Points</td>
                    <td class="font-weight-normal">Quota: ' . number_format($quota, 1) . '</td>
                    <td class="font-weight-normal">Hole: +' . number_format($holePoints, 1) . '</td>
                    <td class="font-weight-normal">Match: ' . ($matchPoints >= 0 ? '+' : '') . number_format($matchPoints, 1) . '</td>
                    <td class="font-weight-normal">Bonus: ' . ($roundBonusPoints >= 0 ? '+' : '') . number_format($roundBonusPoints, 1) . '</td>
                    <td>-</td>
                    <td class="text-center text-success font-weight-bold">' . number_format($round['points'], 1) . '</td>
                  </tr>';
            
            // Add explanation row
            echo '<tr class="bg-light font-italic text-muted">
                    <td colspan="7" class="border-1 p-2 text-center" style="font-size: 11px;">
                        Points Breakdown: Quota (Handicap - 18) + Hole Performance + Match Result (' . ucfirst($matchResult) . ') + Round Bonus = Total Points
                    </td>
                  </tr>';
        }

        echo '</table>';
    }
    
} catch (Exception $e) {
    echo '<p style="color: red;">Error loading round summary: ' . htmlspecialchars($e->getMessage()) . '</p>';
    error_log("getroundsummary.php error: " . $e->getMessage());
}

$conn->close();
?>