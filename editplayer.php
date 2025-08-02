<?php
require_once './config.php';

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$pin = isset($_POST['pin']) ? trim($_POST['pin']) : '';
$handicap = isset($_POST['handicap']) ? floatval($_POST['handicap']) : 0;
$isAdmin = isset($_POST['isAdmin']) ? 1 : 0;

if (empty($username) || empty($pin)) {
    http_response_code(400);
    echo "Username and PIN are required";
    exit();
}

if (strlen($pin) !== 4 || !ctype_digit($pin)) {
    http_response_code(400);
    echo "PIN must be 4 digits";
    exit();
}

// Check if PIN already exists for a different user
$stmt = $conn->prepare("SELECT username FROM users WHERE pin = ? AND username != ?");
$stmt->bind_param("ss", $pin, $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    echo "PIN already exists for another user";
    $stmt->close();
    exit();
}

// Update user
$stmt = $conn->prepare("UPDATE users SET pin = ?, handicap = ?, isAdmin = ? WHERE username = ?");
$stmt->bind_param("sdis", $pin, $handicap, $isAdmin, $username);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "Player updated successfully";
    } else {
        echo "No changes made or player not found";
    }
} else {
    http_response_code(500);
    echo "Error updating player: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>