<?php
require_once './config.php';

$pin = isset($_POST['pin']) ? trim($_POST['pin']) : '';

if (strlen($pin) !== 4 || !ctype_digit($pin)) {
    echo "Invalid PIN format";
    exit();
}

$stmt = $conn->prepare("SELECT username, isAdmin FROM users WHERE pin = ?");
$stmt->bind_param("s", $pin);

if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "VALID:" . $user['username'] . ":" . $user['isAdmin'];
    } else {
        echo "Invalid PIN";
        exit(); 
    }
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>