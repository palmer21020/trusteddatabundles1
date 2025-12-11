<?php
// user_dashboard.php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.html");
    exit;
}

// USER DATA (fetched from login)
$userName = $_SESSION['user_name'];
$userPhone = $_SESSION['user_phone'];
$wallet = $_SESSION['wallet']; // fetched from DB during login

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>User Dashboard – Trusted Data Bundles</title>
<link rel="stylesheet" href="/styles/dashboard.css" />
</head>
<body>

<div class="sidebar">
    <h2>Trusted Data</h2>
    <ul>
        <li><a href="user_dashboard.php">Dashboard</a></li>
        <li><a href="buy_bundle.php">Buy Bundle</a></li>
        <li><a href="wallet_topup.php">Top-up Wallet</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="support.php">Support</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main">
    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($userName); ?> 👋</h1>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Wallet Balance</h3>
            <p>GHS <?php echo number_format($wallet, 2); ?></p>
        </div>

        <div class="card">
            <h3>Working Hours</h3>
            <p>Mon – Sat</p>
            <p>8:00am – 8:00pm</p>
        </div>

        <div class="card">
            <h3>Customer Support</h3>
            <p>📞 0541896641</p>
        </div>
    </div>

    <h2>Recent Orders</h2>
    <iframe src="orders_list.php" style="border:none;width:100%;height:300px;"></iframe>
</div>

</body>
</html>
