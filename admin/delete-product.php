<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

$productId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$productId || $productId < 1) {
    header("Location: products.php?error=invalid_product");
    exit;
}

try {
    $conn->begin_transaction();

    // Get product image names before deleting the database record.
    $get = $conn->prepare("SELECT image, image2, image3, image4 FROM products WHERE id = ? FOR UPDATE");
    if (!$get) {
        throw new Exception("Unable to prepare product lookup.");
    }
    $get->bind_param("i", $productId);
    $get->execute();
    $product = $get->get_result()->fetch_assoc();
    $get->close();

    if (!$product) {
        throw new Exception("Product not found.");
    }

    // Remove dependent cart/order-item rows first so foreign keys cannot block deletion.
    $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ?");
    if (!$stmt) throw new Exception("Unable to prepare cart cleanup.");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM order_items WHERE product_id = ?");
    if (!$stmt) throw new Exception("Unable to prepare order-item cleanup.");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if (!$stmt) throw new Exception("Unable to prepare product deletion.");
    $stmt->bind_param("i", $productId);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception("Product could not be deleted.");
    }
    $stmt->close();

    $conn->commit();

    // Delete uploaded product images only after the DB transaction succeeds.
    $uploadDir = __DIR__ . "/../uploads/products/";
    foreach (["image", "image2", "image3", "image4"] as $field) {
        $file = basename((string)($product[$field] ?? ""));
        if ($file !== "" && is_file($uploadDir . $file)) {
            @unlink($uploadDir . $file);
        }
    }

    header("Location: products.php?deleted=1");
    exit;

} catch (Throwable $e) {
    if ($conn->errno || $conn->connect_errno === 0) {
        @$conn->rollback();
    }
    header("Location: products.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>
