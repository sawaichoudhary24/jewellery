
<?php
session_start();
require_once "db.php";

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name    = trim($_POST["name"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $phone   = trim($_POST["phone"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $message === "") {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO messages
            (name, email, phone, subject, message, status)
            VALUES (?, ?, ?, ?, ?, 'unread')
        ");

        $stmt->bind_param(
            "sssss",
            $name,
            $email,
            $phone,
            $subject,
            $message
        );

        if ($stmt->execute()) {
            $success = "Thank you! Your message has been sent successfully.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<?php require_once "includes/header.php"; ?>

<style>
.contact-page {
    background: #faf8f3;
    min-height: 100vh;
}

.contact-hero {
    min-height: 360px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    background:
        linear-gradient(rgba(20,20,20,.65), rgba(20,20,20,.65)),
        url("assets/images/contact-banner.jpg") center/cover no-repeat;
    color: #fff;
}

.contact-hero h1 {
    font-family: "Cormorant Garamond", serif;
    font-size: 64px;
    margin: 0 0 10px;
}

.contact-hero p {
    font-size: 15px;
    letter-spacing: 1px;
}

.contact-section {
    max-width: 1200px;
    margin: auto;
    padding: 80px 20px;
}

.contact-grid {
    display: grid;
    grid-template-columns: 0.85fr 1.15fr;
    gap: 50px;
}

.contact-info h2,
.contact-form-box h2 {
    font-family: "Cormorant Garamond", serif;
    font-size: 42px;
    margin: 0 0 15px;
    color: #171717;
}

.contact-info > p {
    color: #666;
    line-height: 1.8;
    margin-bottom: 30px;
}

.info-item {
    display: flex;
    gap: 18px;
    margin-bottom: 25px;
    align-items: flex-start;
}

.info-icon {
    width: 48px;
    height: 48px;
    border: 1px solid #b08a45;
    color: #b08a45;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    flex-shrink: 0;
}

.info-item h4 {
    margin: 0 0 5px;
    font-size: 15px;
    color: #222;
}

.info-item p {
    margin: 0;
    color: #777;
    line-height: 1.6;
}

.contact-form-box {
    background: #fff;
    padding: 40px;
    box-shadow: 0 8px 35px rgba(0,0,0,.07);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    color: #333;
    font-weight: 600;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 14px 15px;
    border: 1px solid #ddd;
    outline: none;
    font-family: inherit;
    font-size: 14px;
    transition: .3s;
}

.form-group textarea {
    height: 150px;
    resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #b08a45;
}

.contact-btn {
    border: none;
    background: #171717;
    color: #fff;
    padding: 14px 30px;
    cursor: pointer;
    font-size: 13px;
    letter-spacing: 1px;
    transition: .3s;
}

.contact-btn:hover {
    background: #b08a45;
}

.alert {
    padding: 14px 16px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-success {
    background: #edf8ef;
    color: #27733a;
    border: 1px solid #c9e8d0;
}

.alert-error {
    background: #fff0f0;
    color: #a52a2a;
    border: 1px solid #f0cccc;
}

.contact-map {
    margin-top: 60px;
}

.contact-map iframe {
    width: 100%;
    height: 380px;
    border: 0;
    display: block;
}

@media (max-width: 850px) {

    .contact-grid {
        grid-template-columns: 1fr;
    }

    .contact-hero h1 {
        font-size: 48px;
    }

    .contact-form-box {
        padding: 25px;
    }
}

@media (max-width: 600px) {

    .contact-hero {
        min-height: 280px;
    }

    .contact-hero h1 {
        font-size: 40px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .contact-section {
        padding: 55px 15px;
    }

    .contact-info h2,
    .contact-form-box h2 {
        font-size: 34px;
    }
}
</style>

<main class="contact-page">

    <!-- HERO -->
    <section class="contact-hero">
        <div>
            <h1>Contact Us</h1>
            <p>WE WOULD LOVE TO HEAR FROM YOU</p>
        </div>
    </section>

    <!-- CONTACT -->
    <section class="contact-section">

        <div class="contact-grid">

            <!-- INFO -->
            <div class="contact-info">

                <h2>Get In Touch</h2>

                <p>
                    Have a question about our jewellery, orders or services?
                    Send us a message and our team will get back to you.
                </p>

                <div class="info-item">
                    <div class="info-icon">📍</div>
                    <div>
                        <h4>Our Address</h4>
                        <p>
                            Jodhpur, Rajasthan, India
                        </p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">☎</div>
                    <div>
                        <h4>Phone</h4>
                        <p>
                            +91 00000 00000
                        </p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">✉</div>
                    <div>
                        <h4>Email</h4>
                        <p>
                            info@khushbujewellers.com
                        </p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">◷</div>
                    <div>
                        <h4>Opening Hours</h4>
                        <p>
                            Monday - Sunday<br>
                            10:00 AM - 8:00 PM
                        </p>
                    </div>
                </div>

            </div>

            <!-- FORM -->
            <div class="contact-form-box">

                <h2>Send A Message</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <div class="form-row">

                        <div class="form-group">
                            <label>Your Name *</label>
                            <input
                                type="text"
                                name="name"
                                placeholder="Enter your name"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label>Email Address *</label>
                            <input
                                type="email"
                                name="email"
                                placeholder="Enter your email"
                                required
                            >
                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input
                                type="text"
                                name="phone"
                                placeholder="Enter phone number"
                            >
                        </div>

                        <div class="form-group">
                            <label>Subject</label>
                            <input
                                type="text"
                                name="subject"
                                placeholder="Enter subject"
                            >
                        </div>

                    </div>

                    <div class="form-group">

                        <label>Your Message *</label>

                        <textarea
                            name="message"
                            placeholder="Write your message..."
                            required
                        ></textarea>

                    </div>

                    <button type="submit" class="contact-btn">
                        SEND MESSAGE
                    </button>

                </form>

            </div>

        </div>

        <!-- MAP -->
        <div class="contact-map">

            <iframe
                src="https://www.google.com/maps?q=Jodhpur,Rajasthan,India&output=embed"
                loading="lazy"
                allowfullscreen>
            </iframe>

        </div>

    </section>

</main>

<?php require_once "includes/footer.php"; ?>
