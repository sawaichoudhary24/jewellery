
<?php

session_start();

require_once "db.php";

/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];


/* =========================================
   GET ORDER NUMBER
========================================= */

$orderNumber = trim($_GET["order"] ?? "");

if ($orderNumber === "") {
    header("Location: index.php");
    exit;
}


/* =========================================
   GET ORDER
========================================= */

$stmt = $conn->prepare("
    SELECT
        id,
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
        order_status,
        created_at
    FROM orders
    WHERE order_number = ?
    AND user_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "si",
    $orderNumber,
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$order = $result->fetch_assoc();


/* =========================================
   ORDER NOT FOUND
========================================= */

if (!$order) {
    header("Location: index.php");
    exit;
}


/* =========================================
   GET ORDER ITEMS
========================================= */

$orderId = (int) $order["id"];

$stmt = $conn->prepare("
    SELECT
        product_name,
        price,
        quantity,
        total
    FROM order_items
    WHERE order_id = ?
    ORDER BY id ASC
");

$stmt->bind_param(
    "i",
    $orderId
);

$stmt->execute();

$itemsResult = $stmt->get_result();

$orderItems = [];

while ($item = $itemsResult->fetch_assoc()) {
    $orderItems[] = $item;
}

?>

<?php require_once "includes/header.php"; ?>


<!-- =========================================
     SUCCESS HERO
========================================= -->

<section class="success-hero">

    <div class="container">

        <div class="success-content">

            <div class="success-icon">
                ✓
            </div>

            <span>
                ORDER CONFIRMED
            </span>

            <h1>
                Thank You!
            </h1>

            <p>
                Your jewellery order has been placed successfully.
            </p>

        </div>

    </div>

</section>


<!-- =========================================
     ORDER SUCCESS
========================================= -->

<section class="section success-section">

    <div class="container">

        <div class="success-wrapper">


            <!-- =================================
                 CONFIRMATION MESSAGE
            ================================= -->

            <div class="confirmation-box">

                <span class="confirmation-label">
                    ORDER NUMBER
                </span>

                <h2>
                    <?= htmlspecialchars($order["order_number"]) ?>
                </h2>

                <p>
                    Thank you,
                    <strong>
                        <?= htmlspecialchars($order["name"]) ?>
                    </strong>.
                    We have received your order and will process it shortly.
                </p>

                <div class="confirmation-actions">

                    <a
                        href="shop.php"
                        class="btn"
                    >
                        Continue Shopping
                    </a>

                    <a
                        href="index.php"
                        class="success-home-btn"
                    >
                        Back to Home
                    </a>

                </div>

            </div>


            <!-- =================================
                 ORDER DETAILS
            ================================= -->

            <div class="order-success-grid">


                <!-- ORDER ITEMS -->

                <div class="success-card">

                    <div class="success-card-heading">

                        <span>01</span>

                        <h3>
                            Order Items
                        </h3>

                    </div>


                    <div class="success-items">

                        <?php foreach ($orderItems as $item): ?>

                            <div class="success-item">

                                <div>

                                    <h4>
                                        <?= htmlspecialchars(
                                            $item["product_name"]
                                        ) ?>
                                    </h4>

                                    <p>
                                        ₹<?= number_format(
                                            (float)$item["price"],
                                            2
                                        ) ?>
                                        ×
                                        <?= (int)$item["quantity"] ?>
                                    </p>

                                </div>

                                <strong>
                                    ₹<?= number_format(
                                        (float)$item["total"],
                                        2
                                    ) ?>
                                </strong>

                            </div>

                        <?php endforeach; ?>

                    </div>


                    <div class="success-total-row">

                        <span>
                            Subtotal
                        </span>

                        <strong>
                            ₹<?= number_format(
                                (float)$order["subtotal"],
                                2
                            ) ?>
                        </strong>

                    </div>


                    <div class="success-total-row">

                        <span>
                            Shipping
                        </span>

                        <strong>

                            <?php if (
                                (float)$order["shipping"] == 0
                            ): ?>

                                Free

                            <?php else: ?>

                                ₹<?= number_format(
                                    (float)$order["shipping"],
                                    2
                                ) ?>

                            <?php endif; ?>

                        </strong>

                    </div>


                    <div class="success-grand-total">

                        <span>
                            Grand Total
                        </span>

                        <strong>
                            ₹<?= number_format(
                                (float)$order["total"],
                                2
                            ) ?>
                        </strong>

                    </div>

                </div>


                <!-- DELIVERY DETAILS -->

                <div class="success-card">

                    <div class="success-card-heading">

                        <span>02</span>

                        <h3>
                            Delivery Details
                        </h3>

                    </div>


                    <div class="delivery-details">

                        <div>

                            <span>
                                Customer
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $order["name"]
                                ) ?>
                            </strong>

                        </div>


                        <div>

                            <span>
                                Email
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $order["email"]
                                ) ?>
                            </strong>

                        </div>


                        <div>

                            <span>
                                Phone
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $order["phone"]
                                ) ?>
                            </strong>

                        </div>


                        <div>

                            <span>
                                Address
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $order["address"]
                                ) ?>
                            </strong>

                        </div>


                        <div>

                            <span>
                                City
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $order["city"]
                                ) ?>
                            </strong>

                        </div>


                        <div>

                            <span>
                                State
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $order["state"]
                                ) ?>
                            </strong>

                        </div>


                        <div>

                            <span>
                                Pincode
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $order["pincode"]
                                ) ?>
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- PAYMENT -->

                <div class="success-card">

                    <div class="success-card-heading">

                        <span>03</span>

                        <h3>
                            Payment
                        </h3>

                    </div>


                    <div class="payment-status-box">

                        <span>
                            Payment Method
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $order["payment_method"]
                            ) ?>
                        </strong>

                        <small>
                            Payment will be collected at the time of delivery.
                        </small>

                    </div>

                </div>


                <!-- ORDER STATUS -->

                <div class="success-card">

                    <div class="success-card-heading">

                        <span>04</span>

                        <h3>
                            Order Status
                        </h3>

                    </div>


                    <div class="order-status-box">

                        <div class="status-circle">
                            ✓
                        </div>

                        <div>

                            <strong>
                                Order Placed
                            </strong>

                            <p>
                                <?= date(
                                    "d M Y, h:i A",
                                    strtotime(
                                        $order["created_at"]
                                    )
                                ) ?>
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================
                 SUPPORT
            ================================= -->

            <div class="success-support">

                <span>
                    NEED HELP?
                </span>

                <h3>
                    We're here for you.
                </h3>

                <p>
                    If you have any questions about your order,
                    please contact our customer support team.
                </p>

                <a href="contact.php">
                    Contact Us →
                </a>

            </div>

        </div>

    </div>

</section>


<style>

/* =========================================
   SUCCESS HERO
========================================= */

.success-hero {

    min-height: 390px;

    display: flex;

    align-items: center;

    position: relative;

    color: #fff;

    background:
        linear-gradient(
            rgba(20,20,20,.66),
            rgba(20,20,20,.66)
        ),
        url("https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=2000&q=85")
        center/cover no-repeat;
}


.success-content {

    text-align: center;

    max-width: 700px;

    margin: auto;
}


.success-icon {

    width: 65px;

    height: 65px;

    border: 1px solid #d3ad62;

    color: #d3ad62;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: 0 auto 20px;

    font-size: 27px;
}


.success-content > span {

    color: #d3ad62;

    font-size: 11px;

    letter-spacing: 4px;
}


.success-content h1 {

    font-family: "Cormorant Garamond", serif;

    font-size: clamp(50px, 7vw, 75px);

    font-weight: 500;

    margin: 12px 0;
}


.success-content p {

    color: rgba(255,255,255,.85);

    font-size: 14px;
}


/* =========================================
   SUCCESS WRAPPER
========================================= */

.success-wrapper {

    max-width: 1100px;

    margin: auto;
}


/* =========================================
   CONFIRMATION
========================================= */

.confirmation-box {

    text-align: center;

    padding: 45px 30px;

    border: 1px solid #e5dfd4;

    background: #faf8f3;

    margin-bottom: 35px;
}


.confirmation-label {

    color: var(--gold);

    font-size: 10px;

    letter-spacing: 3px;
}


.confirmation-box h2 {

    font-family: "Cormorant Garamond", serif;

    font-size: 36px;

    font-weight: 600;

    margin: 8px 0 15px;
}


.confirmation-box p {

    color: #666;

    font-size: 13px;

    line-height: 1.8;

    max-width: 650px;

    margin: auto;
}


.confirmation-actions {

    display: flex;

    justify-content: center;

    gap: 12px;

    margin-top: 25px;

    flex-wrap: wrap;
}


.success-home-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 14px 25px;

    border: 1px solid #171717;

    color: #171717;

    font-size: 10px;

    letter-spacing: 1.5px;

    text-transform: uppercase;

    transition: .25s ease;
}


.success-home-btn:hover {

    background: #171717;

    color: #fff;
}


/* =========================================
   GRID
========================================= */

.order-success-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 25px;
}


.success-card {

    border: 1px solid #e5dfd4;

    background: #fff;

    padding: 28px;
}


.success-card-heading {

    display: flex;

    align-items: center;

    gap: 13px;

    padding-bottom: 17px;

    margin-bottom: 18px;

    border-bottom: 1px solid #e6e1d8;
}


.success-card-heading span {

    color: var(--gold);

    font-size: 11px;

    letter-spacing: 2px;
}


.success-card-heading h3 {

    font-family: "Cormorant Garamond", serif;

    font-size: 26px;

    margin: 0;

    font-weight: 600;
}


/* =========================================
   ITEMS
========================================= */

.success-item {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding: 13px 0;

    border-bottom: 1px solid #eee;
}


.success-item h4 {

    margin: 0 0 4px;

    font-family: "Cormorant Garamond", serif;

    font-size: 19px;

    font-weight: 600;
}


.success-item p {

    margin: 0;

    color: #888;

    font-size: 11px;
}


.success-item > strong {

    white-space: nowrap;

    font-size: 12px;
}


.success-total-row {

    display: flex;

    justify-content: space-between;

    padding: 12px 0;

    color: #777;

    font-size: 12px;
}


.success-grand-total {

    display: flex;

    justify-content: space-between;

    padding-top: 17px;

    margin-top: 5px;

    border-top: 1px solid #171717;

    font-size: 14px;
}


.success-grand-total strong {

    font-size: 18px;
}


/* =========================================
   DELIVERY DETAILS
========================================= */

.delivery-details > div {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding: 11px 0;

    border-bottom: 1px solid #eee;
}


.delivery-details span {

    color: #888;

    font-size: 11px;
}


.delivery-details strong {

    max-width: 60%;

    text-align: right;

    font-size: 12px;

    font-weight: 500;

    line-height: 1.6;
}


/* =========================================
   PAYMENT
========================================= */

.payment-status-box {

    background: #faf8f3;

    padding: 22px;
}


.payment-status-box span {

    display: block;

    color: #888;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: 1px;

    margin-bottom: 6px;
}


.payment-status-box strong {

    display: block;

    font-size: 17px;

    margin-bottom: 8px;
}


.payment-status-box small {

    color: #777;

    font-size: 11px;

    line-height: 1.6;
}


/* =========================================
   STATUS
========================================= */

.order-status-box {

    display: flex;

    align-items: center;

    gap: 15px;

    padding: 20px;

    background: #faf8f3;
}


.status-circle {

    width: 42px;

    height: 42px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #171717;

    color: #fff;

    font-size: 18px;
}


.order-status-box strong {

    display: block;

    font-size: 13px;

    margin-bottom: 4px;
}


.order-status-box p {

    margin: 0;

    color: #888;

    font-size: 11px;
}


/* =========================================
   SUPPORT
========================================= */

.success-support {

    text-align: center;

    padding: 45px 20px 10px;
}


.success-support > span {

    color: var(--gold);

    font-size: 10px;

    letter-spacing: 3px;
}


.success-support h3 {

    font-family: "Cormorant Garamond", serif;

    font-size: 34px;

    margin: 8px 0;
}


.success-support p {

    max-width: 550px;

    margin: 0 auto 15px;

    color: #777;

    font-size: 12px;

    line-height: 1.7;
}


.success-support a {

    color: #171717;

    font-size: 11px;

    text-decoration: underline;
}


.success-support a:hover {

    color: var(--gold);
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 750px) {

    .order-success-grid {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 500px) {

    .success-hero {

        min-height: 320px;

    }


    .success-content h1 {

        font-size: 48px;

    }


    .confirmation-box {

        padding: 35px 20px;

    }


    .success-card {

        padding: 22px;

    }


    .delivery-details > div {

        align-items: flex-start;

        flex-direction: column;

        gap: 5px;

    }


    .delivery-details strong {

        max-width: 100%;

        text-align: left;

    }

}

</style>


<?php require_once "includes/footer.php"; ?>
