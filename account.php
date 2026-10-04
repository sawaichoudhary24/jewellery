
<?php

session_start();

require_once "db.php";

/* LOGIN CHECK */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?redirect=account.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$message = "";
$error = "";


/* =========================
   UPDATE PROFILE
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_profile"])) {

    $name    = trim($_POST["name"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $phone   = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $city    = trim($_POST["city"] ?? "");
    $state   = trim($_POST["state"] ?? "");
    $pincode = trim($_POST["pincode"] ?? "");

    if ($name === "" || $email === "") {

        $error = "Name and email are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /* CHECK EMAIL */
        $check = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ");

        $check->bind_param(
            "si",
            $email,
            $userId
        );

        $check->execute();

        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {

            $error = "This email address is already registered.";

        } else {

            $stmt = $conn->prepare("
                UPDATE users
                SET
                    name = ?,
                    email = ?,
                    phone = ?,
                    address = ?,
                    city = ?,
                    state = ?,
                    pincode = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "sssssssi",
                $name,
                $email,
                $phone,
                $address,
                $city,
                $state,
                $pincode,
                $userId
            );

            if ($stmt->execute()) {

                $_SESSION["user_name"] = $name;
                $_SESSION["user_email"] = $email;

                $message = "Profile updated successfully.";

            } else {

                $error = "Unable to update profile.";
            }
        }
    }
}


/* =========================
   GET USER
========================= */

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        email,
        phone,
        address,
        city,
        state,
        pincode,
        created_at
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


/* =========================
   GET ORDERS
========================= */

$orderStmt = $conn->prepare("
    SELECT
        id,
        order_number,
        total,
        payment_method,
        payment_status,
        order_status,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY id DESC
");

$orderStmt->bind_param("i", $userId);
$orderStmt->execute();

$orders = $orderStmt->get_result();


/* =========================
   ORDER COUNT
========================= */

$countStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE user_id = ?
");

$countStmt->bind_param("i", $userId);
$countStmt->execute();

$countResult = $countStmt->get_result();
$orderCount = (int) $countResult->fetch_assoc()["total"];

?>

<?php require_once "includes/header.php"; ?>


<style>

.account-page {
    background: #faf8f3;
    min-height: 100vh;
}


/* =========================
   HERO
========================= */

.account-hero {

    min-height: 300px;

    display: flex;
    align-items: center;
    justify-content: center;

    text-align: center;

    color: #fff;

    background:
        linear-gradient(
            rgba(20,20,20,.68),
            rgba(20,20,20,.68)
        ),
        url("assets/images/account-banner.jpg")
        center/cover no-repeat;
}

.account-hero h1 {

    font-family: "Cormorant Garamond", serif;

    font-size: 58px;

    font-weight: 500;

    margin: 0 0 8px;
}

.account-hero p {

    margin: 0;

    font-size: 13px;

    letter-spacing: 2px;
}


/* =========================
   CONTAINER
========================= */

.account-container {

    max-width: 1200px;

    margin: auto;

    padding: 70px 20px;
}


/* =========================
   ALERT
========================= */

.alert {

    padding: 14px 18px;

    margin-bottom: 25px;

    font-size: 14px;

    border-radius: 2px;
}

.alert-success {

    background: #edf8ef;

    color: #26753a;

    border: 1px solid #c9e8d0;
}

.alert-error {

    background: #fff0f0;

    color: #a52a2a;

    border: 1px solid #efcccc;
}


/* =========================
   TOP CARDS
========================= */

.account-cards {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;

    margin-bottom: 45px;
}

.account-card {

    background: #fff;

    border: 1px solid #eee;

    padding: 25px;
}

.account-card span {

    display: block;

    color: #888;

    font-size: 12px;

    text-transform: uppercase;

    letter-spacing: 1px;

    margin-bottom: 8px;
}

.account-card strong {

    font-family:
        "Cormorant Garamond",
        serif;

    font-size: 30px;

    font-weight: 500;

    color: #171717;
}


/* =========================
   MAIN GRID
========================= */

.account-grid {

    display: grid;

    grid-template-columns:
        360px 1fr;

    gap: 30px;

    align-items: start;
}


/* =========================
   BOX
========================= */

.account-box {

    background: #fff;

    border: 1px solid #eee;

    padding: 30px;
}

.account-box h2 {

    font-family:
        "Cormorant Garamond",
        serif;

    font-size: 34px;

    font-weight: 500;

    margin: 0 0 25px;
}


/* =========================
   PROFILE FORM
========================= */

.form-group {

    margin-bottom: 18px;
}

.form-group label {

    display: block;

    font-size: 13px;

    font-weight: 600;

    color: #333;

    margin-bottom: 7px;
}

.form-group input,
.form-group textarea {

    width: 100%;

    padding: 12px 13px;

    border: 1px solid #ddd;

    outline: none;

    font-family: inherit;

    font-size: 14px;
}

.form-group textarea {

    height: 90px;

    resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus {

    border-color: #b08a45;
}


/* =========================
   BUTTON
========================= */

.gold-btn {

    border: none;

    background: #171717;

    color: #fff;

    padding: 13px 24px;

    cursor: pointer;

    font-size: 12px;

    letter-spacing: 1px;

    transition: .3s;
}

.gold-btn:hover {

    background: #b08a45;
}


/* =========================
   ORDERS
========================= */

.orders-box {

    overflow-x: auto;
}

.orders-table {

    width: 100%;

    border-collapse: collapse;

    min-width: 700px;
}

.orders-table th {

    background: #171717;

    color: #fff;

    padding: 13px;

    text-align: left;

    font-size: 12px;

    font-weight: 500;
}

.orders-table td {

    padding: 14px 13px;

    border-bottom: 1px solid #eee;

    font-size: 13px;

    color: #555;
}

.orders-table tr:hover td {

    background: #fcfcfc;
}

.order-number {

    color: #171717;

    font-weight: 600;
}


/* =========================
   STATUS
========================= */

.status {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 11px;

    text-transform: capitalize;

    font-weight: 600;
}

.status.pending {

    background: #fff3cd;

    color: #856404;
}

.status.confirmed {

    background: #dbeafe;

    color: #1d4ed8;
}

.status.processing {

    background: #e0e7ff;

    color: #4338ca;
}

.status.shipped {

    background: #d1fae5;

    color: #047857;
}

.status.delivered {

    background: #dcfce7;

    color: #166534;
}

.status.cancelled {

    background: #fee2e2;

    color: #b91c1c;
}


/* =========================
   LOGOUT
========================= */

.logout-btn {

    display: inline-block;

    margin-top: 20px;

    color: #b00020;

    border: 1px solid #b00020;

    padding: 10px 18px;

    text-decoration: none;

    font-size: 12px;

    transition: .3s;
}

.logout-btn:hover {

    background: #b00020;

    color: #fff;
}


/* =========================
   EMPTY ORDERS
========================= */

.empty-orders {

    text-align: center;

    padding: 45px 20px;

    color: #777;
}

.empty-orders h3 {

    font-family:
        "Cormorant Garamond",
        serif;

    font-size: 30px;

    font-weight: 500;

    color: #222;

    margin: 0 0 10px;
}

.shop-btn {

    display: inline-block;

    margin-top: 15px;

    background: #171717;

    color: #fff;

    text-decoration: none;

    padding: 12px 22px;

    font-size: 12px;

    letter-spacing: 1px;
}

.shop-btn:hover {

    background: #b08a45;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 900px) {

    .account-grid {

        grid-template-columns: 1fr;
    }

    .account-cards {

        grid-template-columns:
            repeat(2, 1fr);
    }
}

@media (max-width: 600px) {

    .account-hero {

        min-height: 250px;
    }

    .account-hero h1 {

        font-size: 43px;
    }

    .account-container {

        padding: 50px 15px;
    }

    .account-cards {

        grid-template-columns: 1fr;
    }

    .account-box {

        padding: 22px 18px;
    }

    .account-box h2 {

        font-size: 30px;
    }
}

</style>


<main class="account-page">


    <!-- HERO -->

    <section class="account-hero">

        <div>

            <h1>My Account</h1>

            <p>
                WELCOME BACK, <?= htmlspecialchars($user["name"]) ?>
            </p>

        </div>

    </section>


    <!-- CONTENT -->

    <section class="account-container">


        <!-- ALERT -->

        <?php if ($message): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <!-- SUMMARY CARDS -->

        <div class="account-cards">

            <div class="account-card">

                <span>
                    Customer
                </span>

                <strong>
                    <?= htmlspecialchars($user["name"]) ?>
                </strong>

            </div>


            <div class="account-card">

                <span>
                    Total Orders
                </span>

                <strong>
                    <?= $orderCount ?>
                </strong>

            </div>


            <div class="account-card">

                <span>
                    Member Since
                </span>

                <strong>
                    <?= date(
                        "M Y",
                        strtotime($user["created_at"])
                    ) ?>
                </strong>

            </div>

        </div>


        <!-- MAIN -->

        <div class="account-grid">


            <!-- PROFILE -->

            <div class="account-box">

                <h2>
                    My Profile
                </h2>


                <form method="POST">

                    <input
                        type="hidden"
                        name="update_profile"
                        value="1"
                    >


                    <div class="form-group">

                        <label>
                            Full Name *
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="<?= htmlspecialchars(
                                $user["name"]
                            ) ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Email *
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars(
                                $user["email"]
                            ) ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="<?= htmlspecialchars(
                                $user["phone"] ?? ""
                            ) ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Address
                        </label>

                        <textarea
                            name="address"
                        ><?= htmlspecialchars(
                            $user["address"] ?? ""
                        ) ?></textarea>

                    </div>


                    <div class="form-group">

                        <label>
                            City
                        </label>

                        <input
                            type="text"
                            name="city"
                            value="<?= htmlspecialchars(
                                $user["city"] ?? ""
                            ) ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            State
                        </label>

                        <input
                            type="text"
                            name="state"
                            value="<?= htmlspecialchars(
                                $user["state"] ?? ""
                            ) ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Pincode
                        </label>

                        <input
                            type="text"
                            name="pincode"
                            value="<?= htmlspecialchars(
                                $user["pincode"] ?? ""
                            ) ?>"
                        >

                    </div>


                    <button
                        type="submit"
                        class="gold-btn"
                    >
                        UPDATE PROFILE
                    </button>

                </form>


                <a
                    href="logout.php"
                    class="logout-btn"
                    onclick="
                        return confirm(
                            'Are you sure you want to logout?'
                        );
                    "
                >
                    LOGOUT
                </a>

            </div>


            <!-- ORDERS -->

            <div class="account-box orders-box">

                <h2>
                    My Orders
                </h2>


                <?php if ($orders && $orders->num_rows > 0): ?>

                    <table class="orders-table">

                        <thead>

                            <tr>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Payment
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php while (
                                $order =
                                $orders->fetch_assoc()
                            ): ?>

                                <tr>

                                    <td>

                                        <span class="order-number">

                                            <?= htmlspecialchars(
                                                $order[
                                                    "order_number"
                                                ]
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?= date(
                                            "d M Y",
                                            strtotime(
                                                $order[
                                                    "created_at"
                                                ]
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <strong>

                                            ₹<?= number_format(
                                                (float)
                                                $order["total"],
                                                2
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $order[
                                                "payment_method"
                                            ]
                                        ) ?>

                                        <br>

                                        <span
                                            class="status
                                            <?= htmlspecialchars(
                                                $order[
                                                    "payment_status"
                                                ]
                                            ) ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $order[
                                                    "payment_status"
                                                ]
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span
                                            class="status
                                            <?= htmlspecialchars(
                                                $order[
                                                    "order_status"
                                                ]
                                            ) ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $order[
                                                    "order_status"
                                                ]
                                            ) ?>

                                        </span>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                <?php else: ?>


                    <div class="empty-orders">

                        <h3>
                            No Orders Yet
                        </h3>

                        <p>
                            You haven't placed any orders yet.
                        </p>

                        <a
                            href="shop.php"
                            class="shop-btn"
                        >
                            START SHOPPING
                        </a>

                    </div>


                <?php endif; ?>

            </div>


        </div>

    </section>

</main>


<?php require_once "includes/footer.php"; ?>
