<?php
header("Content-Type: application/json");
require_once "db.php";

// Get POST data
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

// Validate minimum top-up
if ($amount < 10) {
    echo json_encode([
        "ok" => false,
        "error" => "Minimum top-up amount is 10 GHC"
    ]);
    exit;
}

// Update wallet
$conn->begin_transaction();

try {
    // 1. Update wallet balance
    $stmt = $conn->prepare("UPDATE wallet SET balance = balance + ? WHERE id = 1");
    $stmt->bind_param("d", $amount);
    $stmt->execute();

    // 2. Log transaction
    $stmt = $conn->prepare("
        INSERT INTO wallet_transactions(amount, type, status, created_at) 
        VALUES (?, 'topup', 'success', NOW())
    ");
    $stmt->bind_param("d", $amount);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        "ok" => true,
        "message" => "Wallet topped up successfully",
        "amount" => $amount
    ]);

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "ok" => false,
        "error" => "Top-up failed",
        "details" => $e->getMessage()
    ]);
}
?>
