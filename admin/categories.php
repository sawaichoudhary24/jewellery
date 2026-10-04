<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

$message = "";
$error = "";

/* ADD CATEGORY */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_category"])) {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if ($name === "") {
        $error = "Category name is required.";
    } else {

        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        /* Check duplicate */
        $check = $conn->prepare(
            "SELECT id FROM categories WHERE name = ? OR slug = ?"
        );

        $check->bind_param("ss", $name, $slug);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {

            $error = "Category already exists.";

        } else {

            $imageName = null;

            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"] === UPLOAD_ERR_OK
            ) {

                $uploadDir = "../uploads/categories/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $extension = strtolower(
                    pathinfo(
                        $_FILES["image"]["name"],
                        PATHINFO_EXTENSION
                    )
                );

                $allowed = ["jpg", "jpeg", "png", "webp"];

                if (!in_array($extension, $allowed)) {

                    $error = "Only JPG, PNG and WEBP images are allowed.";

                } else {

                    $imageName =
                        time() . "_" .
                        uniqid() . "." .
                        $extension;

                    move_uploaded_file(
                        $_FILES["image"]["tmp_name"],
                        $uploadDir . $imageName
                    );
                }
            }

            if ($error === "") {

                $stmt = $conn->prepare("
                    INSERT INTO categories
                    (name, slug, image, description)
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "ssss",
                    $name,
                    $slug,
                    $imageName,
                    $description
                );

                if ($stmt->execute()) {
                    $message = "Category added successfully.";
                } else {
                    $error = $stmt->error;
                }
            }
        }
    }
}

/* DELETE CATEGORY */
if (isset($_GET["delete"])) {

    $id = (int) $_GET["delete"];

    $stmt = $conn->prepare(
        "DELETE FROM categories WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: categories.php?deleted=1");
    exit;
}

/* GET CATEGORIES */
$categories = $conn->query("
    SELECT *
    FROM categories
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Categories - Admin</title>

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
    background: white;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.full {
    grid-column: 1 / -1;
}

label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
}

input,
textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

textarea {
    min-height: 100px;
}

button {
    background: #b8860b;
    color: white;
    border: none;
    padding: 13px 25px;
    border-radius: 5px;
    cursor: pointer;
}

.success {
    background: #e7f7e7;
    color: #176b17;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.error {
    background: #ffe5e5;
    color: #b00020;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 5px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 13px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background: #181818;
    color: white;
}

.category-img {
    width: 70px;
    height: 70px;
    object-fit: cover;
}

.delete {
    background: #b00020;
    color: white;
    text-decoration: none;
    padding: 7px 12px;
    border-radius: 4px;
}

@media(max-width:800px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 15px;
        overflow-x: auto;
    }

    .form-grid {
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

<h1>Categories</h1>

<?php if ($message): ?>

<div class="success">
<?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>

<?php if ($error): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<!-- ADD CATEGORY -->

<div class="box">

<h2>Add Category</h2>

<form
    method="POST"
    enctype="multipart/form-data"
>

<input
    type="hidden"
    name="add_category"
    value="1"
>

<div class="form-grid">

<div>

<label>
Category Name *
</label>

<input
    type="text"
    name="name"
    placeholder="e.g. Rings"
    required
>

</div>

<div>

<label>
Category Image
</label>

<input
    type="file"
    name="image"
    accept=".jpg,.jpeg,.png,.webp"
>

</div>

<div class="full">

<label>
Description
</label>

<textarea
    name="description"
    placeholder="Category description"
></textarea>

</div>

<div class="full">

<button type="submit">
Add Category
</button>

</div>

</div>

</form>

</div>

<!-- CATEGORY LIST -->

<div class="box">

<h2>All Categories</h2>

<table>

<thead>

<tr>

<th>ID</th>

<th>Image</th>

<th>Name</th>

<th>Slug</th>

<th>Status</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php if ($categories && $categories->num_rows > 0): ?>

<?php while ($category = $categories->fetch_assoc()): ?>

<tr>

<td>
<?= $category["id"] ?>
</td>

<td>

<?php if (!empty($category["image"])): ?>

<img
    class="category-img"
    src="../uploads/categories/<?= htmlspecialchars(
        $category["image"]
    ) ?>"
>

<?php else: ?>

No Image

<?php endif; ?>

</td>

<td>
<?= htmlspecialchars($category["name"]) ?>
</td>

<td>
<?= htmlspecialchars($category["slug"]) ?>
</td>

<td>
<?= htmlspecialchars($category["status"]) ?>
</td>

<td>

<a
    class="delete"
    href="categories.php?delete=<?= $category["id"] ?>"
    onclick="return confirm('Delete this category?')"
>
Delete
</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="6">
No categories found.
</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</main>

</body>

</html>