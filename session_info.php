<?php
echo "<h3>PHP Session Configuration</h3>";
echo "Session save path: " . session_save_path() . "<br>";
echo "Session name: " . session_name() . "<br>";
echo "Session cookie params: ";
print_r(session_get_cookie_params());
echo "<br>";
echo "Session module name: " . session_module_name() . "<br>";
echo "Session cache limiter: " . session_cache_limiter() . "<br>";

// Test if we can write to session save path
$save_path = session_save_path();
if (empty($save_path)) {
    $save_path = sys_get_temp_dir();
}
echo "<br>Session save path writable: " . (is_writable($save_path) ? "YES" : "NO") . "<br>";
echo "Session save path: " . $save_path . "<br>";

session_start();
echo "Current session ID: " . session_id() . "<br>";
?>