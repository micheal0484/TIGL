<?php
require_once './config.php';

// Check if user is admin (you should implement proper session checking here)
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

// Check if PIN already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE pin = ?");
$stmt->bind_param("s", $pin);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    echo "PIN already exists";
    $stmt->close();
    exit();
}

// Add new user with handicap
$stmt = $conn->prepare("INSERT INTO users (username, pin, handicap, isAdmin) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssdi", $username, $pin, $handicap, $isAdmin);

if ($stmt->execute()) {
    echo "Player added successfully";
} else {
    http_response_code(500);
    echo "Error adding player: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>