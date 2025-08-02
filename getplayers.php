<?php
// filepath: c:\Users\Mike\source\repos\TIGL\getplayers.php
require_once './config.php';

// Add cache-busting headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$format = isset($_GET['format']) ? $_GET['format'] : 'html';

// Get all players
$stmt = $conn->prepare("SELECT username, pin, handicap, isAdmin FROM users ORDER BY username");

if ($stmt->execute()) {
    $result = $stmt->get_result();
    
    if ($format === 'json') {
        $players = [];
        while ($row = $result->fetch_assoc()) {
            $players[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($players);
    } else {
        if ($result->num_rows > 0) {
            echo '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
            echo '<tr style="background-color: #f2f2f2;">
                    <th style="border: 1px solid #ddd; padding: 8px;">Username</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">PIN</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Handicap</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Type</th>
                  </tr>';
            
            while ($row = $result->fetch_assoc()) {
                $userType = $row['isAdmin'] ? 'Admin' : 'Player';
                echo '<tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($row['username']) . '</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($row['pin']) . '</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($row['handicap']) . '</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">' . $userType . '</td>
                      </tr>';
            }
            echo '</table>';
        } else {
            echo '<p>No players found.</p>';
        }
    }
} else {
    if ($format === 'json') {
        header('Content-Type: application/json');
        echo json_encode(['error' => $stmt->error]);
    } else {
        http_response_code(500);
        echo "Error retrieving players: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>