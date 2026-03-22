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
    $year = intval($_POST['year']);
    $setAsActive = isset($_POST['setAsActive']) ? true : false;
    
    // Validate year
    if ($year < 2024 || $year > 2050) {
        http_response_code(400);
        echo "Invalid year. Please enter a year between 2024 and 2050.";
        exit;
    }
    
    // Check if season already exists
    $stmt = $conn->prepare("SELECT id FROM seasons WHERE year = ?");
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(400);
        echo "Season for year $year already exists.";
        $stmt->close();
        exit;
    }
    $stmt->close();
    
    // Start transaction
    $conn->autocommit(false);
    
    try {
        // If setting as active, deactivate all other seasons first
        if ($setAsActive) {
            $stmt = $conn->prepare("UPDATE seasons SET is_active = 0");
            $stmt->execute();
            $stmt->close();
        }
        
        // Insert new season
        $stmt = $conn->prepare("INSERT INTO seasons (year, is_active) VALUES (?, ?)");
        $isActiveInt = $setAsActive ? 1 : 0;
        $stmt->bind_param("ii", $year, $isActiveInt);
        
        if ($stmt->execute()) {
            $conn->commit();
            $statusText = $setAsActive ? " and set as active" : "";
            echo "Season $year created successfully$statusText!";
        } else {
            throw new Exception("Failed to create season: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error creating season: " . $e->getMessage();
    error_log("addseason.php error: " . $e->getMessage());
}

$conn->close();
?>
