<?php

session_start();

require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    $redirect = $_SERVER["HTTP_REFERER"] ?? "shop.php";
    header("Location: login.php?redirect=" . urlencode($redirect));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: shop.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$productId = isset($_POST["product_id"]) ? (int) $_POST["product_id"] : 0;
$quantity = isset($_POST["quantity"]) ? (int) $_POST["quantity"] : 1;

if ($productId <= 0) {
    header("Location: shop.php");
    exit;
}

if ($quantity < 1) {
    $quantity = 1;
}

/* Check product */
$stmt = $conn->prepare("
    SELECT id, name, stock, status
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $productId);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product || $product["status"] !== "active") {
    header("Location: shop.php?error=product-not-found");
    exit;
}

/* Check stock */
if ((int)$product["stock"] <= 0) {
    header("Location: shop.php?error=out-of-stock");
    exit;
}

/* Check existing cart item */
$stmt = $conn->prepare("
    SELECT id, quantity
    FROM cart
    WHERE user_id = ? AND product_id = ?
    LIMIT 1
");

$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();

$result = $stmt->get_result();
$cartItem = $result->fetch_assoc();

if ($cartItem) {

    $newQuantity = (int)$cartItem["quantity"] + $quantity;

    /* Do not allow quantity greater than stock */
    if ($newQuantity > (int)$product["stock"]) {
        $newQuantity = (int)$product["stock"];
    }

    $update = $conn->prepare("
        UPDATE cart
        SET quantity = ?
        WHERE id = ?
    ");

    $update->bind_param(
        "ii",
        $newQuantity,
        $cartItem["id"]
    );

    $update->execute();

} else {

    /* First time adding product */
    if ($quantity > (int)$product["stock"]) {
        $quantity = (int)$product["stock"];
    }

    $insert = $conn->prepare("
        INSERT INTO cart
        (user_id, product_id, quantity)
        VALUES (?, ?, ?)
    ");

    $insert->bind_param(
        "iii",
        $userId,
        $productId,
        $quantity
    );

    $insert->execute();
}

/* Redirect back */
$redirect = $_SERVER["HTTP_REFERER"] ?? "shop.php";

header("Location: " . $redirect);
exit;
?>