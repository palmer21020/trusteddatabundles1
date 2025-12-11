<?php
// create_order.php — Sends bundle order to SPFastIT API + logs in DB

header('Content-Type: application/json');
session_start();

$cfg = [
    'db_host' => 'localhost',
    'db_name' => 'trusted_bundles',
    'db_user' => 'db_user',
    'db_pass' => 'db_pass',

    // Your API key (from SPFastIT)
    'sp_api_key' => '28725d538f64293bcaf1c2f4dbc15767',
    'sp_endpoint' => 'https://spfastit.com/wp-json/custom-api/v1/place-order',

    // Logging folder
    'log_dir' => __DIR__ . '/logs'
];

if (!file_exists($cfg['log_dir'])) {
    mkdir($cfg['log_dir'], 0777, true);
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Fields
$phone     = trim($data['phone'] ?? '');
$network   = strtolower(trim($data['network'] ?? ''));
$size_mb   = intval($data['size_mb'] ?? 0);
$reference = $data['reference'] ?? ('ref-' . time());

if (!$phone || !$network || !$size_mb) {
    echo json_encode(['error' => 'missing_fields']);
    exit;
}

// DB connect
$mysqli = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name']);
if ($mysqli->connect_errno) {
    echo json_encode(['error' => 'db_failed']);
    exit;
}

// Payload to API
$payload = [
    "api_key" => $cfg['sp_api_key'],
    "phone" => $phone,
    "size_mb" => $size_mb,
    "network" => $network,
    "reference" => $reference,
    "webhook_url" => "" // optional
];

// CURL request
$ch = curl_init($cfg['sp_endpoint']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$error = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Log all requests
$logFile = $cfg['log_dir'] . "/orders_" . date("Y-m-d") . ".log";
file_put_contents($logFile,
    date('c') . " REQUEST: " . json_encode($payload) .
    " RESPONSE: " . $response . PHP_EOL,
    FILE_APPEND
);

// Error check
if ($error) {
    echo json_encode(['error' => 'curl_failed', 'detail' => $error]);
    exit;
}

$decoded = json_decode($response, true);
$status = $decoded['status'] ?? 'pending';

// Save in orders table
$stmt = $mysqli->prepare("
    INSERT INTO orders (reference, phone, network, size_mb, price, status, meta, created_at)
    VALUES (?, ?, ?, ?, 0, ?, ?, NOW())
");

$meta = json_encode($decoded);
$stmt->bind_param("sssis", $reference, $phone, $network, $size_mb, $status);
$stmt->execute();
$stmt->close();

echo json_encode([
    'ok' => true,
    'api_response' => $decoded
]);

$mysqli->close();
?>
