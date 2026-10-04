
<?php

session_start();

require_once "db.php";


/* =========================
   GET ACTIVE CATEGORIES
========================= */

$categories = $conn->query("
    SELECT
        c.id,
        c.name,
        c.slug,
        c.image,
        c.description,
        COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p
        ON p.category_id = c.id
        AND p.status = 'active'
    WHERE c.status = 'active'
    GROUP BY
        c.id,
        c.name,
        c.slug,
        c.image,
        c.description
    ORDER BY c.id ASC
");

?>

<?php require_once "includes/header.php"; ?>


<style>

/* =========================
   PAGE
========================= */

.category-page {
    background: #faf8f3;
    min-height: 100vh;
}


/* =========================
   HERO
========================= */

.category-hero {

    min-height: 360px;

    display: flex;

    align-items: center;

    justify-content: center;

    text-align: center;

    color: #fff;

    background:
        linear-gradient(
            rgba(20,20,20,.65),
            rgba(20,20,20,.65)
        ),
        url("assets/images/category-banner.jpg")
        center/cover no-repeat;
}

.category-hero h1 {

    font-family:
        "Cormorant Garamond",
        serif;

    font-size: 64px;

    font-weight: 500;

    margin: 0 0 10px;
}

.category-hero p {

    margin: 0;

    font-size: 13px;

    letter-spacing: 2px;
}


/* =========================
   CONTAINER
========================= */

.category-container {

    max-width: 1200px;

    margin: auto;

    padding: 80px 20px;
}


/* =========================
   HEADING
========================= */

.category-heading {

    text-align: center;

    margin-bottom: 50px;
}

.category-heading span {

    color: #b08a45;

    font-size: 12px;

    letter-spacing: 3px;

    text-transform: uppercase;
}

.category-heading h2 {

    font-family:
        "Cormorant Garamond",
        serif;

    font-size: 46px;

    font-weight: 500;

    margin: 8px 0 15px;
}

.category-heading p {

    max-width: 650px;

    margin: auto;

    color: #777;

    line-height: 1.8;
}


/* =========================
   GRID
========================= */

.category-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 25px;
}


/* =========================
   CARD
========================= */

.category-card {

    position: relative;

    background: #fff;

    border: 1px solid #eee;

    overflow: hidden;

    transition: .35s;
}

.category-card:hover {

    transform: translateY(-7px);

    box-shadow:
        0 15px 35px
        rgba(0,0,0,.10);
}


/* =========================
   IMAGE
========================= */

.category-image {

    height: 330px;

    overflow: hidden;

    background: #f1eee8;
}

.category-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;

    transition: .5s;
}

.category-card:hover
.category-image img {

    transform: scale(1.05);
}


/* =========================
   CONTENT
========================= */

.category-content {

    text-align: center;

    padding: 25px 20px 28px;
}

.category-content h3 {

    font-family:
        "Cormorant Garamond",
        serif;

    font-size: 30px;

    font-weight: 500;

    margin: 0 0 8px;

    color: #171717;
}

.category-content p {

    color: #777;

    font-size: 13px;

    line-height: 1.7;

    min-height: 45px;

    margin: 0 auto 12px;

    max-width: 320px;
}

.product-count {

    color: #b08a45;

    font-size: 12px;

    letter-spacing: 1px;

    margin-bottom: 18px;
}


/* =========================
   BUTTON
========================= */

.category-btn {

    display: inline-block;

    background: #171717;

    color: #fff;

    text-decoration: none;

    padding: 12px 24px;

    font-size: 12px;

    letter-spacing: 1px;

    transition: .3s;
}

.category-btn:hover {

    background: #b08a45;
}


/* =========================
   EMPTY
========================= */

.empty-category {

    text-align: center;

    padding: 80px 20px;

    background: #fff;

    border: 1px solid #eee;
}

.empty-category h2 {

    font-family:
        "Cormorant Garamond",
        serif;

    font-size: 38px;

    font-weight: 500;

    margin: 0 0 10px;
}

.empty-category p {

    color: #777;

    margin-bottom: 20px;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 900px) {

    .category-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }

    .category-hero h1 {

        font-size: 52px;
    }
}


@media (max-width: 600px) {

    .category-hero {

        min-height: 280px;
    }

    .category-hero h1 {

        font-size: 43px;
    }

    .category-container {

        padding: 55px 15px;
    }

    .category-heading h2 {

        font-size: 38px;
    }

    .category-grid {

        grid-template-columns: 1fr;
    }

    .category-image {

        height: 350px;
    }
}

</style>


<main class="category-page">


    <!-- HERO -->

    <section class="category-hero">

        <div>

            <h1>
                Jewellery Categories
            </h1>

            <p>
                DISCOVER OUR BEAUTIFUL COLLECTIONS
            </p>

        </div>

    </section>


    <!-- CATEGORIES -->

    <section class="category-container">


        <div class="category-heading">

            <span>
                Explore Collection
            </span>

            <h2>
                Shop By Category
            </h2>

            <p>
                Explore our carefully selected jewellery collections
                and discover something beautiful for every occasion.
            </p>

        </div>


        <?php if ($categories && $categories->num_rows > 0): ?>


            <div class="category-grid">


                <?php while (
                    $category =
                    $categories->fetch_assoc()
                ): ?>


                    <?php

                    /*
                     * CATEGORY IMAGE
                     *
                     * If category image exists,
                     * use it.
                     *
                     * Otherwise use placeholder.
                     */

                    $categoryImage =
                        !empty($category["image"])
                        ? "assets/images/categories/"
                            . $category["image"]
                        : "assets/images/category-placeholder.jpg";


                    /*
                     * Shop URL
                     */

                    $shopUrl =
                        "shop.php?category="
                        . urlencode(
                            $category["slug"]
                        );

                    ?>


                    <article class="category-card">


                        <!-- IMAGE -->

                        <a
                            href="<?= htmlspecialchars(
                                $shopUrl
                            ) ?>"
                            class="category-image"
                        >

                            <img
                                src="<?= htmlspecialchars(
                                    $categoryImage
                                ) ?>"
                                alt="<?= htmlspecialchars(
                                    $category["name"]
                                ) ?>"
                                loading="lazy"
                                onerror="
                                    this.onerror=null;
                                    this.src='assets/images/category-placeholder.jpg';
                                "
                            >

                        </a>


                        <!-- CONTENT -->

                        <div class="category-content">


                            <h3>

                                <?= htmlspecialchars(
                                    $category["name"]
                                ) ?>

                            </h3>


                            <p>

                                <?=
                                !empty(
                                    $category["description"]
                                )
                                    ? htmlspecialchars(
                                        $category[
                                            "description"
                                        ]
                                    )
                                    : "Explore our beautiful "
                                    . htmlspecialchars(
                                        $category["name"]
                                    )
                                    . " collection."
                                ?>

                            </p>


                            <div class="product-count">

                                <?= (int)
                                    $category[
                                        "product_count"
                                    ] ?>

                                PRODUCTS

                            </div>


                            <a
                                href="<?= htmlspecialchars(
                                    $shopUrl
                                ) ?>"
                                class="category-btn"
                            >
                                SHOP COLLECTION
                            </a>


                        </div>


                    </article>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <div class="empty-category">

                <h2>
                    No Categories Found
                </h2>

                <p>
                    Categories will appear here once
                    they are added from the admin panel.
                </p>

                <a
                    href="shop.php"
                    class="category-btn"
                >
                    VIEW SHOP
                </a>

            </div>


        <?php endif; ?>


    </section>

</main>


<?php require_once "includes/footer.php"; ?>
