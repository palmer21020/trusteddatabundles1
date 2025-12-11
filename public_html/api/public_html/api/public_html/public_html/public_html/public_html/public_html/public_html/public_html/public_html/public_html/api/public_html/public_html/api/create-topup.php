<?php
header("Content-Type: application/json");

// Load database connection
require_once "db.php";

// Read POST body
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['amount'])) {
    echo json_encode(["ok" => false, "error" => "Amount required"]);
    exit;
}

$amount = floatval($data['amount']);

if ($amount < 10) {
    echo json_encode(["ok" => false, "error" => "Minimum top-up is 10 GHC"]);
    exit;
}

// Generate transaction ID
$reference = "TDB" . time() . rand(1000, 9999);

// Example Hubtel/MoMo config
$clientId = "YOUR_CLIENT_ID";
$secret = "YOUR_SECRET_KEY";
$callback = "https://yourdomain.com/api/webhook.php";

// Create payment request
$payload = [
    "amount" => $amount,
    "title" => "Wallet Top-Up",
    "description" => "Trusted Data Bundles Wallet Funding",
    "callbackUrl" => $callback,
    "returnUrl" => "https://yourdomain.com/wallet.html",
    "cancellationUrl" => "https://yourdomain.com/wallet.html",
    "merchantAccountNumber" => "MERCHANT_NUMBER",
    "clientReference" => $reference
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://payproxyapi.hubtel.com/items/initiate");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . base64_encode("$clientId:$secret"),
    "Content-Type: application/json"
]);

$response = curl_exec($ch);

if (!$response) {
    echo json_encode(["ok" => false, "error" => "Hubtel error"]);
    exit;
}

$result = json_decode($response, true);

// Check if Hubtel returned a payment page URL
if (!isset($result['data']['checkoutUrl'])) {
    echo json_encode(["ok" => false, "error" => "Payment link failed"]);
    exit;
}

// Save pending transaction in DB
$stmt = $conn->prepare("INSERT INTO topups (reference, amount, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("sd", $reference, $amount);
$stmt->execute();

echo json_encode([
    "ok" => true,
    "payment_url" => $result['data']['checkoutUrl']
]);
?>
