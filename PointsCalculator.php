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
            'points_ob_stroke' => -2.0,
            'points_match_win' => 2.0,
            'points_match_loss' => 0.0,
            'points_under_par_round' => 3.0,
            'points_even_par_round' => 1.0
        );
    }
    
    // Get user's quota based on their handicap
    public function getUserQuota($userId) {
        try {
            $stmt = $this->conn->prepare("SELECT handicap FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $quota = floatval($user['handicap']) - 18.0;
                $stmt->close();
                return $quota;
            }
            $stmt->close();
            return 0.0; // Default if user not found
        } catch (Exception $e) {
            return 0.0;
        }
    }
    
    // Add a new method to get quota for a specific round
    public function getRoundQuota($roundId) {
        try {
            $stmt = $this->conn->prepare("SELECT handicap_used FROM rounds WHERE id = ?");
            $stmt->bind_param("i", $roundId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $round = $result->fetch_assoc();
                $quota = floatval($round['handicap_used']) - 18.0;
                $stmt->close();
                return $quota;
            }
            $stmt->close();
            return 0.0; // Default if round not found
        } catch (Exception $e) {
            error_log("Error getting round quota: " . $e->getMessage());
            return 0.0;
        }
    }
    
    public function calculateHolePoints($score, $par, $penalties, $obStrokes) {
        $scoreToPar = $score - $par;
        $points = 0.0; // Start with float
        
        switch ($scoreToPar) {
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
        
        return floatval($points); // Ensure return is float
    }
    
    // Calculate total round points including quota
    public function calculateTotalRoundPoints($userId, $holePoints, $totalScore, $totalPar, $matchResult = null) {
        $quota = $this->getUserQuota($userId);
        $roundBonusPoints = $this->calculateRoundBonusPoints($totalScore, $totalPar);
        $matchPoints = 0.0;
        
        if ($matchResult) {
            $matchPoints = $this->getMatchResultPoints($matchResult === 'won');
        }
        
        $totalPoints = $quota + $holePoints + $roundBonusPoints + $matchPoints;
        
        return array(
            'quota' => $quota,
            'holePoints' => $holePoints,
            'roundBonusPoints' => $roundBonusPoints,
            'matchPoints' => $matchPoints,
            'totalPoints' => $totalPoints
        );
    }
    
    // Update the calculateTotalRoundPoints method to use round-specific handicap:
    public function calculateTotalRoundPointsForRound($roundId, $holePoints, $totalScore, $totalPar, $matchResult = null) {
        $quota = $this->getRoundQuota($roundId);
        $roundBonusPoints = $this->calculateRoundBonusPoints($totalScore, $totalPar);
        $matchPoints = 0.0;
        
        if ($matchResult) {
            $matchPoints = $this->getMatchResultPoints($matchResult === 'won');
        }
        
        $totalPoints = $quota + $holePoints + $roundBonusPoints + $matchPoints;
        
        return array(
            'quota' => $quota,
            'holePoints' => $holePoints,
            'roundBonusPoints' => $roundBonusPoints,
            'matchPoints' => $matchPoints,
            'totalPoints' => $totalPoints
        );
    }
    
    public function calculateRoundBonusPoints($totalScore, $totalPar) {
        $scoreToPar = $totalScore - $totalPar;
        
        if ($scoreToPar < 0) {
            return floatval($this->pointsConfig['points_under_par_round']);
        } elseif ($scoreToPar == 0) {
            return floatval($this->pointsConfig['points_even_par_round']);
        }
        
        return 0.0;
    }
    
    public function getMatchResultPoints($won) {
        return $won ? floatval($this->pointsConfig['points_match_win']) : floatval($this->pointsConfig['points_match_loss']);
    }
}
?>