
<?php

session_start();

require_once "db.php";

/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?redirect=cart.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];


/* =========================================
   REMOVE ITEM
========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["remove_item"])
) {

    $cartId = (int) $_POST["cart_id"];

    if ($cartId > 0) {

        $stmt = $conn->prepare("
            DELETE FROM cart
            WHERE id = ?
            AND user_id = ?
        ");

        $stmt->bind_param(
            "ii",
            $cartId,
            $userId
        );

        $stmt->execute();
    }

    header("Location: cart.php");
    exit;
}


/* =========================================
   UPDATE QUANTITY
========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_cart"])
) {

    $cartId = (int) $_POST["cart_id"];
    $quantity = (int) $_POST["quantity"];

    if ($cartId > 0) {

        if ($quantity < 1) {
            $quantity = 1;
        }

        /*
         * Get available stock
         */

        $stmt = $conn->prepare("
            SELECT p.stock
            FROM cart c
            INNER JOIN products p
                ON p.id = c.product_id
            WHERE c.id = ?
            AND c.user_id = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "ii",
            $cartId,
            $userId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $stockData = $result->fetch_assoc();


        if ($stockData) {

            $stock = (int) $stockData["stock"];

            if ($stock <= 0) {
                $quantity = 1;
            } elseif ($quantity > $stock) {
                $quantity = $stock;
            }


            $update = $conn->prepare("
                UPDATE cart
                SET quantity = ?
                WHERE id = ?
                AND user_id = ?
            ");

            $update->bind_param(
                "iii",
                $quantity,
                $cartId,
                $userId
            );

            $update->execute();
        }
    }

    header("Location: cart.php");
    exit;
}


/* =========================================
   GET CART ITEMS
========================================= */

$stmt = $conn->prepare("
    SELECT
        c.id AS cart_id,
        c.product_id,
        c.quantity,

        p.name,
        p.slug,
        p.price,
        p.sale_price,
        p.stock,
        p.image,
        p.status

    FROM cart c

    INNER JOIN products p
        ON p.id = c.product_id

    WHERE c.user_id = ?

    ORDER BY c.created_at DESC
");

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result = $stmt->get_result();


/* =========================================
   CALCULATE TOTAL
========================================= */

$cartItems = [];

$subtotal = 0;

while ($row = $result->fetch_assoc()) {

    /*
     * Use sale price if available
     */

    $unitPrice =
        (
            $row["sale_price"] !== null &&
            $row["sale_price"] > 0
        )
        ? (float) $row["sale_price"]
        : (float) $row["price"];


    $quantity = (int) $row["quantity"];

    $lineTotal = $unitPrice * $quantity;

    $row["unit_price"] = $unitPrice;

    $row["line_total"] = $lineTotal;

    $cartItems[] = $row;

    $subtotal += $lineTotal;
}


/* =========================================
   SHIPPING
========================================= */

$shipping = 0;

/*
 * Free shipping above ₹10,000
 * Otherwise ₹199
 */

if ($subtotal > 0 && $subtotal < 10000) {
    $shipping = 199;
}


/* =========================================
   GRAND TOTAL
========================================= */

$grandTotal = $subtotal + $shipping;

?>

<?php require_once "includes/header.php"; ?>


<!-- =========================================
     CART HERO
========================================= -->

<section class="cart-hero">

    <div class="container">

        <div class="cart-hero-content">

            <span>YOUR SHOPPING BAG</span>

            <h1>Shopping Cart</h1>

            <p>
                Review your selected jewellery before checkout.
            </p>

        </div>

    </div>

</section>


<!-- =========================================
     CART SECTION
========================================= -->

<section class="section cart-section">

    <div class="container">

        <div class="cart-breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <strong>Cart</strong>
        </div>


        <?php if (empty($cartItems)): ?>

            <!-- EMPTY CART -->

            <div class="empty-cart">

                <div class="empty-cart-icon">
                    🛒
                </div>

                <h2>Your Cart is Empty</h2>

                <p>
                    You haven't added any jewellery to your cart yet.
                </p>

                <a href="shop.php" class="btn">
                    Continue Shopping
                </a>

            </div>


        <?php else: ?>


            <!-- CART LAYOUT -->

            <div class="cart-layout">


                <!-- =================================
                     CART ITEMS
                ================================= -->

                <div class="cart-items-box">

                    <div class="cart-items-header">

                        <h2>Your Items</h2>

                        <span>
                            <?= count($cartItems) ?>
                            <?= count($cartItems) == 1 ? "Item" : "Items" ?>
                        </span>

                    </div>


                    <?php foreach ($cartItems as $item): ?>

                        <?php

                        $image = !empty($item["image"])
                                    ? "uploads/products/" . ltrim($item["image"], "/\\")
                                    : "assets/images/about.png";

                        ?>


                        <div class="cart-item">

                            <!-- IMAGE -->

                            <div class="cart-item-image">

                                <a href="shop-details.php?id=<?= (int)$item["product_id"] ?>">

                                    <img
                                        src="<?= htmlspecialchars($image) ?>"
                                        alt="<?= htmlspecialchars($item["name"]) ?>"
                                    >

                                </a>

                            </div>


                            <!-- INFO -->

                            <div class="cart-item-info">

                                <span class="cart-item-label">
                                    Jewellery
                                </span>

                                <h3>

                                    <a
                                        href="shop-details.php?id=<?= (int)$item["product_id"] ?>"
                                    >
                                        <?= htmlspecialchars($item["name"]) ?>
                                    </a>

                                </h3>


                                <div class="cart-item-price">

                                    <?php if (
                                        $item["sale_price"] !== null &&
                                        $item["sale_price"] > 0 &&
                                        $item["sale_price"] < $item["price"]
                                    ): ?>

                                        <strong>
                                            ₹<?= number_format((float)$item["sale_price"], 2) ?>
                                        </strong>

                                        <del>
                                            ₹<?= number_format((float)$item["price"], 2) ?>
                                        </del>

                                    <?php else: ?>

                                        <strong>
                                            ₹<?= number_format((float)$item["price"], 2) ?>
                                        </strong>

                                    <?php endif; ?>

                                </div>


                                <!-- QUANTITY -->

                                <form
                                    method="POST"
                                    class="cart-quantity-form"
                                >

                                    <input
                                        type="hidden"
                                        name="cart_id"
                                        value="<?= (int)$item["cart_id"] ?>"
                                    >

                                    <div class="quantity-control">

                                        <button
                                            type="button"
                                            class="quantity-minus"
                                        >
                                            −
                                        </button>

                                        <input
                                            type="number"
                                            name="quantity"
                                            value="<?= (int)$item["quantity"] ?>"
                                            min="1"
                                            max="<?= (int)$item["stock"] ?>"
                                        >

                                        <button
                                            type="button"
                                            class="quantity-plus"
                                        >
                                            +
                                        </button>

                                    </div>

                                    <button
                                        type="submit"
                                        name="update_cart"
                                        class="update-cart-btn"
                                    >
                                        Update
                                    </button>

                                </form>


                                <!-- REMOVE -->

                                <form
                                    method="POST"
                                    class="remove-cart-form"
                                >

                                    <input
                                        type="hidden"
                                        name="cart_id"
                                        value="<?= (int)$item["cart_id"] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="remove_item"
                                    >
                                        Remove
                                    </button>

                                </form>

                            </div>


                            <!-- LINE TOTAL -->

                            <div class="cart-item-total">

                                <span>Total</span>

                                <strong>
                                    ₹<?= number_format($item["line_total"], 2) ?>
                                </strong>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <!-- =================================
                     CART SUMMARY
                ================================= -->

                <aside class="cart-summary">

                    <div class="cart-summary-inner">

                        <span class="summary-label">
                            ORDER SUMMARY
                        </span>

                        <h2>
                            Cart Total
                        </h2>


                        <div class="summary-row">

                            <span>
                                Subtotal
                            </span>

                            <strong>
                                ₹<?= number_format($subtotal, 2) ?>
                            </strong>

                        </div>


                        <div class="summary-row">

                            <span>
                                Shipping
                            </span>

                            <strong>

                                <?php if ($shipping == 0): ?>

                                    Free

                                <?php else: ?>

                                    ₹<?= number_format($shipping, 2) ?>

                                <?php endif; ?>

                            </strong>

                        </div>


                        <?php if ($subtotal > 0 && $subtotal < 10000): ?>

                            <div class="shipping-note">

                                Add ₹<?= number_format(10000 - $subtotal, 2) ?>
                                more for FREE shipping.

                            </div>

                        <?php endif; ?>


                        <div class="summary-total">

                            <span>
                                Grand Total
                            </span>

                            <strong>
                                ₹<?= number_format($grandTotal, 2) ?>
                            </strong>

                        </div>


                        <a
                            href="checkout.php"
                            class="checkout-btn"
                        >
                            Proceed to Checkout
                        </a>


                        <a
                            href="shop.php"
                            class="continue-shopping"
                        >
                            ← Continue Shopping
                        </a>

                    </div>

                </aside>

            </div>

        <?php endif; ?>

    </div>

</section>


<style>

/* =========================================
   CART HERO
========================================= */

.cart-hero {

    min-height: 340px;

    display: flex;

    align-items: center;

    position: relative;

    color: #fff;

    background:
        linear-gradient(
            rgba(20,20,20,.62),
            rgba(20,20,20,.62)
        ),
        url("https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=2000&q=85")
        center/cover no-repeat;
}


.cart-hero-content {

    text-align: center;

    max-width: 700px;

    margin: auto;
}


.cart-hero-content span {

    color: #d3ad62;

    font-size: 11px;

    letter-spacing: 4px;
}


.cart-hero-content h1 {

    font-family: "Cormorant Garamond", serif;

    font-size: clamp(48px, 7vw, 72px);

    font-weight: 500;

    margin: 15px 0;
}


.cart-hero-content p {

    color: rgba(255,255,255,.85);

    font-size: 14px;
}


/* =========================================
   BREADCRUMB
========================================= */

.cart-breadcrumb {

    display: flex;

    gap: 10px;

    font-size: 12px;

    margin-bottom: 35px;

    color: #999;
}


.cart-breadcrumb a {

    color: #555;
}


.cart-breadcrumb strong {

    color: #171717;

    font-weight: 500;
}


/* =========================================
   CART LAYOUT
========================================= */

.cart-layout {

    display: grid;

    grid-template-columns: 1fr 360px;

    gap: 40px;

    align-items: start;
}


/* =========================================
   CART ITEMS BOX
========================================= */

.cart-items-box {

    border-top: 1px solid #ddd;
}


.cart-items-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 20px 0;

    border-bottom: 1px solid #ddd;
}


.cart-items-header h2 {

    font-family: "Cormorant Garamond", serif;

    font-size: 30px;

    font-weight: 600;

    margin: 0;
}


.cart-items-header span {

    color: #888;

    font-size: 12px;
}


/* =========================================
   CART ITEM
========================================= */

.cart-item {

    display: grid;

    grid-template-columns: 160px 1fr 150px;

    gap: 25px;

    padding: 25px 0;

    border-bottom: 1px solid #e7e3dc;
}


.cart-item-image {

    height: 180px;

    overflow: hidden;

    background: #f5f2ec;
}


.cart-item-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    transition: .4s ease;
}


.cart-item-image:hover img {

    transform: scale(1.05);
}


.cart-item-info {

    min-width: 0;
}


.cart-item-label {

    color: var(--gold);

    font-size: 9px;

    letter-spacing: 2px;

    text-transform: uppercase;
}


.cart-item-info h3 {

    font-family: "Cormorant Garamond", serif;

    font-size: 26px;

    margin: 7px 0 8px;

    font-weight: 600;
}


.cart-item-info h3 a {

    color: #171717;
}


.cart-item-price {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 18px;
}


.cart-item-price strong {

    font-size: 14px;
}


.cart-item-price del {

    color: #999;

    font-size: 12px;
}


/* =========================================
   QUANTITY
========================================= */

.cart-quantity-form {

    display: flex;

    align-items: center;

    gap: 10px;

    flex-wrap: wrap;
}


.quantity-control {

    display: flex;

    border: 1px solid #ddd;

    height: 40px;
}


.quantity-control button {

    width: 38px;

    border: none;

    background: #fff;

    cursor: pointer;

    font-size: 18px;
}


.quantity-control button:hover {

    background: #f5f2ec;
}


.quantity-control input {

    width: 50px;

    border: none;

    border-left: 1px solid #ddd;

    border-right: 1px solid #ddd;

    text-align: center;

    outline: none;
}


/* Remove number arrows */

.quantity-control input::-webkit-outer-spin-button,
.quantity-control input::-webkit-inner-spin-button {

    -webkit-appearance: none;

    margin: 0;
}


.update-cart-btn {

    height: 40px;

    padding: 0 15px;

    border: 1px solid #171717;

    background: #171717;

    color: #fff;

    cursor: pointer;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: 1px;
}


.update-cart-btn:hover {

    background: var(--gold);

    border-color: var(--gold);
}


.remove-cart-form {

    margin-top: 15px;
}


.remove-cart-form button {

    border: none;

    background: transparent;

    padding: 0;

    color: #a44;

    cursor: pointer;

    font-size: 11px;

    text-decoration: underline;
}


/* =========================================
   LINE TOTAL
========================================= */

.cart-item-total {

    text-align: right;

    display: flex;

    flex-direction: column;

    gap: 8px;
}


.cart-item-total span {

    color: #999;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: 1px;
}


.cart-item-total strong {

    font-size: 16px;
}


/* =========================================
   CART SUMMARY
========================================= */

.cart-summary {

    position: sticky;

    top: 110px;
}


.cart-summary-inner {

    background: #faf8f3;

    border: 1px solid #e8e2d7;

    padding: 35px 30px;
}


.summary-label {

    color: var(--gold);

    font-size: 10px;

    letter-spacing: 3px;
}


.cart-summary h2 {

    font-family: "Cormorant Garamond", serif;

    font-size: 34px;

    font-weight: 600;

    margin: 8px 0 25px;
}


.summary-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 13px 0;

    border-bottom: 1px solid #e4dfd5;

    font-size: 13px;
}


.summary-row span {

    color: #777;
}


.summary-row strong {

    font-weight: 500;
}


.shipping-note {

    padding: 12px;

    margin-top: 15px;

    background: #fff;

    border-left: 2px solid var(--gold);

    color: #777;

    font-size: 11px;

    line-height: 1.6;
}


.summary-total {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 22px 0;

    margin-top: 8px;

    border-top: 1px solid #171717;
}


.summary-total span {

    font-size: 13px;

    font-weight: 600;
}


.summary-total strong {

    font-size: 18px;

    font-weight: 600;
}


.checkout-btn {

    display: block;

    width: 100%;

    text-align: center;

    background: #171717;

    color: #fff;

    padding: 15px;

    font-size: 10px;

    letter-spacing: 2px;

    text-transform: uppercase;

    transition: .25s ease;
}


.checkout-btn:hover {

    background: var(--gold);
}


.continue-shopping {

    display: block;

    text-align: center;

    margin-top: 18px;

    color: #555;

    font-size: 11px;
}


.continue-shopping:hover {

    color: var(--gold);
}


/* =========================================
   EMPTY CART
========================================= */

.empty-cart {

    min-height: 400px;

    border: 1px solid #eee;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    text-align: center;

    padding: 40px;
}


.empty-cart-icon {

    font-size: 55px;

    margin-bottom: 10px;
}


.empty-cart h2 {

    font-family: "Cormorant Garamond", serif;

    font-size: 42px;

    margin: 10px 0;
}


.empty-cart p {

    color: #777;

    font-size: 13px;

    margin-bottom: 25px;
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 1000px) {

    .cart-layout {

        grid-template-columns: 1fr;

    }


    .cart-summary {

        position: static;

    }

}


@media (max-width: 700px) {

    .cart-item {

        grid-template-columns: 110px 1fr;

        gap: 18px;

    }


    .cart-item-image {

        height: 140px;

    }


    .cart-item-total {

        grid-column: 2;

        text-align: left;

        flex-direction: row;

        align-items: center;

    }

}


@media (max-width: 500px) {

    .cart-hero {

        min-height: 280px;

    }


    .cart-hero-content h1 {

        font-size: 45px;

    }


    .cart-item {

        grid-template-columns: 90px 1fr;

        gap: 14px;

    }


    .cart-item-image {

        height: 120px;

    }


    .cart-item-info h3 {

        font-size: 21px;

    }


    .cart-summary-inner {

        padding: 25px 20px;

    }

}

</style>


<script>

/* =========================================
   QUANTITY +/- BUTTONS
========================================= */

document.querySelectorAll(".quantity-control").forEach(function(control) {

    const minus = control.querySelector(".quantity-minus");
    const plus = control.querySelector(".quantity-plus");
    const input = control.querySelector("input");

    if (minus) {

        minus.addEventListener("click", function() {

            let value = parseInt(input.value) || 1;

            if (value > 1) {
                input.value = value - 1;
            }

        });

    }


    if (plus) {

        plus.addEventListener("click", function() {

            let value = parseInt(input.value) || 1;

            let max = parseInt(input.getAttribute("max")) || 999;

            if (value < max) {
                input.value = value + 1;
            }

        });

    }

});

</script>


<?php require_once "includes/footer.php"; ?>


