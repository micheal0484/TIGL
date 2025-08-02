<?php
class SessionManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function createSession($userId, $username, $isAdmin) {
        // Clean up expired sessions first
        $this->cleanupExpiredSessions();
        
        // Generate session token
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Store session in database
        $stmt = $this->conn->prepare("INSERT INTO user_sessions (session_token, user_id, username, isAdmin, expires_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $sessionToken, $userId, $username, $isAdmin, $expiresAt);
        
        if ($stmt->execute()) {
            // Set cookie
            setcookie('session_token', $sessionToken, strtotime('+24 hours'), '/', '', false, true);
            return $sessionToken;
        }
        
        return false;
    }
    
    public function getSession($sessionToken = null) {
        if (!$sessionToken) {
            $sessionToken = isset($_COOKIE['session_token']) ? $_COOKIE['session_token'] : '';
        }
        
        if (empty($sessionToken)) {
            return false;
        }
        
        // Check if session exists and is not expired
        $stmt = $this->conn->prepare("SELECT user_id, username, isAdmin FROM user_sessions WHERE session_token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $sessionToken);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    public function destroySession($sessionToken = null) {
        if (!$sessionToken) {
            $sessionToken = isset($_COOKIE['session_token']) ? $_COOKIE['session_token'] : '';
        }
        
        if (!empty($sessionToken)) {
            $stmt = $this->conn->prepare("DELETE FROM user_sessions WHERE session_token = ?");
            $stmt->bind_param("s", $sessionToken);
            $stmt->execute();
            
            // Clear cookie
            setcookie('session_token', '', time() - 3600, '/', '', false, true);
        }
    }
    
    private function cleanupExpiredSessions() {
        $stmt = $this->conn->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
        $stmt->execute();
    }
}
?>