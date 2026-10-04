<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

$message = "";
$error = "";

/* MARK AS READ */
if (isset($_GET["read"])) {

    $id = (int) $_GET["read"];

    $stmt = $conn->prepare("
        UPDATE messages
        SET status = 'read'
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: messages.php?updated=1");
        exit;
    }
}

/* DELETE MESSAGE */
if (isset($_GET["delete"])) {

    $id = (int) $_GET["delete"];

    $stmt = $conn->prepare("
        DELETE FROM messages
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: messages.php?deleted=1");
        exit;
    }

    $error = "Unable to delete message.";
}

/* SUCCESS */
if (isset($_GET["updated"])) {
    $message = "Message marked as read.";
}

if (isset($_GET["deleted"])) {
    $message = "Message deleted successfully.";
}


/* MESSAGE STATS */

$totalMessages = 0;
$unreadMessages = 0;
$readMessages = 0;

$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total_messages,
        SUM(status = 'unread') AS unread_messages,
        SUM(status = 'read') AS read_messages
    FROM messages
");

if ($statsResult) {

    $stats = $statsResult->fetch_assoc();

    $totalMessages =
        (int) ($stats["total_messages"] ?? 0);

    $unreadMessages =
        (int) ($stats["unread_messages"] ?? 0);

    $readMessages =
        (int) ($stats["read_messages"] ?? 0);
}


/* GET MESSAGES */

$messages = $conn->query("
    SELECT *
    FROM messages
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

<title>Messages - Jewellery Admin</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;

    font-family:
        Arial,
        sans-serif;

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

    min-width: 1000px;
}

th,
td {

    padding: 14px;

    border-bottom:
        1px solid #eee;

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


/* UNREAD */

.unread-row td {

    background: #fffaf0;
}

.unread-badge {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    background: #f59e0b;

    color: white;

    font-size: 12px;

    font-weight: bold;
}

.read-badge {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    background: #dcfce7;

    color: #166534;

    font-size: 12px;

    font-weight: bold;
}


/* ACTION */

.action {

    display: inline-block;

    padding: 7px 11px;

    border-radius: 5px;

    text-decoration: none;

    font-size: 13px;

    margin-right: 5px;

    margin-bottom: 5px;
}

.read-btn {

    background: #2563eb;

    color: white;
}

.delete-btn {

    background: #b00020;

    color: white;
}


/* MESSAGE TEXT */

.message-text {

    max-width: 350px;

    line-height: 1.5;
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
Contact Messages
</h1>

<p>
Manage customer enquiries and contact messages.
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
Total Messages
</h3>

<strong>
<?= $totalMessages ?>
</strong>

</div>


<div class="stat-card">

<h3>
Unread Messages
</h3>

<strong>
<?= $unreadMessages ?>
</strong>

</div>


<div class="stat-card">

<h3>
Read Messages
</h3>

<strong>
<?= $readMessages ?>
</strong>

</div>


</div>


<!-- MESSAGES TABLE -->

<div class="table-box">

<h2>
All Messages
</h2>


<table>

<thead>

<tr>

<th>
ID
</th>

<th>
Customer
</th>

<th>
Contact
</th>

<th>
Subject
</th>

<th>
Message
</th>

<th>
Status
</th>

<th>
Date
</th>

<th>
Action
</th>

</tr>

</thead>


<tbody>


<?php if (
    $messages &&
    $messages->num_rows > 0
): ?>


<?php while (
    $msg = $messages->fetch_assoc()
): ?>


<tr class="<?= $msg["status"] === "unread"
    ? "unread-row"
    : "" ?>">


<td>

<?= (int) $msg["id"] ?>

</td>


<td>

<strong>

<?= htmlspecialchars(
    $msg["name"]
) ?>

</strong>

</td>


<td>

<?= htmlspecialchars(
    $msg["email"]
) ?>

<br>

<?= htmlspecialchars(
    $msg["phone"] ?? "-"
) ?>

</td>


<td>

<?= htmlspecialchars(
    $msg["subject"] ?? "-"
) ?>

</td>


<td>

<div class="message-text">

<?= nl2br(
    htmlspecialchars(
        $msg["message"]
    )
) ?>

</div>

</td>


<td>

<?php if (
    $msg["status"] === "unread"
): ?>

<span class="unread-badge">
Unread
</span>

<?php else: ?>

<span class="read-badge">
Read
</span>

<?php endif; ?>

</td>


<td>

<?= htmlspecialchars(
    $msg["created_at"]
) ?>

</td>


<td>


<?php if (
    $msg["status"] === "unread"
): ?>

<a
    class="action read-btn"
    href="messages.php?read=<?= (int) $msg["id"] ?>"
>

Mark Read

</a>

<?php endif; ?>


<a
    class="action delete-btn"
    href="messages.php?delete=<?= (int) $msg["id"] ?>"
    onclick="return confirm('Delete this message permanently?')"
>

Delete

</a>


</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td colspan="8">

No messages found.

</td>

</tr>


<?php endif; ?>


</tbody>

</table>

</div>


</main>

</body>

</html>