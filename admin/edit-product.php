<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

if (!isset($_GET["id"])) {
    header("Location: products.php");
    exit;
}

$id = (int) $_GET["id"];

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Product not found.");
}

$product = $result->fetch_assoc();

$categories = $conn->query(
    "SELECT id, name FROM categories
     WHERE status='active'
     ORDER BY name ASC"
);

$error = "";

$uploadDir = "../uploads/products/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $category_id = (int) $_POST["category_id"];
    $sku = trim($_POST["sku"]);
    $description = trim($_POST["description"]);
    $short_description = trim($_POST["short_description"]);
    $price = (float) $_POST["price"];

    $sale_price = $_POST["sale_price"] !== ""
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
    } else {

        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        $baseSlug = $slug;
        $counter = 1;

        while (true) {

            $check = $conn->prepare(
                "SELECT id FROM products
                 WHERE slug = ? AND id != ?"
            );

            $check->bind_param("si", $slug, $id);
            $check->execute();

            if ($check->get_result()->num_rows === 0) {
                break;
            }

            $slug = $baseSlug . "-" . $counter;
            $counter++;
        }

        $images = [
            "image" => $product["image"],
            "image2" => $product["image2"],
            "image3" => $product["image3"],
            "image4" => $product["image4"]
        ];

        $fields = [
            "image",
            "image2",
            "image3",
            "image4"
        ];

        $allowedTypes = [
            "image/jpeg",
            "image/jpg",
            "image/png",
            "image/webp"
        ];

        foreach ($fields as $field) {

            if (
                isset($_FILES[$field]) &&
                $_FILES[$field]["error"] === UPLOAD_ERR_OK
            ) {

                $tmp = $_FILES[$field]["tmp_name"];
                $type = mime_content_type($tmp);

                if (!in_array($type, $allowedTypes)) {
                    $error = "Only JPG, PNG and WEBP images are allowed.";
                    break;
                }

                $extension = strtolower(
                    pathinfo(
                        $_FILES[$field]["name"],
                        PATHINFO_EXTENSION
                    )
                );

                $newName =
                    time() . "_" .
                    uniqid() . "." .
                    $extension;

                if (
                    move_uploaded_file(
                        $tmp,
                        $uploadDir . $newName
                    )
                ) {
                    $images[$field] = $newName;
                }
            }
        }

        if ($error === "") {

            $stmt = $conn->prepare("
                UPDATE products SET

                category_id = ?,
                name = ?,
                slug = ?,
                sku = ?,
                description = ?,
                short_description = ?,
                price = ?,
                sale_price = ?,
                stock = ?,
                image = ?,
                image2 = ?,
                image3 = ?,
                image4 = ?,
                material = ?,
                weight = ?,
                size = ?,
                status = ?,
                featured = ?

                WHERE id = ?
            ");

            $stmt->bind_param(
                "isssssddissssssssii",
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
                $featured,
                $id
            );

            if ($stmt->execute()) {
                header("Location: products.php?updated=1");
                exit;
            }

            $error = $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Edit Product</title>

<style>

* {
    box-sizing:border-box;
}

body {
    margin:0;
    font-family:Arial,sans-serif;
    background:#f6f6f6;
}

.sidebar {
    position:fixed;
    width:240px;
    height:100vh;
    background:#181818;
    padding:25px 15px;
    color:white;
}

.sidebar h2 {
    text-align:center;
    margin-bottom:30px;
}

.sidebar a {
    display:block;
    color:white;
    text-decoration:none;
    padding:13px 15px;
    margin-bottom:5px;
    border-radius:5px;
}

.sidebar a:hover {
    background:#b8860b;
}

.main {
    margin-left:240px;
    padding:35px;
}

.box {
    max-width:1000px;
    background:white;
    padding:30px;
    border-radius:10px;
}

.grid {
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:20px;
}

.full {
    grid-column:1/-1;
}

label {
    display:block;
    margin-bottom:7px;
    font-weight:bold;
}

input,
select,
textarea {
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:5px;
}

textarea {
    min-height:120px;
}

.current {
    width:100px;
    height:100px;
    object-fit:cover;
    margin-top:10px;
}

button {
    background:#b8860b;
    color:white;
    border:0;
    padding:14px 25px;
    border-radius:5px;
    cursor:pointer;
}

.error {
    background:#ffe5e5;
    color:#b00020;
    padding:12px;
    margin-bottom:20px;
}

.check {
    display:flex;
    align-items:center;
    gap:10px;
}

.check input {
    width:auto;
}

@media(max-width:800px) {

    .sidebar {
        position:relative;
        width:100%;
        height:auto;
    }

    .main {
        margin-left:0;
        padding:15px;
    }

    .grid {
        grid-template-columns:1fr;
    }

    .full {
        grid-column:auto;
    }
}

</style>

</head>

<body>

<aside class="sidebar">

<h2>Jewellery Admin</h2>

<a href="dashboard.php">Dashboard</a>
<a href="products.php">Products</a>
<a href="add-product.php">Add Product</a>
<a href="categories.php">Categories</a>
<a href="orders.php">Orders</a>
<a href="users.php">Users</a>
<a href="messages.php">Messages</a>
<a href="logout.php">Logout</a>

</aside>

<main class="main">

<div class="box">

<h1>Edit Product</h1>

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

<label>Product Name</label>

<input
    type="text"
    name="name"
    value="<?= htmlspecialchars($product["name"]) ?>"
    required
>

</div>

<div>

<label>Category</label>

<select name="category_id" required>

<?php while ($category = $categories->fetch_assoc()): ?>

<option
    value="<?= $category["id"] ?>"
    <?= $product["category_id"] == $category["id"]
        ? "selected"
        : "" ?>
>
<?= htmlspecialchars($category["name"]) ?>
</option>

<?php endwhile; ?>

</select>

</div>

<div>

<label>SKU</label>

<input
    type="text"
    name="sku"
    value="<?= htmlspecialchars($product["sku"] ?? "") ?>"
>

</div>

<div>

<label>Stock</label>

<input
    type="number"
    name="stock"
    value="<?= $product["stock"] ?>"
    min="0"
>

</div>

<div>

<label>Price</label>

<input
    type="number"
    name="price"
    step="0.01"
    value="<?= $product["price"] ?>"
>

</div>

<div>

<label>Sale Price</label>

<input
    type="number"
    name="sale_price"
    step="0.01"
    value="<?= $product["sale_price"] ?? "" ?>"
>

</div>

<div>

<label>Material</label>

<input
    type="text"
    name="material"
    value="<?= htmlspecialchars($product["material"] ?? "") ?>"
>

</div>

<div>

<label>Weight</label>

<input
    type="text"
    name="weight"
    value="<?= htmlspecialchars($product["weight"] ?? "") ?>"
>

</div>

<div>

<label>Size</label>

<input
    type="text"
    name="size"
    value="<?= htmlspecialchars($product["size"] ?? "") ?>"
>

</div>

<div>

<label>Status</label>

<select name="status">

<option
    value="active"
    <?= $product["status"] === "active"
        ? "selected"
        : "" ?>
>
Active
</option>

<option
    value="inactive"
    <?= $product["status"] === "inactive"
        ? "selected"
        : "" ?>
>
Inactive
</option>

</select>

</div>

<div class="full">

<label>Short Description</label>

<textarea name="short_description"><?= htmlspecialchars(
    $product["short_description"] ?? ""
) ?></textarea>

</div>

<div class="full">

<label>Description</label>

<textarea name="description"><?= htmlspecialchars(
    $product["description"] ?? ""
) ?></textarea>

</div>

<?php foreach (
    ["image", "image2", "image3", "image4"] as $field
): ?>

<div>

<label>
<?= ucfirst($field) ?>
</label>

<input
    type="file"
    name="<?= $field ?>"
    accept=".jpg,.jpeg,.png,.webp"
>

<?php if (!empty($product[$field])): ?>

<img
    class="current"
    src="../uploads/products/<?= htmlspecialchars(
        $product[$field]
    ) ?>"
>

<?php endif; ?>

</div>

<?php endforeach; ?>

<div class="full">

<label class="check">

<input
    type="checkbox"
    name="featured"
    value="1"
    <?= $product["featured"] ? "checked" : "" ?>
>

Featured Product

</label>

</div>

<div class="full">

<button type="submit">
Update Product
</button>

</div>

</div>

</form>

</div>

</main>

</body>

</html>