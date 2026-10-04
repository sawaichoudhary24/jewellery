
<?php
session_start();
require_once "db.php";
?>

<?php require_once "includes/header.php"; ?>

<style>

/* ================================
   ABOUT PAGE
================================ */

.about-page {
    --gold: #b08a45;
    --dark: #1d1d1d;
    --text: #555;
    --light: #faf8f4;

    background: #fff;
    color: var(--dark);
}


/* ================================
   COMMON CONTAINER
================================ */

.about-container {
    width: min(1100px, calc(100% - 36px));
    margin: auto;
}


/* ================================
   HERO
================================ */

.about-hero {
    min-height: 330px;

    display: flex;
    align-items: center;
    justify-content: center;

    text-align: center;
    color: #fff;

    background:
        linear-gradient(
            rgba(0, 0, 0, 0.48),
            rgba(0, 0, 0, 0.48)
        ),
        url("assets/images/about.png")
        center / cover no-repeat;
}

.about-hero h1 {
    font-family: "Cormorant Garamond", serif;
    font-size: 60px;
    font-weight: 500;

    margin: 0 0 8px;
}

.about-hero p {
    margin: 0;

    font-size: 12px;
    letter-spacing: 2px;
}


/* ================================
   COMMON SECTION
================================ */

.about-section {
    padding: 75px 0;
}

.about-section.light {
    background: var(--light);
}


/* ================================
   OUR STORY
================================ */

.about-story {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 55px;

    align-items: center;
}

.about-story img {
    width: 100%;
    height: 460px;

    object-fit: cover;

    display: block;
}

.about-label {
    color: var(--gold);

    font-size: 12px;
    letter-spacing: 2px;

    text-transform: uppercase;
}

.about-story h2,
.about-heading h2,
.about-why h2 {
    font-family: "Cormorant Garamond", serif;

    font-size: 46px;
    font-weight: 500;

    line-height: 1.05;

    margin: 12px 0 20px;
}

.about-line {
    width: 55px;
    height: 1px;

    background: var(--gold);

    margin: 20px 0;
}

.about-story p,
.about-heading p,
.about-why p {
    color: var(--text);

    line-height: 1.8;

    font-size: 15px;
}


/* ================================
   VALUES
================================ */

.about-heading {
    text-align: center;

    max-width: 650px;

    margin: 0 auto 45px;
}

.about-heading h2 {
    margin-bottom: 12px;
}

.about-values {
    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 20px;
}

.about-card {
    background: #fff;

    border: 1px solid #eee;

    padding: 32px 25px;

    text-align: center;
}

.about-card .icon {
    width: 52px;
    height: 52px;

    border: 1px solid var(--gold);

    color: var(--gold);

    display: flex;
    align-items: center;
    justify-content: center;

    margin: 0 auto 18px;

    font-size: 21px;
}

.about-card h3 {
    font-family: "Cormorant Garamond", serif;

    font-size: 28px;
    font-weight: 500;

    margin: 0 0 10px;
}

.about-card p {
    color: var(--text);

    font-size: 14px;

    line-height: 1.7;

    margin: 0;
}


/* ================================
   WHY CHOOSE US
================================ */

.about-why {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 55px;

    align-items: center;
}

.about-why img {
    width: 100%;
    height: 460px;

    object-fit: cover;

    display: block;
}

.about-list {
    margin-top: 25px;
}

.about-item {
    display: flex;

    gap: 14px;

    margin-bottom: 18px;
}

.about-check {
    width: 28px;
    height: 28px;

    border: 1px solid var(--gold);

    color: var(--gold);

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;
}

.about-item h4 {
    margin: 0 0 4px;

    font-size: 15px;
}

.about-item p {
    margin: 0;

    font-size: 13px;
}


/* ================================
   STATS
================================ */

.about-stats {
    background: var(--dark);

    color: #fff;

    padding: 45px 0;
}

.stats-grid {
    display: grid;

    grid-template-columns: repeat(4, 1fr);

    text-align: center;
}

.stat {
    padding: 10px 20px;

    border-right: 1px solid rgba(255, 255, 255, 0.15);
}

.stat:last-child {
    border-right: 0;
}

.stat strong {
    display: block;

    color: var(--gold);

    font-family: "Cormorant Garamond", serif;

    font-size: 42px;

    font-weight: 500;
}

.stat span {
    font-size: 11px;

    letter-spacing: 1px;

    color: #ccc;
}


/* ================================
   CTA
================================ */

.about-cta {
    text-align: center;

    padding: 70px 20px;

    background:
        linear-gradient(
            rgba(0, 0, 0, 0.62),
            rgba(0, 0, 0, 0.62)
        ),
        url("assets/images/about.png")
        center / cover no-repeat;

    color: #fff;
}

.about-cta h2 {
    font-family: "Cormorant Garamond", serif;

    font-size: 46px;
    font-weight: 500;

    margin: 10px 0 25px;
}

.about-btn {
    display: inline-block;

    background: var(--gold);

    color: #fff;

    text-decoration: none;

    padding: 13px 27px;

    font-size: 12px;

    letter-spacing: 1px;
}

.about-btn:hover {
    background: #fff;

    color: var(--dark);
}


/* ================================
   TABLET
================================ */

@media (max-width: 800px) {

    .about-story,
    .about-why {
        grid-template-columns: 1fr;

        gap: 35px;
    }

    .about-values {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .stat {
        border-right: 0;

        border-bottom: 1px solid rgba(255, 255, 255, 0.15);

        padding: 18px;
    }

    .stat:nth-child(odd) {
        border-right: 1px solid rgba(255, 255, 255, 0.15);
    }
}


/* ================================
   MOBILE
================================ */

@media (max-width: 600px) {

    .about-hero {
        min-height: 270px;
    }

    .about-hero h1 {
        font-size: 46px;
    }

    .about-section {
        padding: 55px 0;
    }

    .about-story img,
    .about-why img {
        height: 340px;
    }

    .about-story h2,
    .about-heading h2,
    .about-why h2 {
        font-size: 38px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stat:nth-child(odd) {
        border-right: 0;
    }

    .stat:last-child {
        border-bottom: 0;
    }

}

</style>


<!-- ================================
     ABOUT PAGE
================================ -->

<main class="about-page">


    <!-- HERO -->

    <section class="about-hero">

        <div>

            <h1>
                About Us
            </h1>

            <p>
                CRAFTING BEAUTY • CELEBRATING MOMENTS
            </p>

        </div>

    </section>


    <!-- OUR STORY -->

    <section class="about-section">

        <div class="about-container">

            <div class="about-story">


                <img
                    src="assets/images/about.png"
                    alt="Khushbu Jewellers"
                >


                <div>

                    <span class="about-label">
                        Our Story
                    </span>


                    <h2>
                        Jewellery Made With Passion
                    </h2>


                    <div class="about-line"></div>


                    <p>
                        Jewellery is more than an accessory.
                        It is a reflection of personality, emotions
                        and the beautiful moments we celebrate in life.
                    </p>


                    <p>
                        At Khushbu Jewellers, we bring together
                        timeless designs, fine craftsmanship and
                        a passion for creating jewellery that can
                        be cherished for years.
                    </p>


                    <p>
                        From elegant everyday pieces to special
                        occasion jewellery, every design is selected
                        with care to offer beauty, quality and
                        lasting value.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <!-- OUR VALUES -->

    <section class="about-section light">

        <div class="about-container">


            <div class="about-heading">

                <span class="about-label">
                    What We Believe
                </span>


                <h2>
                    Our Values
                </h2>


                <p>
                    We believe beautiful jewellery should be
                    created with attention to detail, trust
                    and timeless style.
                </p>

            </div>


            <div class="about-values">


                <!-- QUALITY -->

                <div class="about-card">

                    <div class="icon">
                        ✦
                    </div>

                    <h3>
                        Quality
                    </h3>

                    <p>
                        We focus on quality craftsmanship
                        and carefully selected jewellery
                        for our customers.
                    </p>

                </div>


                <!-- TRUST -->

                <div class="about-card">

                    <div class="icon">
                        ♡
                    </div>

                    <h3>
                        Trust
                    </h3>

                    <p>
                        Customer trust is at the heart
                        of everything we do and every
                        relationship we build.
                    </p>

                </div>


                <!-- ELEGANCE -->

                <div class="about-card">

                    <div class="icon">
                        ◇
                    </div>

                    <h3>
                        Elegance
                    </h3>

                    <p>
                        Our collection brings together
                        elegant designs that remain
                        beautiful beyond trends.
                    </p>

                </div>


            </div>

        </div>

    </section>


    <!-- WHY CHOOSE US -->

    <section class="about-section">

        <div class="about-container">

            <div class="about-why">


                <div>

                    <span class="about-label">
                        Why Choose Us
                    </span>


                    <h2>
                        Designed To Make
                        Every Moment Special
                    </h2>


                    <div class="about-line"></div>


                    <p>
                        We understand that every jewellery
                        purchase represents something meaningful.
                        That's why we aim to provide a shopping
                        experience built around quality, elegance
                        and customer care.
                    </p>


                    <div class="about-list">


                        <!-- ITEM 1 -->

                        <div class="about-item">

                            <div class="about-check">
                                ✓
                            </div>

                            <div>

                                <h4>
                                    Beautiful Designs
                                </h4>

                                <p>
                                    Elegant designs for everyday
                                    wear and special occasions.
                                </p>

                            </div>

                        </div>


                        <!-- ITEM 2 -->

                        <div class="about-item">

                            <div class="about-check">
                                ✓
                            </div>

                            <div>

                                <h4>
                                    Customer Focused
                                </h4>

                                <p>
                                    We put customer satisfaction
                                    at the centre of our service.
                                </p>

                            </div>

                        </div>


                        <!-- ITEM 3 -->

                        <div class="about-item">

                            <div class="about-check">
                                ✓
                            </div>

                            <div>

                                <h4>
                                    Timeless Collection
                                </h4>

                                <p>
                                    Jewellery selected to remain
                                    beautiful for years to come.
                                </p>

                            </div>

                        </div>


                    </div>

                </div>


                <img
                    src="assets/images/about1.png"
                    alt="Jewellery"
                >


            </div>

        </div>

    </section>


    <!-- STATS -->

    <section class="about-stats">

        <div class="about-container">

            <div class="stats-grid">


                <div class="stat">

                    <strong>
                        10+
                    </strong>

                    <span>
                        Years Experience
                    </span>

                </div>


                <div class="stat">

                    <strong>
                        500+
                    </strong>

                    <span>
                        Happy Customers
                    </span>

                </div>


                <div class="stat">

                    <strong>
                        1000+
                    </strong>

                    <span>
                        Beautiful Designs
                    </span>

                </div>


                <div class="stat">

                    <strong>
                        100%
                    </strong>

                    <span>
                        Customer Care
                    </span>

                </div>


            </div>

        </div>

    </section>


    <!-- CTA -->

    <section class="about-cta">

        <div class="about-container">

            <span class="about-label">
                Discover Our Collection
            </span>


            <h2>
                Find Something Beautiful For You
            </h2>


            <a
                href="shop.php"
                class="about-btn"
            >
                SHOP NOW
            </a>

        </div>

    </section>


</main>


<?php require_once "includes/footer.php"; ?>

