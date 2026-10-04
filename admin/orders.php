<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

$message = "";
$error = "";

/* UPDATE ORDER STATUS */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $order_id = (int) $_POST["order_id"];
    $order_status = $_POST["order_status"];
    $payment_status = $_POST["payment_status"];

    $allowedOrderStatus = [
        "pending",
        "confirmed",
        "processing",
        "shipped",
        "delivered",
        "cancelled"
    ];

    $allowedPaymentStatus = [
        "pending",
        "paid",
        "failed"
    ];

    if (
        in_array($order_status, $allowedOrderStatus, true) &&
        in_array($payment_status, $allowedPaymentStatus, true)
    ) {

        $stmt = $conn->prepare("
            UPDATE orders
            SET order_status = ?, payment_status = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssi",
            $order_status,
            $payment_status,
            $order_id
        );

        if ($stmt->execute()) {
            $message = "Order updated successfully.";
        } else {
            $error = "Unable to update order.";
        }

    } else {
        $error = "Invalid order status.";
    }
}

/* DELETE ORDER */
if (isset($_GET["delete"])) {

    $order_id = (int) $_GET["delete"];

    $stmt = $conn->prepare("
        DELETE FROM orders
        WHERE id = ?
    ");

    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        header("Location: orders.php?deleted=1");
        exit;
    }
}

/* DELETE MESSAGE */
if (isset($_GET["deleted"])) {
    $message = "Order deleted successfully.";
}

/* GET ORDERS */
$orders = $conn->query("
    SELECT
        o.*,
        u.name AS user_name
    FROM orders o
    LEFT JOIN users u
        ON o.user_id = u.id
    ORDER BY o.id DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Orders - Jewellery Admin</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f6f6f6;
    color: #222;
}

/* SIDEBAR */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
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

/* MAIN */

.main {
    margin-left: 240px;
    padding: 30px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-header h1 {
    margin: 0;
}

/* MESSAGE */

.success {
    background: #e8f7e8;
    color: #197019;
    padding: 14px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.error {
    background: #ffe6e6;
    color: #b00020;
    padding: 14px;
    border-radius: 6px;
    margin-bottom: 20px;
}

/* STATS */

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    padding: 22px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.stat-card h3 {
    margin: 0 0 10px;
    font-size: 14px;
    color: #777;
}

.stat-card strong {
    font-size: 28px;
}

/* ORDER TABLE */

.table-box {
    background: white;
    padding: 20px;
    border-radius: 10px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1100px;
}

th,
td {
    padding: 13px;
    border-bottom: 1px solid #eee;
    text-align: left;
    vertical-align: top;
}

th {
    background: #181818;
    color: white;
}

tr:hover td {
    background: #fafafa;
}

/* STATUS */

.status {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: capitalize;
}

.pending {
    background: #fff3cd;
    color: #856404;
}

.confirmed {
    background: #dbeafe;
    color: #1d4ed8;
}

.processing {
    background: #e0e7ff;
    color: #4338ca;
}

.shipped {
    background: #d1fae5;
    color: #047857;
}

.delivered {
    background: #dcfce7;
    color: #166534;
}

.cancelled {
    background: #fee2e2;
    color: #b91c1c;
}

.paid {
    background: #dcfce7;
    color: #166534;
}

.failed {
    background: #fee2e2;
    color: #b91c1c;
}

/* FORM */

.status-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 150px;
}

.status-form select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.update-btn {
    border: none;
    background: #b8860b;
    color: white;
    padding: 8px 10px;
    border-radius: 5px;
    cursor: pointer;
}

.delete-btn {
    display: inline-block;
    margin-top: 8px;
    background: #b00020;
    color: white;
    padding: 7px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 13px;
}

/* MOBILE */

@media(max-width: 1000px) {

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media(max-width: 700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 15px;
    }

    .stats {
        grid-template-columns: 1fr;
    }

    .page-header {
        display: block;
    }

}

</style>

</head>

<body>

<!-- SIDEBAR -->

<aside class="sidebar">

<h2>Jewellery Admin</h2>

<a href="dashboard.php">
Dashboard
</a>

<a href="products.php">
Products
</a>

<a href="add-product.php">
Add Product
</a>

<a href="categories.php">
Categories
</a>

<a href="orders.php">
Orders
</a>

<a href="users.php">
Users
</a>

<a href="messages.php">
Messages
</a>

<a href="logout.php">
Logout
</a>

</aside>

<!-- MAIN -->

<main class="main">

<div class="page-header">

<h1>
Orders
</h1>

</div>

<?php if ($message): ?>

<div class="success">
<?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>

<?php if ($error): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>


<!-- STATS -->

<?php

$totalOrders = 0;
$pendingOrders = 0;
$processingOrders = 0;
$deliveredOrders = 0;

$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total_orders,
        SUM(order_status = 'pending') AS pending_orders,
        SUM(order_status = 'processing') AS processing_orders,
        SUM(order_status = 'delivered') AS delivered_orders
    FROM orders
");

if ($statsResult) {

    $stats = $statsResult->fetch_assoc();

    $totalOrders = (int) ($stats["total_orders"] ?? 0);
    $pendingOrders = (int) ($stats["pending_orders"] ?? 0);
    $processingOrders = (int) ($stats["processing_orders"] ?? 0);
    $deliveredOrders = (int) ($stats["delivered_orders"] ?? 0);
}

?>

<div class="stats">

<div class="stat-card">

<h3>
Total Orders
</h3>

<strong>
<?= $totalOrders ?>
</strong>

</div>


<div class="stat-card">

<h3>
Pending Orders
</h3>

<strong>
<?= $pendingOrders ?>
</strong>

</div>


<div class="stat-card">

<h3>
Processing Orders
</h3>

<strong>
<?= $processingOrders ?>
</strong>

</div>


<div class="stat-card">

<h3>
Delivered Orders
</h3>

<strong>
<?= $deliveredOrders ?>
</strong>

</div>

</div>


<!-- ORDERS -->

<div class="table-box">

<h2>
All Orders
</h2>

<table>

<thead>

<tr>

<th>
ID
</th>

<th>
Order Number
</th>

<th>
Customer
</th>

<th>
Contact
</th>

<th>
Address
</th>

<th>
Total
</th>

<th>
Payment
</th>

<th>
Order Status
</th>

<th>
Update
</th>

<th>
Action
</th>

</tr>

</thead>

<tbody>

<?php if ($orders && $orders->num_rows > 0): ?>

<?php while ($order = $orders->fetch_assoc()): ?>

<tr>

<td>
<?= (int) $order["id"] ?>
</td>

<td>

<strong>
<?= htmlspecialchars($order["order_number"]) ?>
</strong>

<br>

<small>
<?= htmlspecialchars($order["created_at"]) ?>
</small>

</td>

<td>

<?= htmlspecialchars($order["name"]) ?>

<br>

<small>
User:
<?= htmlspecialchars($order["user_name"] ?? "Guest") ?>
</small>

</td>

<td>

<?= htmlspecialchars($order["email"]) ?>

<br>

<?= htmlspecialchars($order["phone"]) ?>

</td>

<td>

<?= htmlspecialchars($order["address"]) ?>

<br>

<?= htmlspecialchars($order["city"]) ?>,
<?= htmlspecialchars($order["state"]) ?>

<br>

<?= htmlspecialchars($order["pincode"]) ?>

</td>

<td>

<strong>
₹<?= number_format(
    (float) $order["total"],
    2
) ?>
</strong>

<br>

<small>
Subtotal:
₹<?= number_format(
    (float) $order["subtotal"],
    2
) ?>
</small>

<br>

<small>
Shipping:
₹<?= number_format(
    (float) $order["shipping"],
    2
) ?>
</small>

</td>

<td>

<span class="status <?= htmlspecialchars(
    $order["payment_status"]
) ?>">

<?= htmlspecialchars(
    $order["payment_status"]
) ?>

</span>

<br><br>

<?= htmlspecialchars(
    $order["payment_method"]
) ?>

</td>

<td>

<span class="status <?= htmlspecialchars(
    $order["order_status"]
) ?>">

<?= htmlspecialchars(
    $order["order_status"]
) ?>

</span>

</td>

<td>

<form
    method="POST"
    class="status-form"
>

<input
    type="hidden"
    name="update_status"
    value="1"
>

<input
    type="hidden"
    name="order_id"
    value="<?= (int) $order["id"] ?>"
>

<select name="order_status">

<?php

$orderStatuses = [
    "pending",
    "confirmed",
    "processing",
    "shipped",
    "delivered",
    "cancelled"
];

foreach ($orderStatuses as $status):

?>

<option
    value="<?= $status ?>"
    <?= $order["order_status"] === $status
        ? "selected"
        : "" ?>
>

<?= ucfirst($status) ?>

</option>

<?php endforeach; ?>

</select>


<select name="payment_status">

<?php

$paymentStatuses = [
    "pending",
    "paid",
    "failed"
];

foreach ($paymentStatuses as $status):

?>

<option
    value="<?= $status ?>"
    <?= $order["payment_status"] === $status
        ? "selected"
        : "" ?>
>

<?= ucfirst($status) ?>

</option>

<?php endforeach; ?>

</select>


<button
    type="submit"
    class="update-btn"
>

Update

</button>

</form>

</td>

<td>

<a
    href="orders.php?delete=<?= (int) $order["id"] ?>"
    class="delete-btn"
    onclick="return confirm('Are you sure you want to delete this order?')"
>

Delete

</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="10">

No orders found.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</main>

</body>

</html>