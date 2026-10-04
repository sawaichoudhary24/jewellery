<?php

require_once "db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================
   FILTER VALUES
========================================= */

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");
$sort = $_GET["sort"] ?? "latest";

$page = max(1, (int)($_GET["page"] ?? 1));

$perPage = 12;

$offset = ($page - 1) * $perPage;


/* =========================================
   CATEGORY LIST
========================================= */

$categories = [];

$categoryQuery = $conn->query("
    SELECT *
    FROM categories
    WHERE status = 'active'
    ORDER BY name ASC
");

if ($categoryQuery) {

    while ($row = $categoryQuery->fetch_assoc()) {
        $categories[] = $row;
    }

}


/* =========================================
   BUILD WHERE
========================================= */

$where = [
    "p.status = 'active'"
];

$params = [];
$types = "";


/* SEARCH */

if ($search !== "") {

    $where[] = "
        (
            p.name LIKE ?
            OR p.description LIKE ?
            OR p.short_description LIKE ?
            OR p.sku LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ssss";
}


/* CATEGORY */

if ($category !== "") {

    $where[] = "c.slug = ?";

    $params[] = $category;

    $types .= "s";
}


$whereSQL = implode(" AND ", $where);


/* =========================================
   SORT
========================================= */

$orderBy = "p.id DESC";

switch ($sort) {

    case "price_low":
        $orderBy = "COALESCE(NULLIF(p.sale_price, 0), p.price) ASC";
        break;

    case "price_high":
        $orderBy = "COALESCE(NULLIF(p.sale_price, 0), p.price) DESC";
        break;

    case "name":
        $orderBy = "p.name ASC";
        break;

    case "oldest":
        $orderBy = "p.id ASC";
        break;

    default:
        $orderBy = "p.id DESC";
}


/* =========================================
   TOTAL PRODUCTS
========================================= */

$countSQL = "
    SELECT COUNT(*) AS total
    FROM products p
    LEFT JOIN categories c
        ON p.category_id = c.id
    WHERE $whereSQL
";

$countStmt = $conn->prepare($countSQL);

if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();

$countResult = $countStmt->get_result();

$totalProducts = 0;

if ($countRow = $countResult->fetch_assoc()) {
    $totalProducts = (int)$countRow["total"];
}

$totalPages = max(
    1,
    (int)ceil($totalProducts / $perPage)
);


/* =========================================
   PRODUCT QUERY
========================================= */

$productSQL = "
    SELECT
        p.*,
        c.name AS category_name,
        c.slug AS category_slug
    FROM products p
    LEFT JOIN categories c
        ON p.category_id = c.id
    WHERE $whereSQL
    ORDER BY $orderBy
    LIMIT ? OFFSET ?
";


$productStmt = $conn->prepare($productSQL);

$productParams = $params;
$productTypes = $types . "ii";

$productParams[] = $perPage;
$productParams[] = $offset;

$productStmt->bind_param(
    $productTypes,
    ...$productParams
);

$productStmt->execute();

$productResult = $productStmt->get_result();

?>


<?php require_once "includes/header.php"; ?>


<!-- =========================================
     SHOP HERO
========================================= -->

<section class="shop-hero">

    <div class="shop-hero-overlay"></div>

    <div class="container">

        <div class="shop-hero-content">

            <span>
                OUR COLLECTION
            </span>

            <h1>
                Jewellery Collection
            </h1>

            <p>
                Discover timeless jewellery designed
                for every beautiful moment.
            </p>

        </div>

    </div>

</section>


<!-- =========================================
     SHOP
========================================= -->

<section class="section shop-section">

    <div class="container">


        <!-- BREADCRUMB -->

        <div class="shop-breadcrumb">

            <a href="index.php">
                Home
            </a>

            <span>
                /
            </span>

            <strong>
                Shop
            </strong>

        </div>


        <!-- SHOP TOP -->

        <div class="shop-top">

            <div>

                <span class="shop-result-count">

                    <?php if ($totalProducts > 0): ?>

                        Showing
                        <?= $offset + 1 ?>
                        -
                        <?= min($offset + $perPage, $totalProducts) ?>
                        of
                        <?= $totalProducts ?>

                    <?php else: ?>

                        No products found

                    <?php endif; ?>

                </span>

            </div>


            <div class="shop-sort">

                <label for="sort">
                    Sort By
                </label>

                <select
                    id="sort"
                    onchange="changeShopSort(this.value)"
                >

                    <option
                        value="latest"
                        <?= $sort === "latest" ? "selected" : "" ?>
                    >
                        Latest
                    </option>

                    <option
                        value="price_low"
                        <?= $sort === "price_low" ? "selected" : "" ?>
                    >
                        Price: Low to High
                    </option>

                    <option
                        value="price_high"
                        <?= $sort === "price_high" ? "selected" : "" ?>
                    >
                        Price: High to Low
                    </option>

                    <option
                        value="name"
                        <?= $sort === "name" ? "selected" : "" ?>
                    >
                        Name
                    </option>

                    <option
                        value="oldest"
                        <?= $sort === "oldest" ? "selected" : "" ?>
                    >
                        Oldest
                    </option>

                </select>

            </div>

        </div>


        <div class="shop-layout">


            <!-- =================================
                 SIDEBAR
            ================================= -->

            <aside class="shop-sidebar">


                <!-- SEARCH -->

                <div class="filter-box">

                    <h3>
                        Search Jewellery
                    </h3>

                    <form method="GET">

                        <?php if ($category !== ""): ?>

                            <input
                                type="hidden"
                                name="category"
                                value="<?= htmlspecialchars($category) ?>"
                            >

                        <?php endif; ?>


                        <?php if ($sort !== "latest"): ?>

                            <input
                                type="hidden"
                                name="sort"
                                value="<?= htmlspecialchars($sort) ?>"
                            >

                        <?php endif; ?>


                        <div class="shop-search">

                            <input
                                type="text"
                                name="search"
                                value="<?= htmlspecialchars($search) ?>"
                                placeholder="Search products..."
                            >

                            <button type="submit">
                                ⌕
                            </button>

                        </div>

                    </form>

                </div>


                <!-- CATEGORIES -->

                <div class="filter-box">

                    <h3>
                        Categories
                    </h3>

                    <ul class="category-filter">

                        <li>

                            <a
                                href="shop.php"
                                class="<?= $category === "" ? "active" : "" ?>"
                            >

                                <span>
                                    All Jewellery
                                </span>

                                <span>
                                    →
                                </span>

                            </a>

                        </li>


                        <?php foreach ($categories as $cat): ?>

                            <li>

                                <a
                                    href="shop.php?category=<?= urlencode($cat["slug"]) ?>"
                                    class="<?= $category === $cat["slug"] ? "active" : "" ?>"
                                >

                                    <span>
                                        <?= htmlspecialchars($cat["name"]) ?>
                                    </span>

                                    <span>
                                        →
                                    </span>

                                </a>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>


                <!-- INFO -->

                <div class="shop-sidebar-banner">

                    <div>

                        <span>
                            KHUSHBU
                        </span>

                        <h3>
                            Timeless<br>
                            Elegance
                        </h3>

                        <a href="about.php">
                            Discover More →
                        </a>

                    </div>

                </div>


            </aside>


            <!-- =================================
                 PRODUCTS
            ================================= -->

            <div class="shop-products-area">


                <?php if ($productResult->num_rows > 0): ?>


                    <div class="shop-product-grid">


                        <?php while ($product = $productResult->fetch_assoc()): ?>


                            <?php

                            $image = !empty($product["image"])
                                ? "uploads/products/" . $product["image"]
                                : "https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=700&q=85";


                            $price = (float)$product["price"];

                            $salePrice = !empty($product["sale_price"])
                                ? (float)$product["sale_price"]
                                : 0;

                            $finalPrice =
                                ($salePrice > 0 && $salePrice < $price)
                                ? $salePrice
                                : $price;

                            ?>


                            <div class="shop-product-card">


                                <!-- IMAGE -->

                                <div class="shop-product-image">

                                    <a
                                        href="shop-details.php?slug=<?= urlencode($product["slug"]) ?>"
                                    >

                                        <img
                                            src="<?= htmlspecialchars($image) ?>"
                                            alt="<?= htmlspecialchars($product["name"]) ?>"
                                            onerror="this.src='https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=700&q=85'"
                                        >

                                    </a>


                                    <?php if (
                                        $salePrice > 0 &&
                                        $salePrice < $price
                                    ): ?>

                                        <span class="shop-sale-badge">
                                            SALE
                                        </span>

                                    <?php endif; ?>


                                    <?php if ((int)$product["stock"] <= 0): ?>

                                        <span class="out-stock-badge">
                                            OUT OF STOCK
                                        </span>

                                    <?php endif; ?>


                                    <div class="shop-product-actions">

                                        <a
                                            href="shop-details.php?slug=<?= urlencode($product["slug"]) ?>"
                                            title="View Product"
                                        >
                                            ♡
                                        </a>

                                        <a
                                            href="shop-details.php?slug=<?= urlencode($product["slug"]) ?>"
                                            title="Quick View"
                                        >
                                            👁
                                        </a>

                                    </div>

                                </div>


                                <!-- INFO -->

                                <div class="shop-product-info">

                                    <span class="shop-product-category">

                                        <?= htmlspecialchars(
                                            $product["category_name"] ?? "Jewellery"
                                        ) ?>

                                    </span>


                                    <h3>

                                        <a
                                            href="shop-details.php?slug=<?= urlencode($product["slug"]) ?>"
                                        >

                                            <?= htmlspecialchars($product["name"]) ?>

                                        </a>

                                    </h3>


                                    <div class="shop-rating">

                                        <span>
                                            ★★★★★
                                        </span>

                                    </div>


                                    <div class="shop-product-price">

                                        <strong>
                                            ₹<?= number_format($finalPrice, 2) ?>
                                        </strong>


                                        <?php if (
                                            $salePrice > 0 &&
                                            $salePrice < $price
                                        ): ?>

                                            <del>
                                                ₹<?= number_format($price, 2) ?>
                                            </del>

                                        <?php endif; ?>

                                    </div>


                                    <?php if ((int)$product["stock"] > 0): ?>

                                        <?php if (isset($_SESSION["user_id"])): ?>

                                            <form
                                                method="POST"
                                                action="add-to-cart.php"
                                                class="shop-cart-form"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="product_id"
                                                    value="<?= (int)$product["id"] ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="quantity"
                                                    value="1"
                                                >

                                                <button
                                                    type="submit"
                                                    class="shop-add-cart"
                                                >
                                                    Add to Cart
                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <a
                                                href="login.php?redirect=shop.php"
                                                class="shop-add-cart"
                                            >
                                                Add to Cart
                                            </a>

                                        <?php endif; ?>

                                    <?php else: ?>

                                        <button
                                            class="shop-add-cart disabled"
                                            disabled
                                        >
                                            Out of Stock
                                        </button>

                                    <?php endif; ?>

                                </div>

                            </div>


                        <?php endwhile; ?>


                    </div>


                    <!-- =================================
                         PAGINATION
                    ================================= -->

                    <?php if ($totalPages > 1): ?>

                        <div class="shop-pagination">


                            <?php if ($page > 1): ?>

                                <a
                                    href="<?= htmlspecialchars(
                                        buildShopUrl(
                                            $search,
                                            $category,
                                            $sort,
                                            $page - 1
                                        )
                                    ) ?>"
                                >
                                    ←
                                </a>

                            <?php endif; ?>


                            <?php

                            $startPage = max(1, $page - 2);

                            $endPage = min(
                                $totalPages,
                                $page + 2
                            );

                            ?>


                            <?php for (
                                $i = $startPage;
                                $i <= $endPage;
                                $i++
                            ): ?>

                                <a
                                    href="<?= htmlspecialchars(
                                        buildShopUrl(
                                            $search,
                                            $category,
                                            $sort,
                                            $i
                                        )
                                    ) ?>"
                                    class="<?= $i === $page ? "active" : "" ?>"
                                >
                                    <?= $i ?>
                                </a>

                            <?php endfor; ?>


                            <?php if ($page < $totalPages): ?>

                                <a
                                    href="<?= htmlspecialchars(
                                        buildShopUrl(
                                            $search,
                                            $category,
                                            $sort,
                                            $page + 1
                                        )
                                    ) ?>"
                                >
                                    →
                                </a>

                            <?php endif; ?>


                        </div>

                    <?php endif; ?>


                <?php else: ?>


                    <div class="no-products">

                        <div class="no-products-icon">
                            ♢
                        </div>

                        <h2>
                            No Jewellery Found
                        </h2>

                        <p>
                            We couldn't find any products matching
                            your search or selected category.
                        </p>

                        <a
                            href="shop.php"
                            class="btn btn-dark"
                        >
                            View All Products
                        </a>

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</section>


<script>

function changeShopSort(value) {

    const url = new URL(window.location.href);

    url.searchParams.set("sort", value);

    url.searchParams.delete("page");

    window.location.href = url.toString();

}

</script>


<?php

require_once "includes/footer.php";


/* =========================================
   SHOP URL HELPER
========================================= */

function buildShopUrl(
    $search,
    $category,
    $sort,
    $page
) {

    $query = [];

    if ($search !== "") {
        $query["search"] = $search;
    }

    if ($category !== "") {
        $query["category"] = $category;
    }

    if ($sort !== "latest") {
        $query["sort"] = $sort;
    }

    if ($page > 1) {
        $query["page"] = $page;
    }

    return "shop.php?" . http_build_query($query);
}

?>