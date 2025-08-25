<?php
require_once './config.php';
require_once './SessionManager.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in and is admin
$sessionManager = new SessionManager($conn);
$session = $sessionManager->getSession();

if (!$session) {
    http_response_code(401);
    echo "Unauthorized access. Please log in.";
    exit;
}

if (!$session['isAdmin']) {
    http_response_code(403);
    echo "Admin access required.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed.";
    exit;
}

try {
    $seasonId = intval($_POST['seasonId']);
    
    if ($seasonId <= 0) {
        http_response_code(400);
        echo "Invalid season ID.";
        exit;
    }
    
    // Check if season exists
    $stmt = $conn->prepare("SELECT year FROM seasons WHERE id = ?");
    $stmt->bind_param("i", $seasonId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo "Season not found.";
        $stmt->close();
        exit;
    }
    
    $season = $result->fetch_assoc();
    $stmt->close();
    
    // Start transaction
    $conn->autocommit(false);
    
    try {
        // Deactivate all seasons first
        $stmt = $conn->prepare("UPDATE seasons SET is_active = 0");
        $stmt->execute();
        $stmt->close();
        
        // Activate the selected season
        $stmt = $conn->prepare("UPDATE seasons SET is_active = 1 WHERE id = ?");
        $stmt->bind_param("i", $seasonId);
        
        if ($stmt->execute()) {
            $conn->commit();
            echo "Season " . $season['year'] . " has been activated successfully!";
        } else {
            throw new Exception("Failed to activate season: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error activating season: " . $e->getMessage();
    error_log("activateseason.php error: " . $e->getMessage());
}

$conn->close();
?>
