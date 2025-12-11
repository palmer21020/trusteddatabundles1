<?php
/* ============================================================
   Trusted Data Bundles – MTN MoMo Callback
   ------------------------------------------------------------
   This file receives payment confirmation from MTN.
   It verifies the transaction and updates the wallet.
   ------------------------------------------------------------ */

header("Content-Type: application/json");

// ============================================================
// Load DB Connection
// ============================================================

$host = "localhost";
$user = "db_user";      // CHANGE THIS
$pass = "db_pass";      // CHANGE THIS
$db   = "trusted_bundles";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB error: " . $conn->connect_error);
}

// ============================================================
// Read incoming MoMo JSON
// ============================================================

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

file_put_contents(__DIR__ . "/momo_callback_log.txt",
    date("c") . " CALLBACK: " . $raw . "\n",
    FILE_APPEND
);

if (!$data || !isset($data["externalId"])) {
    echo json_encode(["error" => "Invalid callback"]);
    exit;
}

$reference = $data["externalId"];
$status    = strtolower($data["status"] ?? "failed");
$amount    = floatval($data["amount"] ?? 0);

// ============================================================
// Validate payment status
// ============================================================

if ($status !== "successful") {
    // mark failed transaction
    $conn->query("UPDATE wallet_topups SET status='failed' WHERE reference='$reference'");
    echo json_encode(["ok" => false, "message" => "Payment failed"]);
    exit;
}

// ============================================================
// Get the user associated with this reference
// ============================================================

$q = $conn->query("SELECT user_id FROM wallet_topups WHERE reference='$reference' LIMIT 1");

if ($q->num_rows !== 1) {
    echo json_encode(["error" => "Reference not found"]);
    exit;
}

$row = $q->fetch_assoc();
$user_id = intval($row["user_id"]);

// ============================================================
// CREDIT USER WALLET
// ============================================================

// Update wallet balance
$conn->query("UPDATE users SET wallet = wallet + $amount WHERE id = $user_id");

// Mark transaction as completed
$conn->query("UPDATE wallet_topups SET status='completed' WHERE reference='$reference'");

// ============================================================
// Show success page
// ============================================================

?>
<!DOCTYPE html>
<html>
<head>
<title>Payment Successful</title>
<style>
body { font-family: Arial; background: #f3f6fa; }
.box {
    max-width: 450px; margin: 60px auto; padding: 25px;
    background: #fff; border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center;
}
h2 { color: #28a745; }
p { font-size: 16px; }
</style>
</head>
<body>
<div class="box">
    <h2>Payment Successful 🎉</h2>
    <p>Your wallet has been topped up successfully.</p>
    <p><b>GHC <?php echo number_format($amount, 2); ?></b> added.</p>
    <p>Reference: <?php echo $reference; ?></p>

    <a href="/dashboard.html"
       style="display:inline-block;margin-top:20px;padding:10px 18px;background:#28a745;color:white;text-decoration:none;border-radius:8px;">
       Go to Dashboard
    </a>
</div>
</body>
</html>
