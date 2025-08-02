<?php
session_start();

if ($_POST) {
    // Simulate login
    $_SESSION['user_id'] = 123;
    $_SESSION['username'] = 'testuser';
    $_SESSION['isAdmin'] = 0;
    echo "Session data set. <a href='test_login.php'>Check session</a>";
} else {
    // Check session
    echo "<h3>Session Check</h3>";
    echo "Session ID: " . session_id() . "<br>";
    echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "<br>";
    echo "Username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'NOT SET') . "<br>";
    echo "Is Admin: " . (isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : 'NOT SET') . "<br>";
    echo "<br>All session data:<br>";
    print_r($_SESSION);
    
    echo "<br><br><form method='post'><button type='submit'>Set Test Session</button></form>";
}
?>