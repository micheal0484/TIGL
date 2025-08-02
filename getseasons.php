<?php
require_once './config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT id, year, is_active FROM seasons ORDER BY year DESC");
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $seasons = [];
        
        while ($row = $result->fetch_assoc()) {
            $seasons[] = $row;
        }
        
        echo json_encode($seasons);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>