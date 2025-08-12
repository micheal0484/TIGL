<?php
require_once './config.php';

$originalUsername = isset($_POST['originalUsername']) ? trim($_POST['originalUsername']) : '';
$newUsername = isset($_POST['username']) ? trim($_POST['username']) : '';
$pin = isset($_POST['pin']) ? trim($_POST['pin']) : '';
$handicap = isset($_POST['handicap']) ? floatval($_POST['handicap']) : 0;
$isAdmin = isset($_POST['isAdmin']) ? 1 : 0;

if (empty($originalUsername) || empty($newUsername) || empty($pin)) {
    http_response_code(400);
    echo "Original username, new username, and PIN are required";
    exit();
}

if (strlen($pin) !== 4 || !ctype_digit($pin)) {
    http_response_code(400);
    echo "PIN must be 4 digits";
    exit();
}

// Validate username format (optional - adjust as needed)
if (strlen($newUsername) < 3 || strlen($newUsername) > 50) {
    http_response_code(400);
    echo "Username must be between 3 and 50 characters";
    exit();
}

// Check if the original user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $originalUsername);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(400);
    echo "Original player not found";
    $stmt->close();
    exit();
}
$userId = $result->fetch_assoc()['id'];
$stmt->close();

// If username is changing, check if new username already exists
if ($originalUsername !== $newUsername) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $newUsername);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo "New username already exists";
        $stmt->close();
        exit();
    }
    $stmt->close();
}

// Check if PIN already exists for a different user
$stmt = $conn->prepare("SELECT username FROM users WHERE pin = ? AND username != ?");
$stmt->bind_param("ss", $pin, $originalUsername);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    echo "PIN already exists for another user";
    $stmt->close();
    exit();
}
$stmt->close();

// Start transaction for data integrity
$conn->begin_transaction();

try {
    // Update user information
    $stmt = $conn->prepare("UPDATE users SET username = ?, pin = ?, handicap = ?, isAdmin = ? WHERE username = ?");
    $stmt->bind_param("ssdis", $newUsername, $pin, $handicap, $isAdmin, $originalUsername);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating player: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No changes made or player not found");
    }
    
    $stmt->close();
    
    // If username changed, update any related session data or other references as needed
    // Note: You might need to update other tables that reference the username
    // For example, if you have any logs or other tables that store username
    
    // Commit the transaction
    $conn->commit();
    
    if ($originalUsername !== $newUsername) {
        echo "Player updated successfully. Username changed from '$originalUsername' to '$newUsername'";
    } else {
        echo "Player updated successfully";
    }
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    http_response_code(500);
    echo $e->getMessage();
}

$conn->close();
?>