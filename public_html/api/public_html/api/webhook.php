<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Location to store logs
$WEBHOOK_LOG = __DIR__ . "/webhook_logs.txt";

// Read incoming data
$input = json_decode(file_get_contents("php://input"), true);

// If no JSON received
if(!$input){
    file_put_contents($WEBHOOK_LOG, date("Y-m-d H:i:s") . " - INVALID WEBHOOK RECEIVED\n", FILE_APPEND);
    echo json_encode(["status" => "error", "message" => "Invalid JSON received"]);
    exit;
}

// Log webhook
file_put_contents(
    $WEBHOOK_LOG,
    date("Y-m-d H:i:s") . " - WEBHOOK: " . json_encode($input) . "\n",
    FILE_APPEND
);

// Always respond OK so SPFastIT knows we received it
echo json_encode(["status" => "success", "message" => "Webhook received"]);
exit;
