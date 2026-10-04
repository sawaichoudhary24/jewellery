<?php

require_once "db.php";

/* =========================
   ACTIVE CATEGORIES
========================= */

$categories = [];

$categoryQuery = $conn->query("
    SELECT *
    FROM categories
    WHERE status = 'active'
    ORDER BY id ASC
    LIMIT 6
");

if ($categoryQuery) {
    while ($row = $categoryQuery->fetch_assoc()) {
        $categories[] = $row;
    }
}


/* =========================
   FEATURED PRODUCTS
========================= */

$products = [];

$productQuery = $conn->query("
    SELECT
        p.*,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c
        ON p.category_id = c.id
    WHERE p.status = 'active'
    AND p.featured = 1
    ORDER BY p.id DESC
    LIMIT 8
");

if ($productQuery) {
    while ($row = $productQuery->fetch_assoc()) {
        $products[] = $row;
    }
}


/* =========================
   FALLBACK PRODUCTS
========================= */

if (empty($products)) {

    $productQuery = $conn->query("
        SELECT
            p.*,
            c.name AS category_name
        FROM products p
        LEFT JOIN categories c
            ON p.category_id = c.id
        WHERE p.status = 'active'
        ORDER BY p.id DESC
        LIMIT 8
    ");

    if ($productQuery) {
        while ($row = $productQuery->fetch_assoc()) {
            $products[] = $row;
        }
    }
}

?>

<?php require_once "includes/header.php"; ?>


<!-- =========================================
     HERO SECTION
========================================= -->

<section class="hero-section">

    <div class="hero-overlay"></div>

    <div class="container hero-content">

        <span class="hero-small-title">
            TIMELESS ELEGANCE
        </span>

        <h1>
            Jewellery That<br>
            Tells Your Story
        </h1>

        <p>
            Discover exquisite gold and diamond jewellery
            crafted to celebrate your most beautiful moments.
        </p>

        <div class="hero-buttons">

            <a href="shop.php" class="btn btn-gold">
                Explore Collection
            </a>

            <a href="about.php" class="btn btn-outline-light">
                Discover Our Story
            </a>

        </div>

    </div>

</section>


<!-- =========================================
     TRUST FEATURES
========================================= -->

<section class="trust-section">

    <div class="container trust-grid">

        <div class="trust-item">

            <div class="trust-icon">
                ♢
            </div>

            <div>
                <h3>
                    Authentic Jewellery
                </h3>

                <p>
                    Certified quality products
                </p>
            </div>

        </div>


        <div class="trust-item">

            <div class="trust-icon">
                ◇
            </div>

            <div>
                <h3>
                    Premium Craftsmanship
                </h3>

                <p>
                    Designed with perfection
                </p>
            </div>

        </div>


        <div class="trust-item">

            <div class="trust-icon">
                ♡
            </div>

            <div>
                <h3>
                    Secure Shopping
                </h3>

                <p>
                    Safe and trusted checkout
                </p>
            </div>

        </div>


        <div class="trust-item">

            <div class="trust-icon">
                ✦
            </div>

            <div>
                <h3>
                    Lifetime Support
                </h3>

                <p>
                    We're always here for you
                </p>
            </div>

        </div>

    </div>

</section>


<!-- =========================================
     CATEGORIES
========================================= -->

<section class="section categories-section">

    <div class="container">

        <div class="section-heading">

            <span>
                SHOP BY CATEGORY
            </span>

            <h2>
                Find Your Perfect Piece
            </h2>

            <p>
                Explore our carefully curated jewellery collections.
            </p>

        </div>


        <div class="category-grid">

            <?php if (!empty($categories)): ?>

                <?php foreach ($categories as $category): ?>

                    <a
                        href="shop.php?category=<?= urlencode($category["slug"]) ?>"
                        class="category-card"
                    >

                        <?php if (!empty($category["image"])): ?>

                            <img
                                src="uploads/categories/<?= htmlspecialchars($category["image"]) ?>"
                                alt="<?= htmlspecialchars($category["name"]) ?>"
                            >

                        <?php else: ?>

                            <div class="category-placeholder">
                                <span>✦</span>
                            </div>

                        <?php endif; ?>


                        <div class="category-overlay">

                            <h3>
                                <?= htmlspecialchars($category["name"]) ?>
                            </h3>

                            <span>
                                Explore Collection →
                            </span>

                        </div>

                    </a>

                <?php endforeach; ?>

            <?php else: ?>

                <a href="shop.php" class="category-card">

                    <div class="category-placeholder">
                        <span>♢</span>
                    </div>

                    <div class="category-overlay">
                        <h3>Gold Jewellery</h3>
                        <span>Explore Collection →</span>
                    </div>

                </a>


                <a href="shop.php" class="category-card">

                    <div class="category-placeholder">
                        <span>◇</span>
                    </div>

                    <div class="category-overlay">
                        <h3>Rings</h3>
                        <span>Explore Collection →</span>
                    </div>

                </a>


                <a href="shop.php" class="category-card">

                    <div class="category-placeholder">
                        <span>✦</span>
                    </div>

                    <div class="category-overlay">
                        <h3>Necklaces</h3>
                        <span>Explore Collection →</span>
                    </div>

                </a>


                <a href="shop.php" class="category-card">

                    <div class="category-placeholder">
                        <span>♡</span>
                    </div>

                    <div class="category-overlay">
                        <h3>Earrings</h3>
                        <span>Explore Collection →</span>
                    </div>

                </a>

            <?php endif; ?>

        </div>

    </div>

</section>


<!-- =========================================
     FEATURED PRODUCTS
========================================= -->

<section class="section products-section">

    <div class="container">

        <div class="section-heading">

            <span>
                OUR COLLECTION
            </span>

            <h2>
                Featured Jewellery
            </h2>

            <p>
                Elegant pieces designed for every special occasion.
            </p>

        </div>


        <div class="product-grid">

            <?php if (!empty($products)): ?>

                <?php foreach ($products as $product): ?>

                    <?php

                    $image = !empty($product["image"])
                        ? "uploads/products/" . $product["image"]
                        : "assets/images/product-placeholder.jpg";

                    $price = (float) $product["price"];

                    $salePrice = !empty($product["sale_price"])
                        ? (float) $product["sale_price"]
                        : 0;

                    ?>

                    <div class="product-card">

                        <div class="product-image">

                            <a
                                href="shop-details.php?slug=<?= urlencode($product["slug"]) ?>"
                            >

                                <img
                                    src="<?= htmlspecialchars($image) ?>"
                                    alt="<?= htmlspecialchars($product["name"]) ?>"
                                    onerror="this.src='https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=700&q=85'"
                                >

                            </a>


                            <?php if ($salePrice > 0 && $salePrice < $price): ?>

                                <span class="product-badge">
                                    SALE
                                </span>

                            <?php endif; ?>


                            <a
                                href="shop-details.php?slug=<?= urlencode($product["slug"]) ?>"
                                class="product-view"
                            >
                                View Product
                            </a>

                        </div>


                        <div class="product-info">

                            <span class="product-category">

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


                            <div class="product-price">

                                <?php if ($salePrice > 0 && $salePrice < $price): ?>

                                    <span class="sale-price">
                                        ₹<?= number_format($salePrice, 2) ?>
                                    </span>

                                    <span class="old-price">
                                        ₹<?= number_format($price, 2) ?>
                                    </span>

                                <?php else: ?>

                                    <span class="sale-price">
                                        ₹<?= number_format($price, 2) ?>
                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="empty-products">

                    <h3>
                        Jewellery Collection Coming Soon
                    </h3>

                    <p>
                        Our latest collection will be available shortly.
                    </p>

                    <a href="shop.php" class="btn btn-dark">
                        Visit Shop
                    </a>

                </div>

            <?php endif; ?>

        </div>


        <div class="center-button">

            <a href="shop.php" class="btn btn-dark">
                View All Jewellery
            </a>

        </div>

    </div>

</section>




<!-- =========================================
     OFFER BANNER
========================================= -->

<section class="offer-section">

    <div class="container">

        <div class="offer-content">

            <span>
                SPECIAL COLLECTION
            </span>

            <h2>
                Celebrate Every<br>
                Beautiful Moment
            </h2>

            <p>
                Discover elegant jewellery created for weddings,
                anniversaries, celebrations and everyday luxury.
            </p>

            <a href="shop.php" class="btn btn-gold">
                Shop Collection
            </a>

        </div>

    </div>

</section>


<!-- =========================================
     WHY CHOOSE US
========================================= -->

<section class="section why-section">

    <div class="container">

        <div class="section-heading">

            <span>
                WHY KHUSHBU
            </span>

            <h2>
                Made For Your Special Moments
            </h2>

            <p>
                Quality, trust and timeless design in every piece.
            </p>

        </div>


        <div class="why-grid">

            <div class="why-card">

                <div class="why-number">
                    01
                </div>

                <h3>
                    Exceptional Quality
                </h3>

                <p>
                    Every jewellery piece is selected with
                    quality and craftsmanship in mind.
                </p>

            </div>


            <div class="why-card">

                <div class="why-number">
                    02
                </div>

                <h3>
                    Timeless Designs
                </h3>

                <p>
                    Classic and contemporary designs created
                    to remain beautiful for years.
                </p>

            </div>


            <div class="why-card">

                <div class="why-number">
                    03
                </div>

                <h3>
                    Trusted Service
                </h3>

                <p>
                    From choosing your jewellery to delivery,
                    we focus on a smooth experience.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     TESTIMONIALS
========================================= -->

<section class="testimonial-section">

    <div class="container">

        <div class="section-heading">

            <span>
                CUSTOMER LOVE
            </span>

            <h2>
                What Our Customers Say
            </h2>

        </div>


        <div class="testimonial-grid">

            <div class="testimonial-card">

                <div class="stars">
                    ★★★★★
                </div>

                <p>
                    “Beautiful collection and excellent quality.
                    The jewellery looked even more beautiful in person.”
                </p>

                <h4>
                    Priya Sharma
                </h4>

                <span>
                    Verified Customer
                </span>

            </div>


            <div class="testimonial-card">

                <div class="stars">
                    ★★★★★
                </div>

                <p>
                    “I purchased a ring for a special occasion
                    and absolutely loved the design and finish.”
                </p>

                <h4>
                    Neha Singh
                </h4>

                <span>
                    Verified Customer
                </span>

            </div>


            <div class="testimonial-card">

                <div class="stars">
                    ★★★★★
                </div>

                <p>
                    “Very elegant designs and a smooth shopping
                    experience. Highly satisfied with my purchase.”
                </p>

                <h4>
                    Anjali Mehta
                </h4>

                <span>
                    Verified Customer
                </span>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     NEWSLETTER
========================================= -->

<section class="newsletter-section">

    <div class="container newsletter-content">

        <div>

            <span>
                STAY CONNECTED
            </span>

            <h2>
                Get Jewellery Inspiration
            </h2>

            <p>
                Subscribe for new collections, special offers
                and jewellery inspiration.
            </p>

        </div>


        <form
            class="newsletter-form"
            onsubmit="return false;"
        >

            <input
                type="email"
                placeholder="Enter your email address"
                required
            >

            <button
                type="submit"
                class="btn btn-gold"
            >
                Subscribe
            </button>

        </form>

    </div>

</section>


<?php require_once "includes/footer.php"; ?>