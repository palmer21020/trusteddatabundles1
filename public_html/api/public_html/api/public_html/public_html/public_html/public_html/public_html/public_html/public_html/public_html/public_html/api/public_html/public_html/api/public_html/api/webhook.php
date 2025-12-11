<?php
header("Content-Type: application/json");

// Load DB
require_once "db.php";

// Read webhook payload
$payload = json_decode(file_get_contents("php://input"), true);

// Validate payload
if (!isset($payload['Data']['ClientReference'])) {
    echo json_encode(["ok" => false, "error" => "Invalid webhook"]);
    exit;
}

$reference = $payload['Data']['ClientReference'];
$status = $payload['Data']['Status'];
$amount = floatval($payload['Data']['Amount']);

// Find transaction
$stmt = $conn->prepare("SELECT id, status FROM topups WHERE reference = ?");
$stmt->bind_param("s", $reference);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["ok" => false, "error" => "Reference not found"]);
    exit;
}

$data = $res->fetch_assoc();

if ($data['status'] == "success") {
    echo json_encode(["ok" => true, "message" => "Already processed"]);
    exit;
}

// If payment was successful
if (strtolower($status) == "success") {

    // Update topup record
    $update = $conn->prepare("UPDATE topups SET status='success' WHERE reference=?");
    $update->bind_param("s", $reference);
    $update->execute();

    // Credit user wallet (default wallet for now)
    // You can later connect it to login system
    $credit = $conn->prepare("UPDATE wallet SET balance = balance + ? WHERE id = 1");
    $credit->bind_param("d", $amount);
    $credit->execute();

    echo json_encode(["ok" => true, "message" => "Wallet credited"]);
    exit;
}

// If failed
$fail = $conn->prepare("UPDATE topups SET status='failed' WHERE reference=?");
$fail->bind_param("s", $reference);
$fail->execute();

echo json_encode(["ok" => true, "message" => "Payment marked as failed"]);
?>
