<?php
session_start();

// Redirect user to login page if not logged in
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.html");
        exit();
    }
}

// Log user out
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.html");
    exit();
}
?>
