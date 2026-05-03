<?php
class PointsCalculator {
    private $conn;
    private $pointsConfig;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->loadPointsConfig();
    }
    
    private function loadPointsConfig() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM points_config WHERE id = 1");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $this->pointsConfig = $result->fetch_assoc();
            } else {
                $this->setDefaultConfig();
            }
            $stmt->close();
        } catch (Exception $e) {
            $this->setDefaultConfig();
        }
    }
    
    private function setDefaultConfig() {
        $this->pointsConfig = array(
            'points_albatross' => 3.0,
            'points_eagle' => 2.5,
            'points_birdie' => 1.5,
            'points_par' => 1.0,
            'points_bogey' => 0.5,
            'points_double_bogey' => 0.0,
            'points_triple_bogey' => -1.0,
            'points_worse' => -2.0,
            'points_penalty_stroke' => -0.5,
            'points_ob_stroke' => -1.0,
            'points_match_win' => 2.0,
            'points_match_loss' => 0.0,
            'points_under_par_round' => 3.0,
            'points_even_par_round' => 1.0
        );
    }
    
    public function calculateHoleStrokesReceived($handicapUsed, $totalHoles, $holeHandicapRank) {
        $handicapUsed = floatval($handicapUsed);
        $totalHoles = intval($totalHoles);
        $holeHandicapRank = intval($holeHandicapRank);

        if ($handicapUsed <= 0 || $totalHoles <= 0 || $holeHandicapRank <= 0) {
            return 0;
        }

        $effectiveHandicap = $handicapUsed;
        $roundedHandicap = intval(floor($effectiveHandicap + 0.5));

        $baseStrokes = intdiv($roundedHandicap, $totalHoles);
        $extraStrokes = $roundedHandicap % $totalHoles;

        $strokesReceived = $baseStrokes;
        if ($holeHandicapRank <= $extraStrokes) {
            $strokesReceived++;
        }
        error_log("Calculating strokes received: Handicap Used: $handicapUsed, Rounded Handicap: $roundedHandicap, Total Holes: $totalHoles, Hole Handicap Rank: $holeHandicapRank, Base Strokes: $baseStrokes, Extra Strokes: $extraStrokes, Strokes Received: $strokesReceived");

        // Current rules allow 0/1/2 strokes per hole.
        return max(0, min(2, $strokesReceived));
    }

    public function calculateHolePointsAgainstNetPar($score, $par, $strokesReceived, $penalties, $obStrokes) {
        $score = intval($score);
        $par = intval($par);
        $strokesReceived = intval($strokesReceived);
        $penalties = intval($penalties);
        $obStrokes = intval($obStrokes);

        // In net-par scoring, strokes received increase the gross target for a net par.
        $netPar = $par + $strokesReceived;
        $scoreToNetPar = $score - $netPar;
        $points = 0.0; // Start with float
        
        switch ($scoreToNetPar) {
            case -3:
                $points += floatval($this->pointsConfig['points_albatross']);
                break;
            case -2:
                $points += floatval($this->pointsConfig['points_eagle']);
                break;
            case -1:
                $points += floatval($this->pointsConfig['points_birdie']);
                break;
            case 0:
                $points += floatval($this->pointsConfig['points_par']);
                break;
            case 1:
                $points += floatval($this->pointsConfig['points_bogey']);
                break;
            case 2:
                $points += floatval($this->pointsConfig['points_double_bogey']);
                break;
            case 3:
                $points += floatval($this->pointsConfig['points_triple_bogey']);
                break;
            default:
                $points += floatval($this->pointsConfig['points_worse']);
                break;
        }
        
        // Add penalty and OB adjustments
        $points += ($penalties * floatval($this->pointsConfig['points_penalty_stroke']));
        $points += ($obStrokes * floatval($this->pointsConfig['points_ob_stroke']));
        error_log("Calculating hole points: Score: $score, Par: $par, Strokes Received: $strokesReceived, Net Par: $netPar, Score to Net Par: $scoreToNetPar, Penalties: $penalties, OB Strokes: $obStrokes, Points: $points");

        return array(
            'points' => floatval($points),
            'netPar' => $netPar,
            'scoreToNetPar' => $scoreToNetPar,
            'strokesReceived' => $strokesReceived
        );
    }

    // Backward-compatible helper used by older call sites.
    public function calculateHolePoints($score, $par, $penalties, $obStrokes) {
        $result = $this->calculateHolePointsAgainstNetPar($score, $par, 0, $penalties, $obStrokes);
        return $result['points'];
    }
    
    // Calculate total round points based on hole points + match + net-target bonus.
    public function calculateTotalRoundPoints($holePoints, $netScore, $matchResult = null, $targetNetScore = 36.0) {
        $holePoints = floatval($holePoints);
        $netScore = floatval($netScore);
        $targetNetScore = floatval($targetNetScore);

        $roundBonusPoints = $this->calculateRoundBonusPoints($netScore, $targetNetScore);
        error_log("Calculating total round points: Hole Points: $holePoints, Net Score: $netScore, Target Net Score: $targetNetScore, Round Bonus Points: $roundBonusPoints, Match Result: $matchResult");
        $matchPoints = 0.0;
        
        if ($matchResult) {
            $matchPoints = $this->getMatchResultPoints($matchResult === 'won');
        }
        
        $totalPoints = $holePoints + $roundBonusPoints + $matchPoints;
        if ($totalPoints < -5.0) {
            $totalPoints = -5.0; // Cap total points to prevent extreme negatives
        }
        
        return array(
            'holePoints' => $holePoints,
            'roundBonusPoints' => $roundBonusPoints,
            'matchPoints' => $matchPoints,
            'totalPoints' => $totalPoints,
            'targetNetScore' => $targetNetScore,
            'netScore' => $netScore
        );
    }

    public function calculateRoundBonusPoints($netScore, $targetNetScore = 36.0) {
        $netScore = floatval($netScore);
        $targetNetScore = floatval($targetNetScore);

        if ($netScore < $targetNetScore) {
            return floatval($this->pointsConfig['points_under_par_round']);
        } elseif ($netScore == $targetNetScore) {
            return floatval($this->pointsConfig['points_even_par_round']);
        }
        
        return 0.0;
    }
    
    public function getMatchResultPoints($won) {
        return $won ? floatval($this->pointsConfig['points_match_win']) : floatval($this->pointsConfig['points_match_loss']);
    }
}
?>