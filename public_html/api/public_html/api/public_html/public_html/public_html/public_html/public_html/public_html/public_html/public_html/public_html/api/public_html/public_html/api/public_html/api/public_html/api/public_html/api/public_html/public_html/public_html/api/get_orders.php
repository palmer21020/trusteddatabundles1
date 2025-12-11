<?php
// get_orders.php – Fetch all orders for dashboard

header('Content-Type: application/json');
session_start();

$cfg = [
    'db_host' => 'localhost',
    'db_name' => 'trusted_bundles',
    'db_user' => 'db_user',
    'db_pass' => 'db_pass'
];

$mysqli = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name']);
if ($mysqli->connect_errno) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

$query = "SELECT reference, phone, network, size_mb, price, status, created_at 
          FROM orders ORDER BY created_at DESC LIMIT 300";

$result = $mysqli->query($query);
$orders = [];

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode([
    'ok' => true,
    'orders' => $orders
]);

$mysqli->close();
?>
