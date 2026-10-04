<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../db.php";

/* CART COUNT */
$cartCount = 0;

if (isset($_SESSION["user_id"])) {

    $userId = (int) $_SESSION["user_id"];

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(quantity), 0) AS total
        FROM cart
        WHERE user_id = ?
    ");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $cartCount = (int) $row["total"];
    }
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
Khushbu Jewellery
</title>

<meta
    name="description"
    content="Discover elegant gold and diamond jewellery at Khushbu Jewellery."
>

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="assets/css/style.css"
>

</head>

<body>


<!-- TOP BAR -->

<div class="top-bar">

<div>
Free shipping on orders above ₹10,000
</div>

<div>
Authentic Jewellery • Trusted Quality
</div>

</div>


<!-- HEADER -->

<header class="site-header">

<div class="header-container">


<!-- LOGO -->

<a
    href="index.php"
    class="logo"
>

<span class="logo-main">
KHUSHBU
</span>

<span class="logo-sub">
JEWELLERS
</span>

</a>


<!-- DESKTOP NAV -->

<nav class="main-nav">

<a href="index.php">
Home
</a>

<a href="about.php">
About
</a>

<a href="shop.php">
Shop
</a>


<a href="contact.php">
Contact
</a>

</nav>


<!-- HEADER ACTIONS -->

<div class="header-actions">
<?php if (isset($_SESSION["user_id"])): ?>

<a
    href="logout.php"
    class="account-link"
    title="Logout"
>
Logout
</a>

<?php else: ?>

<a
    href="login.php"
    class="account-link"
    title="Login"
>
Login
</a>

<?php endif; ?>


<a
    href="cart.php"
    class="cart-link"
>

🛒

<span class="cart-count">
<?= $cartCount ?>
</span>

</a>


<button
    type="button"
    class="mobile-menu-btn"
    id="mobileMenuBtn"
>
☰
</button>


</div>

</div>


<!-- MOBILE MENU -->

<div
    class="mobile-menu"
    id="mobileMenu"
>

<a href="index.php">
Home
</a>

<a href="about.php">
About
</a>

<a href="shop.php">
Shop
</a>


<a href="contact.php">
Contact
</a>

<?php if (isset($_SESSION["user_id"])): ?>

<a href="logout.php">
Logout
</a>

<?php else: ?>

<a href="login.php">
Login
</a>

<a href="register.php">
Register
</a>

<?php endif; ?>

</div>

</header>