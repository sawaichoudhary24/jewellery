<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

$message = "";
$error = "";

/* BLOCK / UNBLOCK USER */
if (isset($_GET["toggle"]) && isset($_GET["id"])) {

    $id = (int) $_GET["id"];

    /* Admin ko block nahi karna */
    if ($id == (int) $_SESSION["admin_id"]) {
        $error = "You cannot block your own admin account.";
    } else {

        $stmt = $conn->prepare("
            SELECT status
            FROM users
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $user = $result->fetch_assoc();

            $newStatus =
                $user["status"] === "active"
                ? "blocked"
                : "active";

            $update = $conn->prepare("
                UPDATE users
                SET status = ?
                WHERE id = ?
            ");

            $update->bind_param(
                "si",
                $newStatus,
                $id
            );

            if ($update->execute()) {
                header("Location: users.php?updated=1");
                exit;
            }

        } else {
            $error = "User not found.";
        }
    }
}


/* DELETE USER */
if (isset($_GET["delete"])) {

    $id = (int) $_GET["delete"];

    /* Admin ko delete nahi karna */
    if ($id == (int) $_SESSION["admin_id"]) {

        $error = "You cannot delete your own admin account.";

    } else {

        $stmt = $conn->prepare("
            DELETE FROM users
            WHERE id = ?
            AND role != 'admin'
        ");

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: users.php?deleted=1");
            exit;
        } else {
            $error = "Unable to delete user.";
        }
    }
}


/* SUCCESS MESSAGES */

if (isset($_GET["updated"])) {
    $message = "User status updated successfully.";
}

if (isset($_GET["deleted"])) {
    $message = "User deleted successfully.";
}


/* USER STATS */

$totalUsers = 0;
$activeUsers = 0;
$blockedUsers = 0;

$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total_users,
        SUM(status = 'active') AS active_users,
        SUM(status = 'blocked') AS blocked_users
    FROM users
    WHERE role = 'user'
");

if ($statsResult) {

    $stats = $statsResult->fetch_assoc();

    $totalUsers = (int) ($stats["total_users"] ?? 0);
    $activeUsers = (int) ($stats["active_users"] ?? 0);
    $blockedUsers = (int) ($stats["blocked_users"] ?? 0);
}


/* GET USERS */

$users = $conn->query("
    SELECT *
    FROM users
    ORDER BY id DESC
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

<title>Users - Jewellery Admin</title>

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


/* HEADER */

.page-header {
    margin-bottom: 25px;
}

.page-header h1 {
    margin: 0 0 8px;
}

.page-header p {
    margin: 0;
    color: #777;
}


/* MESSAGE */

.success {
    background: #e7f7e7;
    color: #176b17;

    padding: 14px;

    border-radius: 6px;

    margin-bottom: 20px;
}

.error {
    background: #ffe5e5;
    color: #b00020;

    padding: 14px;

    border-radius: 6px;

    margin-bottom: 20px;
}


/* STATS */

.stats {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;

    margin-bottom: 25px;
}

.stat-card {
    background: white;

    padding: 22px;

    border-radius: 10px;

    box-shadow:
        0 2px 10px
        rgba(0,0,0,0.05);
}

.stat-card h3 {
    margin: 0 0 10px;

    color: #777;

    font-size: 14px;
}

.stat-card strong {
    font-size: 28px;
}


/* TABLE */

.table-box {
    background: white;

    padding: 20px;

    border-radius: 10px;

    overflow-x: auto;
}

.table-box h2 {
    margin-top: 0;
}

table {
    width: 100%;

    border-collapse: collapse;

    min-width: 950px;
}

th,
td {
    padding: 13px;

    border-bottom:
        1px solid #eee;

    text-align: left;

    vertical-align: middle;
}

th {
    background: #181818;
    color: white;
}

tr:hover td {
    background: #fafafa;
}


/* ROLE */

.role {
    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;
}

.role-admin {
    background: #fef3c7;
    color: #92400e;
}

.role-user {
    background: #dbeafe;
    color: #1d4ed8;
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

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-blocked {
    background: #fee2e2;
    color: #b91c1c;
}


/* ACTIONS */

.action {
    display: inline-block;

    padding: 7px 11px;

    border-radius: 5px;

    text-decoration: none;

    font-size: 13px;

    margin-right: 5px;
}

.block-btn {
    background: #f59e0b;
    color: white;
}

.unblock-btn {
    background: #16a34a;
    color: white;
}

.delete-btn {
    background: #b00020;
    color: white;
}


/* MOBILE */

@media(max-width: 900px) {

    .stats {
        grid-template-columns:
            repeat(2, 1fr);
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

}

</style>

</head>

<body>


<!-- SIDEBAR -->

<aside class="sidebar">

<h2>
Jewellery Admin
</h2>

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
Customers
</h1>

<p>
Manage registered customers.
</p>

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

<div class="stats">


<div class="stat-card">

<h3>
Total Customers
</h3>

<strong>
<?= $totalUsers ?>
</strong>

</div>


<div class="stat-card">

<h3>
Active Customers
</h3>

<strong>
<?= $activeUsers ?>
</strong>

</div>


<div class="stat-card">

<h3>
Blocked Customers
</h3>

<strong>
<?= $blockedUsers ?>
</strong>

</div>


</div>


<!-- USERS TABLE -->

<div class="table-box">

<h2>
All Users
</h2>


<table>

<thead>

<tr>

<th>
ID
</th>

<th>
Name
</th>

<th>
Email
</th>

<th>
Phone
</th>

<th>
Address
</th>

<th>
Role
</th>

<th>
Status
</th>

<th>
Registered
</th>

<th>
Action
</th>

</tr>

</thead>


<tbody>


<?php if ($users && $users->num_rows > 0): ?>


<?php while ($user = $users->fetch_assoc()): ?>


<tr>


<td>

<?= (int) $user["id"] ?>

</td>


<td>

<strong>

<?= htmlspecialchars(
    $user["name"]
) ?>

</strong>

</td>


<td>

<?= htmlspecialchars(
    $user["email"]
) ?>

</td>


<td>

<?= htmlspecialchars(
    $user["phone"] ?? "-"
) ?>

</td>


<td>

<?php if (!empty($user["address"])): ?>

<?= htmlspecialchars(
    $user["address"]
) ?>

<br>

<?= htmlspecialchars(
    $user["city"] ?? ""
) ?>

<?php if (!empty($user["pincode"])): ?>

-
<?= htmlspecialchars(
    $user["pincode"]
) ?>

<?php endif; ?>

<?php else: ?>

-

<?php endif; ?>

</td>


<td>

<?php if ($user["role"] === "admin"): ?>

<span class="role role-admin">
Admin
</span>

<?php else: ?>

<span class="role role-user">
User
</span>

<?php endif; ?>

</td>


<td>

<span class="
status
status-<?= htmlspecialchars(
    $user["status"]
) ?>
">

<?= htmlspecialchars(
    $user["status"]
) ?>

</span>

</td>


<td>

<?= htmlspecialchars(
    $user["created_at"]
) ?>

</td>


<td>


<?php if ($user["role"] === "admin"): ?>

<strong>
Admin
</strong>

<?php else: ?>


<?php if ($user["status"] === "active"): ?>

<a
    class="action block-btn"
    href="users.php?toggle=1&id=<?= (int) $user["id"] ?>"
    onclick="return confirm('Block this user?')"
>

Block

</a>

<?php else: ?>

<a
    class="action unblock-btn"
    href="users.php?toggle=1&id=<?= (int) $user["id"] ?>"
    onclick="return confirm('Unblock this user?')"
>

Unblock

</a>

<?php endif; ?>


<a
    class="action delete-btn"
    href="users.php?delete=<?= (int) $user["id"] ?>"
    onclick="return confirm('Delete this user permanently?')"
>

Delete

</a>


<?php endif; ?>


</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td colspan="9">

No users found.

</td>

</tr>


<?php endif; ?>


</tbody>

</table>

</div>


</main>

</body>

</html>