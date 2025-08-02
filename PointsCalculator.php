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
    
    public function calculateHolePoints($score, $par, $penalties, $obStrokes) {
        $scoreToPar = $score - $par;
        $points = 0.0;
        
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
        
        $points += ($penalties * floatval($this->pointsConfig['points_penalty_stroke']));
        $points += ($obStrokes * floatval($this->pointsConfig['points_ob_stroke']));
        
        return $points;
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