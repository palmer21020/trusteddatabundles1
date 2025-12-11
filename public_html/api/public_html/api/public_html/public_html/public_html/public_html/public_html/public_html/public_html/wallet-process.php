<?php
/* ============================================================
   Trusted Data Bundles – MTN MoMo Payment Processing
   ------------------------------------------------------------
   This script:
   ✔ Receives amount from wallet-topup.html
   ✔ Creates MTN MoMo payment request
   ✔ Redirects user to approve payment
   ------------------------------------------------------------
   Requirements:
   - PHP 7+
   - cURL enabled
   ============================================================ */

$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;

if ($amount < 10) {
    die("Invalid amount");
}

/* =========================
   YOUR CREDENTIALS
   ========================= */
$consumer_key    = "U8HfXXXXXXXXXXXXXXXXXXXXXXXXfK0T";   // YOUR CONSUMER KEY
$consumer_secret = "QOFnXXXXXXXXAh4e";                   // YOUR CONSUMER SECRET
$callback_url    = "https://yourdomain.com/wallet-callback.php";  // Change to your actual domain

/* =========================
   STEP 1 – Generate Token
   ========================= */
$token_url = "https://api.mtn.com/v1/oauth/access_token";

$auth_header = base64_encode("$consumer_key:$consumer_secret");

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic $auth_header",
    "Content-Type: application/x-www-form-urlencoded"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    die("Token error: " . curl_error($ch));
}

$token_data = json_decode($response, true);
curl_close($ch);

if (!isset($token_data["access_token"])) {
    die("Could not get access token");
}

$access_token = $token_data["access_token"];

/* =========================
   STEP 2 – Create MoMo Payment Request
   ========================= */

$payment_url = "https://api.mtn.com/collection/v1_0/requesttopay";

$reference = "TDB_" . time();  // Unique reference

$payload = [
    "amount" => (string)$amount,
    "currency" => "GHS",
    "externalId" => $reference,
    "payer" => [
        "partyIdType" => "MSISDN",
        "partyId" => "0541896641"  // Customer will approve prompt on this phone
    ],
    "payerMessage" => "Wallet Top-up",
    "payeeNote" => "Trusted Data Bundles",
    "callbackUrl" => $callback_url
];

$ch = curl_init($payment_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "X-Reference-Id: $reference",
    "X-Target-Environment: production",
    "Content-Type: application/json",
    "Ocp-Apim-Subscription-Key: $consumer_key"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

/* =========================
   STEP 3 – Redirect User
   ========================= */

echo "
<!DOCTYPE html>
<html>
<head>
<title>Processing Payment</title>
<style>
body { font-family: Arial; background: #f8f9fa; }
.box {
    max-width: 500px; background: white; padding: 20px;
    margin: 80px auto; border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    text-align: center;
}
</style>
</head>
<body>
<div class='box'>
    <h2>Approve Your Payment</h2>
    <p>A MoMo prompt has been sent to <b>0541896641</b>.</p>
    <p>Please approve it to complete your wallet top-up.</p>
    <p><b>Amount:</b> GHC $amount</p>
</div>
</body>
</html>
";
?>
