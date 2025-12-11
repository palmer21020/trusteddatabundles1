<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// ===========================
// CONFIG
// ===========================
$API_KEY = "28725d538f64293bcaf1c2f4dbc15767";
$LOG_FILE = __DIR__ . "/logs.txt";

// Save log
function log_event($msg){
    global $LOG_FILE;
    file_put_contents($LOG_FILE, date("Y-m-d H:i:s") . " - " . $msg . "\n", FILE_APPEND);
}

// Read JSON body
$input = json_decode(file_get_contents("php://input"), true);

if(!$input){
    echo json_encode(["status"=>"error", "message"=>"Invalid JSON"]);
    exit;
}

// Validate API key
if(!isset($input["api_key"]) || $input["api_key"] !== $GLOBALS["API_KEY"]){
    log_event("Unauthorized API access attempt");
    echo json_encode(["status"=>"error", "message"=>"Invalid API Key"]);
    exit;
}

// Routing
$action = $input["action"] ?? null;

// =======================================================
// 📌 1. PLACE ORDER
// =======================================================
if($action === "place_order"){

    $payload = [
        "api_key" => $API_KEY,
        "phone" => $input["phone"],
        "size_mb" => $input["size_mb"],
        "network" => $input["network"],
        "reference" => $input["reference"],
        "webhook_url" => "https://yourdomain.com/api/webhook"
    ];

    // Send to SPFastIT API
    $ch = curl_init("https://spfastit.com/wp-json/custom-api/v1/place-order");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    curl_close($ch);

    log_event("Order Placed: " . json_encode($payload));

    echo $response;
    exit;
}

// =======================================================
// 📌 2. WALLET TOP-UP (MoMo - Placeholder)
// =======================================================
if($action === "topup_wallet"){
    if($input["amount"] < 10){
        echo json_encode(["status"=>"error", "message"=>"Minimum top-up is 10 GHC"]);
        exit;
    }

    log_event("Wallet top-up initiated: ".$input["phone"]." - GHC ".$input["amount"]);

    echo json_encode([
        "status" => "success",
        "message" => "Top-up request received. Processing…"
    ]);
    exit;
}

// =======================================================
// 📌 3. ADMIN READ LOGS
// =======================================================
if($action === "read_logs"){
    echo json_encode([
        "status" => "success",
        "logs" => file_exists($LOG_FILE) ? file_get_contents($LOG_FILE) : "No logs yet."
    ]);
    exit;
}

echo json_encode(["status"=>"error","message"=>"Invalid action"]);
