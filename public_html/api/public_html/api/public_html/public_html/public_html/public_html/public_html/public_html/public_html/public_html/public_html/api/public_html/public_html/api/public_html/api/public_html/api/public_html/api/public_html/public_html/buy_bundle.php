<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$apiKey = "28725d538f64293bcaf1c2f4dbc15767";
$endpoint = "https://spfastit.com/wp-json/custom-api/v1/place-order";

// Working hours check
date_default_timezone_set("Africa/Accra");
$day = date("N"); // 1=Mon, 7=Sun
$time = date("H:i");

if ($day > 6 || $time < "08:00" || $time > "20:00") {
    die("<h2 style='color:red;text-align:center;margin-top:40px;'>Service available Monday–Saturday, 8:00 AM – 8:00 PM</h2>");
}

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = trim($_POST["phone"]);
    $network = trim($_POST["network"]);
    $size_mb = intval($_POST["size"]);
    $reference = "TDB-" . time() . "-" . rand(1000,9999);

    $payload = [
        "api_key" => $apiKey,
        "phone" => $phone,
        "network" => $network,
        "size_mb" => $size_mb,
        "reference" => $reference,
        "webhook_url" => "https://yourdomain.com/api/webhook.php"
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result["status"]) && $result["status"] === "success") {
        header("Location: order_success.php?ref=$reference");
        exit;
    } else {
        header("Location: order_failed.php?ref=$reference");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buy Data Bundle - Trusted Data Bundles</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>

<div class="container">
    <h2>Buy Data Bundle</h2>

    <form method="POST">
        <label>Phone Number</label>
        <input type="text" name="phone" required>

        <label>Network</label>
        <select name="network" required>
            <option value="mtn">MTN</option>
            <option value="airteltigo">AirtelTigo</option>
            <option value="telecel">Telecel</option>
            <option value="airteltigo_ishare">AirtelTigo iShare</option>
            <option value="airteltigo_bigtime">AirtelTigo BigTime</option>
        </select>

        <label>Bundle Size (MB)</label>
        <input type="number" name="size" required>

        <button type="submit" class="btn">Buy Bundle</button>
    </form>
</div>

</body>
</html>
