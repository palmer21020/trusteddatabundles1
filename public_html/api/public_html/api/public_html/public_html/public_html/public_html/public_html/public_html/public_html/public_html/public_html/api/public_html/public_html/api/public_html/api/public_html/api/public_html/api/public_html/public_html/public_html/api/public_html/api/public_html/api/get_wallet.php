<?php
// get_wallet.php — Returns wallet balance for the logged-in user

header('Content-Type: application/json');
session_start();

$cfg = [
    'db_host' => 'localhost',
    'db_name' => 'trusted_bundles',
    'db_user' => 'db_user',
    'db_pass' => 'db_pass'
];

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

// DB connect
$mysqli = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name']);
if ($mysqli->connect_errno) {
    echo json_encode(['error' => 'db_failed']);
    exit;
}

// Fetch wallet
$stmt = $mysqli->prepare("SELECT wallet FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['error' => 'user_not_found']);
    exit;
}

echo json_encode([
    'ok' => true,
    'wallet' => floatval($user['wallet'])
]);

$mysqli->close();
?>
