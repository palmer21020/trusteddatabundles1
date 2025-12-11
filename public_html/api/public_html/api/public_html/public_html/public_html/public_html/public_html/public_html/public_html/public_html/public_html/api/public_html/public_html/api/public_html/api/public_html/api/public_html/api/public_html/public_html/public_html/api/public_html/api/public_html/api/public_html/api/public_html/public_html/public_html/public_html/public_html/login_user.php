<?php
// login_user.php
session_start();

header("Content-Type: application/json");

// Database connection
$db = new SQLite3(__DIR__ . "/database.db");

// Read JSON
$data = json_decode(file_get_contents("php://input"), true);

$email    = trim($data["email"]);
$password = trim($data["password"]);

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Email and password required"]);
    exit;
}

// Fetch user
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
$stmt->bindValue(":email", $email);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$result) {
    echo json_encode(["status" => "error", "message" => "Invalid email or password"]);
    exit;
}

// Verify password
if (!password_verify($password, $result["password"])) {
    echo json_encode(["status" => "error", "message" => "Incorrect password"]);
    exit;
}

// Save user session
$_SESSION["user_id"] = $result["id"];
$_SESSION["fullname"] = $result["fullname"];
$_SESSION["email"] = $result["email"];
$_SESSION["phone"] = $result["phone"];

echo json_encode([
    "status" => "success",
    "message" => "Login successful",
    "user" => [
        "id" => $result["id"],
        "fullname" => $result["fullname"],
        "email" => $result["email"],
        "phone" => $result["phone"]
    ]
]);
?>
