<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

$message = "";
$error = "";

/* Create upload folder */
$uploadDir = "../uploads/products/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* Get categories */
$categories = $conn->query(
    "SELECT id, name FROM categories
     WHERE status='active'
     ORDER BY name ASC"
);

/* Add Product */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $category_id = (int) $_POST["category_id"];
    $sku = trim($_POST["sku"]);
    $description = trim($_POST["description"]);
    $short_description = trim($_POST["short_description"]);
    $price = (float) $_POST["price"];
    $sale_price = ($_POST["sale_price"] !== "")
        ? (float) $_POST["sale_price"]
        : null;

    $stock = (int) $_POST["stock"];
    $material = trim($_POST["material"]);
    $weight = trim($_POST["weight"]);
    $size = trim($_POST["size"]);
    $status = $_POST["status"];
    $featured = isset($_POST["featured"]) ? 1 : 0;

    if ($name === "") {
        $error = "Product name is required.";
    } elseif ($category_id <= 0) {
        $error = "Please select a category.";
    } elseif ($price <= 0) {
        $error = "Please enter a valid price.";
    } else {

        /* Slug */
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        /* Make slug unique */
        $baseSlug = $slug;
        $counter = 1;

        while (true) {

            $checkSlug = $conn->prepare(
                "SELECT id FROM products WHERE slug = ?"
            );

            $checkSlug->bind_param("s", $slug);
            $checkSlug->execute();

            $slugResult = $checkSlug->get_result();

            if ($slugResult->num_rows === 0) {
                break;
            }

            $slug = $baseSlug . "-" . $counter;
            $counter++;
        }

        /* SKU */
        if ($sku === "") {
            $sku = "JEW-" . strtoupper(substr(md5(uniqid()), 0, 8));
        }

        /* Images */
        $images = [
            "image"  => null,
            "image2" => null,
            "image3" => null,
            "image4" => null
        ];

        $allowedTypes = [
            "image/jpeg",
            "image/jpg",
            "image/png",
            "image/webp"
        ];

        $imageFields = [
            "image",
            "image2",
            "image3",
            "image4"
        ];

        foreach ($imageFields as $field) {

            if (
                isset($_FILES[$field]) &&
                $_FILES[$field]["error"] === UPLOAD_ERR_OK
            ) {

                $tmpName = $_FILES[$field]["tmp_name"];
                $originalName = $_FILES[$field]["name"];

                $fileType = mime_content_type($tmpName);

                if (!in_array($fileType, $allowedTypes)) {
                    $error = "Only JPG, PNG and WEBP images are allowed.";
                    break;
                }

                $extension = strtolower(
                    pathinfo($originalName, PATHINFO_EXTENSION)
                );

                $newName =
                    time() . "_" .
                    uniqid() . "." .
                    $extension;

                if (move_uploaded_file(
                    $tmpName,
                    $uploadDir . $newName
                )) {

                    $images[$field] = $newName;

                } else {

                    $error = "Image upload failed.";
                    break;
                }
            }
        }

        if ($error === "") {

            $stmt = $conn->prepare("
                INSERT INTO products
                (
                    category_id,
                    name,
                    slug,
                    sku,
                    description,
                    short_description,
                    price,
                    sale_price,
                    stock,
                    image,
                    image2,
                    image3,
                    image4,
                    material,
                    weight,
                    size,
                    status,
                    featured
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");

            $stmt->bind_param(
                "isssssddissssssssi",
                $category_id,
                $name,
                $slug,
                $sku,
                $description,
                $short_description,
                $price,
                $sale_price,
                $stock,
                $images["image"],
                $images["image2"],
                $images["image3"],
                $images["image4"],
                $material,
                $weight,
                $size,
                $status,
                $featured
            );

            if ($stmt->execute()) {

                header("Location: products.php?added=1");
                exit;

            } else {

                $error = "Product could not be added: " .
                    $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Product - Jewellery Admin</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6f6f6;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #181818;
            color: white;
            padding: 25px 15px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background: #b8860b;
        }

        .main {
            margin-left: 240px;
            padding: 35px;
        }

        .box {
            max-width: 1000px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 25px;
        }

        .message {
            padding: 12px;
            background: #e8f7e8;
            color: #176b17;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .error {
            padding: 12px;
            background: #ffe5e5;
            color: #b00020;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 15px;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input[type="file"] {
            background: #fafafa;
        }

        .check {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .check input {
            width: auto;
        }

        .btn {
            border: none;
            background: #b8860b;
            color: white;
            padding: 14px 28px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn:hover {
            background: #967008;
        }

        @media(max-width: 800px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main {
                margin-left: 0;
                padding: 15px;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: auto;
            }
        }

    </style>

</head>

<body>

<aside class="sidebar">

    <h2>Jewellery Admin</h2>

    <a href="dashboard.php">
        Dashboard
    </a>

    <a href="products.php">
        Products
    </a>

    <a href="add-product.php">
        Add Product
    </a>

    <a href="categories.php">
        Categories
    </a>

    <a href="orders.php">
        Orders
    </a>

    <a href="users.php">
        Users
    </a>

    <a href="messages.php">
        Messages
    </a>

    <a href="logout.php">
        Logout
    </a>

</aside>

<main class="main">

    <div class="box">

        <h1>Add New Product</h1>

        <?php if ($error): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="grid">

                <div>

                    <label>
                        Product Name *
                    </label>

                    <input
                        type="text"
                        name="name"
                        placeholder="e.g. Gold Ring"
                        required
                    >

                </div>

                <div>

                    <label>
                        Category *
                    </label>

                    <select
                        name="category_id"
                        required
                    >

                        <option value="">
                            Select Category
                        </option>

                        <?php while (
                            $category = $categories->fetch_assoc()
                        ): ?>

                            <option
                                value="<?= $category["id"] ?>"
                            >
                                <?= htmlspecialchars(
                                    $category["name"]
                                ) ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div>

                    <label>
                        SKU
                    </label>

                    <input
                        type="text"
                        name="sku"
                        placeholder="Optional"
                    >

                </div>

                <div>

                    <label>
                        Stock *
                    </label>

                    <input
                        type="number"
                        name="stock"
                        value="1"
                        min="0"
                        required
                    >

                </div>

                <div>

                    <label>
                        Price *
                    </label>

                    <input
                        type="number"
                        name="price"
                        step="0.01"
                        min="0"
                        placeholder="25000"
                        required
                    >

                </div>

                <div>

                    <label>
                        Sale Price
                    </label>

                    <input
                        type="number"
                        name="sale_price"
                        step="0.01"
                        min="0"
                        placeholder="22000"
                    >

                </div>

                <div>

                    <label>
                        Material
                    </label>

                    <input
                        type="text"
                        name="material"
                        placeholder="Gold / Silver / Diamond"
                    >

                </div>

                <div>

                    <label>
                        Weight
                    </label>

                    <input
                        type="text"
                        name="weight"
                        placeholder="5.5 gram"
                    >

                </div>

                <div>

                    <label>
                        Size
                    </label>

                    <input
                        type="text"
                        name="size"
                        placeholder="Adjustable / 18 / 20"
                    >

                </div>

                <div>

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <option value="active">
                            Active
                        </option>

                        <option value="inactive">
                            Inactive
                        </option>

                    </select>

                </div>

                <div class="full">

                    <label>
                        Short Description
                    </label>

                    <textarea
                        name="short_description"
                        placeholder="Short product description"
                    ></textarea>

                </div>

                <div class="full">

                    <label>
                        Full Description
                    </label>

                    <textarea
                        name="description"
                        placeholder="Complete product description"
                    ></textarea>

                </div>

                <div>

                    <label>
                        Main Product Image *
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                </div>

                <div>

                    <label>
                        Product Image 2
                    </label>

                    <input
                        type="file"
                        name="image2"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>

                <div>

                    <label>
                        Product Image 3
                    </label>

                    <input
                        type="file"
                        name="image3"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>

                <div>

                    <label>
                        Product Image 4
                    </label>

                    <input
                        type="file"
                        name="image4"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>

                <div class="full">

                    <label class="check">

                        <input
                            type="checkbox"
                            name="featured"
                            value="1"
                        >

                        Featured Product

                    </label>

                </div>

                <div class="full">

                    <button
                        type="submit"
                        class="btn"
                    >
                        Add Product
                    </button>

                </div>

            </div>

        </form>

    </div>

</main>

</body>

</html>