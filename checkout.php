
<?php

session_start();

require_once "db.php";

/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];


/* =========================================
   GET USER
========================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        email,
        phone,
        address,
        city,
        state,
        pincode
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}


/* =========================================
   GET CART
========================================= */

$stmt = $conn->prepare("
    SELECT
        c.id AS cart_id,
        c.product_id,
        c.quantity,
        p.name,
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

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

$cartItems = [];
$subtotal = 0;


/* =========================================
   CALCULATE SUBTOTAL
========================================= */

while ($row = $result->fetch_assoc()) {

    if ($row["status"] !== "active") {
        continue;
    }

    $unitPrice =
        (
            $row["sale_price"] !== null &&
            $row["sale_price"] > 0 &&
            $row["sale_price"] < $row["price"]
        )
        ? (float) $row["sale_price"]
        : (float) $row["price"];

    $quantity = (int) $row["quantity"];

    if ($quantity < 1) {
        $quantity = 1;
    }

    $lineTotal = $unitPrice * $quantity;

    $row["unit_price"] = $unitPrice;
    $row["line_total"] = $lineTotal;

    $cartItems[] = $row;

    $subtotal += $lineTotal;
}


/* =========================================
   EMPTY CART
========================================= */

if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}


/* =========================================
   SHIPPING
========================================= */

$shipping = 0;

if ($subtotal < 10000) {
    $shipping = 199;
}


/* =========================================
   GRAND TOTAL
========================================= */

$grandTotal = $subtotal + $shipping;


/* =========================================
   PLACE ORDER
========================================= */

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $state = trim($_POST["state"] ?? "");
    $pincode = trim($_POST["pincode"] ?? "");
    $paymentMethod = $_POST["payment_method"] ?? "COD";


    /* =====================================
       VALIDATION
    ===================================== */

    if ($name === "") {
        $errors[] = "Please enter your name.";
    }

    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($phone === "") {
        $errors[] = "Please enter your phone number.";
    }

    if ($address === "") {
        $errors[] = "Please enter your address.";
    }

    if ($city === "") {
        $errors[] = "Please enter your city.";
    }

    if ($state === "") {
        $errors[] = "Please enter your state.";
    }

    if ($pincode === "") {
        $errors[] = "Please enter your pincode.";
    }

    if (!in_array($paymentMethod, ["COD"])) {
        $paymentMethod = "COD";
    }


    /* =====================================
       PLACE ORDER IF NO ERROR
    ===================================== */

    if (empty($errors)) {

        $conn->begin_transaction();

        try {

            /* ==============================
               RECHECK STOCK
            ============================== */

            foreach ($cartItems as $item) {

                $productId = (int) $item["product_id"];
                $quantity = (int) $item["quantity"];

                $stockStmt = $conn->prepare("
                    SELECT
                        id,
                        name,
                        price,
                        sale_price,
                        stock,
                        status
                    FROM products
                    WHERE id = ?
                    FOR UPDATE
                ");

                $stockStmt->bind_param(
                    "i",
                    $productId
                );

                $stockStmt->execute();

                $stockResult = $stockStmt->get_result();
                $product = $stockResult->fetch_assoc();

                if (
                    !$product ||
                    $product["status"] !== "active"
                ) {
                    throw new Exception(
                        "A product in your cart is no longer available."
                    );
                }

                if (
                    (int)$product["stock"] < $quantity
                ) {
                    throw new Exception(
                        "Not enough stock available for " .
                        $product["name"] . "."
                    );
                }
            }


            /* ==============================
               ORDER NUMBER
            ============================== */

            $orderNumber =
                "KJ" .
                date("YmdHis") .
                rand(100, 999);


            /* ==============================
               CREATE ORDER
            ============================== */

            $orderStmt = $conn->prepare("
                INSERT INTO orders
                (
                    user_id,
                    order_number,
                    name,
                    email,
                    phone,
                    address,
                    city,
                    state,
                    pincode,
                    subtotal,
                    shipping,
                    total,
                    payment_method,
                    payment_status,
                    order_status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'pending',
                    'pending'
                )
            ");

            $orderStmt->bind_param(
                "issssssssddds",
                $userId,
                $orderNumber,
                $name,
                $email,
                $phone,
                $address,
                $city,
                $state,
                $pincode,
                $subtotal,
                $shipping,
                $grandTotal,
                $paymentMethod
            );

            if (!$orderStmt->execute()) {
                throw new Exception(
                    "Unable to create order."
                );
            }

            $orderId = $conn->insert_id;


            /* ==============================
               CREATE ORDER ITEMS
            ============================== */

            foreach ($cartItems as $item) {

                $productId = (int) $item["product_id"];
                $productName = $item["name"];
                $price = (float) $item["unit_price"];
                $quantity = (int) $item["quantity"];
                $itemTotal = (float) $item["line_total"];


                $itemStmt = $conn->prepare("
                    INSERT INTO order_items
                    (
                        order_id,
                        product_id,
                        product_name,
                        price,
                        quantity,
                        total
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?
                    )
                ");

                $itemStmt->bind_param(
                    "iisdid",
                    $orderId,
                    $productId,
                    $productName,
                    $price,
                    $quantity,
                    $itemTotal
                );

                if (!$itemStmt->execute()) {
                    throw new Exception(
                        "Unable to create order items."
                    );
                }


                /* ==========================
                   REDUCE STOCK
                ========================== */

                $updateStock = $conn->prepare("
                    UPDATE products
                    SET stock = stock - ?
                    WHERE id = ?
                ");

                $updateStock->bind_param(
                    "ii",
                    $quantity,
                    $productId
                );

                if (!$updateStock->execute()) {
                    throw new Exception(
                        "Unable to update product stock."
                    );
                }
            }


            /* ==============================
               EMPTY CART
            ============================== */

            $clearCart = $conn->prepare("
                DELETE FROM cart
                WHERE user_id = ?
            ");

            $clearCart->bind_param(
                "i",
                $userId
            );

            if (!$clearCart->execute()) {
                throw new Exception(
                    "Unable to clear cart."
                );
            }


            /* ==============================
               COMMIT
            ============================== */

            $conn->commit();


            /* ==============================
               SUCCESS
            ============================== */

            header(
                "Location: order-success.php?order=" .
                urlencode($orderNumber)
            );

            exit;

        } catch (Exception $e) {

            $conn->rollback();

            $errors[] = $e->getMessage();
        }
    }
}

?>

<?php require_once "includes/header.php"; ?>


<!-- =========================================
     CHECKOUT HERO
========================================= -->

<section class="checkout-hero">

    <div class="container">

        <div class="checkout-hero-content">

            <span>SECURE CHECKOUT</span>

            <h1>Checkout</h1>

            <p>
                Complete your details to place your jewellery order.
            </p>

        </div>

    </div>

</section>


<!-- =========================================
     CHECKOUT SECTION
========================================= -->

<section class="section checkout-section">

    <div class="container">

        <div class="checkout-breadcrumb">

            <a href="index.php">Home</a>

            <span>/</span>

            <a href="cart.php">Cart</a>

            <span>/</span>

            <strong>Checkout</strong>

        </div>


        <?php if (!empty($errors)): ?>

            <div class="checkout-errors">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?= htmlspecialchars($error) ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            class="checkout-form"
        >

            <div class="checkout-layout">


                <!-- =================================
                     CUSTOMER DETAILS
                ================================= -->

                <div class="checkout-details">

                    <div class="checkout-box">

                        <div class="checkout-box-heading">

                            <span>01</span>

                            <h2>
                                Billing Details
                            </h2>

                        </div>


                        <div class="form-grid">


                            <div class="form-group full">

                                <label>
                                    Full Name *
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    value="<?= htmlspecialchars(
                                        $_POST["name"]
                                        ?? $user["name"]
                                    ) ?>"
                                    required
                                >

                            </div>


                            <div class="form-group">

                                <label>
                                    Email Address *
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    value="<?= htmlspecialchars(
                                        $_POST["email"]
                                        ?? $user["email"]
                                    ) ?>"
                                    required
                                >

                            </div>


                            <div class="form-group">

                                <label>
                                    Phone Number *
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    value="<?= htmlspecialchars(
                                        $_POST["phone"]
                                        ?? $user["phone"]
                                        ?? ""
                                    ) ?>"
                                    required
                                >

                            </div>


                            <div class="form-group full">

                                <label>
                                    Address *
                                </label>

                                <textarea
                                    name="address"
                                    rows="4"
                                    required
                                ><?= htmlspecialchars(
                                    $_POST["address"]
                                    ?? $user["address"]
                                    ?? ""
                                ) ?></textarea>

                            </div>


                            <div class="form-group">

                                <label>
                                    City *
                                </label>

                                <input
                                    type="text"
                                    name="city"
                                    value="<?= htmlspecialchars(
                                        $_POST["city"]
                                        ?? $user["city"]
                                        ?? ""
                                    ) ?>"
                                    required
                                >

                            </div>


                            <div class="form-group">

                                <label>
                                    State *
                                </label>

                                <input
                                    type="text"
                                    name="state"
                                    value="<?= htmlspecialchars(
                                        $_POST["state"]
                                        ?? $user["state"]
                                        ?? ""
                                    ) ?>"
                                    required
                                >

                            </div>


                            <div class="form-group">

                                <label>
                                    Pincode *
                                </label>

                                <input
                                    type="text"
                                    name="pincode"
                                    maxlength="10"
                                    value="<?= htmlspecialchars(
                                        $_POST["pincode"]
                                        ?? $user["pincode"]
                                        ?? ""
                                    ) ?>"
                                    required
                                >

                            </div>

                        </div>

                    </div>


                    <!-- PAYMENT -->

                    <div class="checkout-box">

                        <div class="checkout-box-heading">

                            <span>02</span>

                            <h2>
                                Payment Method
                            </h2>

                        </div>


                        <label class="payment-option">

                            <input
                                type="radio"
                                name="payment_method"
                                value="COD"
                                checked
                            >

                            <div>

                                <strong>
                                    Cash on Delivery
                                </strong>

                                <p>
                                    Pay when your jewellery order is delivered.
                                </p>

                            </div>

                        </label>

                    </div>

                </div>


                <!-- =================================
                     ORDER SUMMARY
                ================================= -->

                <aside class="checkout-summary">

                    <div class="checkout-summary-box">

                        <span class="summary-label">
                            YOUR ORDER
                        </span>

                        <h2>
                            Order Summary
                        </h2>


                        <div class="checkout-products">

                            <?php foreach ($cartItems as $item): ?>

                                <?php

                                $image = !empty($item["image"])
                                    ? "uploads/products/" . ltrim($item["image"], "/\\")
                                    : "assets/images/about.png";

                                ?>

                                <div class="checkout-product">

                                    <div class="checkout-product-image">

                                        <img
                                            src="<?= htmlspecialchars($image) ?>"
                                            alt="<?= htmlspecialchars($item["name"]) ?>"
                                        >

                                        <span>
                                            <?= (int)$item["quantity"] ?>
                                        </span>

                                    </div>


                                    <div class="checkout-product-info">

                                        <h3>
                                            <?= htmlspecialchars($item["name"]) ?>
                                        </h3>

                                        <p>
                                            ₹<?= number_format(
                                                $item["unit_price"],
                                                2
                                            ) ?>
                                        </p>

                                    </div>


                                    <strong>
                                        ₹<?= number_format(
                                            $item["line_total"],
                                            2
                                        ) ?>
                                    </strong>

                                </div>

                            <?php endforeach; ?>

                        </div>


                        <div class="checkout-total-row">

                            <span>
                                Subtotal
                            </span>

                            <strong>
                                ₹<?= number_format(
                                    $subtotal,
                                    2
                                ) ?>
                            </strong>

                        </div>


                        <div class="checkout-total-row">

                            <span>
                                Shipping
                            </span>

                            <strong>

                                <?php if ($shipping == 0): ?>

                                    Free

                                <?php else: ?>

                                    ₹<?= number_format(
                                        $shipping,
                                        2
                                    ) ?>

                                <?php endif; ?>

                            </strong>

                        </div>


                        <div class="checkout-grand-total">

                            <span>
                                Total
                            </span>

                            <strong>
                                ₹<?= number_format(
                                    $grandTotal,
                                    2
                                ) ?>
                            </strong>

                        </div>


                        <button
                            type="submit"
                            class="place-order-btn"
                        >
                            Place Order
                        </button>


                        <p class="secure-note">
                            🔒 Your order information is secure.
                        </p>

                    </div>

                </aside>

            </div>

        </form>

    </div>

</section>


<style>

/* =========================================
   CHECKOUT HERO
========================================= */

.checkout-hero {

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
        url("https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=2000&q=85")
        center/cover no-repeat;
}


.checkout-hero-content {

    text-align: center;

    max-width: 700px;

    margin: auto;
}


.checkout-hero-content span {

    color: #d3ad62;

    font-size: 11px;

    letter-spacing: 4px;
}


.checkout-hero-content h1 {

    font-family: "Cormorant Garamond", serif;

    font-size: clamp(48px, 7vw, 72px);

    font-weight: 500;

    margin: 15px 0;
}


.checkout-hero-content p {

    color: rgba(255,255,255,.85);

    font-size: 14px;
}


/* =========================================
   BREADCRUMB
========================================= */

.checkout-breadcrumb {

    display: flex;

    gap: 10px;

    font-size: 12px;

    margin-bottom: 35px;

    color: #999;
}


.checkout-breadcrumb a {

    color: #555;
}


.checkout-breadcrumb strong {

    color: #171717;

    font-weight: 500;
}


/* =========================================
   ERRORS
========================================= */

.checkout-errors {

    margin-bottom: 25px;

    padding: 15px 20px;

    background: #fff1f1;

    border-left: 3px solid #a33;
}


.checkout-errors p {

    margin: 4px 0;

    color: #8b2b2b;

    font-size: 13px;
}


/* =========================================
   CHECKOUT LAYOUT
========================================= */

.checkout-layout {

    display: grid;

    grid-template-columns: 1fr 390px;

    gap: 40px;

    align-items: start;
}


/* =========================================
   CHECKOUT BOX
========================================= */

.checkout-box {

    border: 1px solid #e6e1d8;

    padding: 30px;

    margin-bottom: 25px;

    background: #fff;
}


.checkout-box-heading {

    display: flex;

    align-items: center;

    gap: 15px;

    margin-bottom: 28px;

    padding-bottom: 18px;

    border-bottom: 1px solid #e6e1d8;
}


.checkout-box-heading span {

    color: var(--gold);

    font-size: 12px;

    letter-spacing: 2px;
}


.checkout-box-heading h2 {

    font-family: "Cormorant Garamond", serif;

    font-size: 30px;

    margin: 0;

    font-weight: 600;
}


/* =========================================
   FORM
========================================= */

.form-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 20px;
}


.form-group {

    display: flex;

    flex-direction: column;

    gap: 8px;
}


.form-group.full {

    grid-column: 1 / -1;
}


.form-group label {

    color: #555;

    font-size: 11px;

    letter-spacing: .5px;
}


.form-group input,
.form-group textarea {

    width: 100%;

    border: 1px solid #ddd;

    outline: none;

    padding: 13px 14px;

    background: #fff;

    color: #171717;

    font-family: inherit;

    font-size: 13px;

    transition: .25s ease;
}


.form-group textarea {

    resize: vertical;

    min-height: 110px;
}


.form-group input:focus,
.form-group textarea:focus {

    border-color: var(--gold);

    box-shadow: 0 0 0 2px rgba(176,138,69,.08);
}


/* =========================================
   PAYMENT
========================================= */

.payment-option {

    display: flex;

    align-items: flex-start;

    gap: 15px;

    padding: 18px;

    border: 1px solid #ddd;

    cursor: pointer;

    background: #faf8f3;
}


.payment-option input {

    margin-top: 4px;

    accent-color: var(--gold);
}


.payment-option strong {

    display: block;

    margin-bottom: 5px;

    font-size: 13px;
}


.payment-option p {

    margin: 0;

    color: #777;

    font-size: 11px;

    line-height: 1.6;
}


/* =========================================
   SUMMARY
========================================= */

.checkout-summary {

    position: sticky;

    top: 110px;
}


.checkout-summary-box {

    background: #faf8f3;

    border: 1px solid #e5dfd4;

    padding: 30px;
}


.checkout-summary-box .summary-label {

    color: var(--gold);

    font-size: 10px;

    letter-spacing: 3px;
}


.checkout-summary-box h2 {

    font-family: "Cormorant Garamond", serif;

    font-size: 34px;

    font-weight: 600;

    margin: 8px 0 25px;
}


/* =========================================
   PRODUCTS
========================================= */

.checkout-products {

    border-top: 1px solid #ddd;

    border-bottom: 1px solid #ddd;

    padding: 5px 0;
}


.checkout-product {

    display: grid;

    grid-template-columns: 55px 1fr auto;

    gap: 12px;

    align-items: center;

    padding: 13px 0;
}


.checkout-product-image {

    width: 55px;

    height: 65px;

    position: relative;

    background: #eee;

}


.checkout-product-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;
}


.checkout-product-image span {

    position: absolute;

    top: -7px;

    right: -7px;

    width: 20px;

    height: 20px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #171717;

    color: #fff;

    border-radius: 50%;

    font-size: 9px;
}


.checkout-product-info {

    min-width: 0;
}


.checkout-product-info h3 {

    font-family: "Cormorant Garamond", serif;

    font-size: 18px;

    margin: 0 0 3px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


.checkout-product-info p {

    margin: 0;

    color: #777;

    font-size: 11px;
}


.checkout-product > strong {

    font-size: 12px;

    white-space: nowrap;
}


/* =========================================
   TOTAL
========================================= */

.checkout-total-row {

    display: flex;

    justify-content: space-between;

    padding: 13px 0;

    border-bottom: 1px solid #e2ddd4;

    color: #777;

    font-size: 12px;
}


.checkout-total-row strong {

    color: #171717;

    font-weight: 500;
}


.checkout-grand-total {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 20px 0;

    border-top: 1px solid #171717;

    margin-top: 10px;
}


.checkout-grand-total span {

    font-size: 14px;

    font-weight: 600;
}


.checkout-grand-total strong {

    font-size: 20px;

    font-weight: 600;
}


/* =========================================
   PLACE ORDER
========================================= */

.place-order-btn {

    width: 100%;

    border: none;

    background: #171717;

    color: #fff;

    padding: 16px;

    cursor: pointer;

    font-size: 10px;

    letter-spacing: 2px;

    text-transform: uppercase;

    transition: .25s ease;
}


.place-order-btn:hover {

    background: var(--gold);
}


.secure-note {

    text-align: center;

    margin: 15px 0 0;

    color: #888;

    font-size: 10px;
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 1000px) {

    .checkout-layout {

        grid-template-columns: 1fr;

    }


    .checkout-summary {

        position: static;

    }

}


@media (max-width: 650px) {

    .checkout-box {

        padding: 22px;

    }


    .form-grid {

        grid-template-columns: 1fr;

    }


    .form-group.full {

        grid-column: auto;

    }

}


@media (max-width: 500px) {

    .checkout-hero {

        min-height: 280px;

    }


    .checkout-hero-content h1 {

        font-size: 45px;

    }


    .checkout-summary-box {

        padding: 22px;

    }

}

</style>


<?php require_once "includes/footer.php"; ?>

