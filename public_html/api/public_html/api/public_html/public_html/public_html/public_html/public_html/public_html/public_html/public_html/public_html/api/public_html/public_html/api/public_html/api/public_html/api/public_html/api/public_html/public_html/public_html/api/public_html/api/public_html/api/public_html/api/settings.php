<?php
// settings.php — Returns general website + business settings

header("Content-Type: application/json");

echo json_encode([
    "ok" => true,
    "business_name" => "Trusted Data Bundles",
    "contact_number" => "0541896641",
    "currency" => "GHS",
    "min_topup" => 10,
    "working_hours" => [
        "days" => "Monday to Saturday",
        "time" => "8:00 AM to 8:00 PM"
    ],
    "networks_supported" => [
        "MTN",
        "Telecel",
        "AirtelTigo",
        "AirtelTigo iShare",
        "AT Big Time",
        "Glo"
    ]
]);
?>
