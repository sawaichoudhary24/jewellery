<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

$totalProducts = 0;
$totalUsers = 0;
$totalOrders = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM products");

if ($result) {
    $totalProducts = $result->fetch_assoc()["total"];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM users");

if ($result) {
    $totalUsers = $result->fetch_assoc()["total"];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM orders");

if ($result) {
    $totalOrders = $result->fetch_assoc()["total"];
}
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .sidebar {
            position: fixed;
            width: 240px;
            height: 100vh;
            background: #181818;
            color: white;
            padding: 25px 15px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background: #b8860b;
        }

        .main {
            margin-left: 240px;
            padding: 35px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .cards {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(200px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        .card h3 {
            margin: 0 0 10px;
            color: #666;
        }

        .number {
            font-size: 32px;
            font-weight: bold;
            color: #b8860b;
        }

        @media(max-width:768px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main {
                margin-left: 0;
            }

            .cards {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<aside class="sidebar">

    <h2>Jewellery Admin</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="add-product.php">Add Product</a>
    <a href="categories.php">Categories</a>
    <a href="orders.php">Orders</a>
    <a href="users.php">Users</a>
    <a href="messages.php">Messages</a>
    <a href="logout.php">Logout</a>

</aside>

<main class="main">

    <div class="topbar">

        <h1>Dashboard</h1>

        <span>
            Welcome,
            <?= htmlspecialchars($_SESSION["admin_name"]) ?>
        </span>

    </div>

    <div class="cards">

        <div class="card">
            <h3>Total Products</h3>
            <div class="number">
                <?= $totalProducts ?>
            </div>
        </div>

        <div class="card">
            <h3>Total Users</h3>
            <div class="number">
                <?= $totalUsers ?>
            </div>
        </div>

        <div class="card">
            <h3>Total Orders</h3>
            <div class="number">
                <?= $totalOrders ?>
            </div>
        </div>

    </div>

</main>

</body>
</html>