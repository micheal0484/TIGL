<?php
$servername = "localhost";
$username = "frczbzmy_mike";
$password = "A!D3Nc@m3r0n"; 
$dbname = "frczbzmy_tigl_points";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>