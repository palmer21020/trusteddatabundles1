<?php
// register_user.php
session_start();

header("Content-Type: application/json");

// Database connection (SQLite for simplicity + free hosting compatibility)
$db = new SQLite3(__DIR__ . "/database.db");

// Create table if not exists
$db->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        fullname TEXT,
        email TEXT UNIQUE,
        phone TEXT,
        password TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

// Read JSON
$data = json_decode(file_get_contents("php://input"), true);

$fullname = trim($data["fullname"]);
$email    = trim($data["email"]);
$phone    = trim($data["phone"]);
$password = trim($data["password"]);

if (!$fullname || !$email || !$phone || !$password) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $db->prepare("INSERT INTO users (fullname, email, phone, password) VALUES (:fullname, :email, :phone, :password)");
$stmt->bindValue(":fullname", $fullname);
$stmt->bindValue(":email", $email);
$stmt->bindValue(":phone", $phone);
$stmt->bindValue(":password", $hashed);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Account created successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Email already exists"]);
}
?>
