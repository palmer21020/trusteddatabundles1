<?php
// logout.php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect user to login page or homepage
header("Location: /login.html");
exit;
?>
