<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin</title>

    <style>
        body{
            margin:0;
            font-family:Arial,sans-serif;
            background:#f6f6f6;
        }

        .sidebar{
            position:fixed;
            width:240px;
            height:100vh;
            background:#181818;
            padding:25px 15px;
            color:white;
        }

        .sidebar h2{
            text-align:center;
            margin-bottom:30px;
        }

        .sidebar a{
            display:block;
            color:white;
            text-decoration:none;
            padding:13px 15px;
            margin-bottom:5px;
            border-radius:5px;
        }

        .sidebar a:hover{
            background:#b8860b;
        }

        .main{
            margin-left:240px;
            padding:30px;
        }

        .top{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:25px;
        }

        .add-btn{
            background:#b8860b;
            color:white;
            padding:12px 20px;
            text-decoration:none;
            border-radius:5px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            background:white;
        }

        th,td{
            padding:14px;
            border-bottom:1px solid #ddd;
            text-align:left;
        }

        th{
            background:#181818;
            color:white;
        }

        img{
            width:70px;
            height:70px;
            object-fit:cover;
        }

        .edit{
            background:#333;
            color:white;
            padding:7px 12px;
            text-decoration:none;
            border-radius:4px;
        }

        .delete{
            background:#b00020;
            color:white;
            padding:7px 12px;
            text-decoration:none;
            border-radius:4px;
        }

        @media(max-width:800px){
            .sidebar{
                position:relative;
                width:auto;
                height:auto;
            }

            .main{
                margin-left:0;
                padding:15px;
                overflow-x:auto;
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

    <?php if (isset($_GET["deleted"])): ?>
<div style="background:#e8f7e8;color:#176b17;padding:12px 15px;border-radius:6px;margin-bottom:18px;">Product deleted successfully.</div>
<?php elseif (isset($_GET["error"])): ?>
<div style="background:#ffe5e5;color:#b00020;padding:12px 15px;border-radius:6px;margin-bottom:18px;">
<?= htmlspecialchars($_GET["error"]) ?>
</div>
<?php endif; ?>

<div class="top">
        <h1>Products</h1>

        <a class="add-btn" href="add-product.php">
            + Add Product
        </a>
    </div>

    <table>

        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Category</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while($product = $result->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?= $product["id"] ?>
                    </td>

                    <td>

                        <?php
                        $image = $product["image"] ?? "";
                        ?>

                        <?php if ($image): ?>

                            <img
                                src="../uploads/products/<?= htmlspecialchars($image) ?>"
                                alt=""
                            >

                        <?php else: ?>

                            No Image

                        <?php endif; ?>

                    </td>

                    <td>
                        <?= htmlspecialchars($product["name"] ?? "") ?>
                    </td>

                    <td>
                        ₹<?= number_format((float)($product["price"] ?? 0), 2) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($product["category"] ?? "") ?>
                    </td>

                    <td>

                        <a
                            class="edit"
                            href="edit-product.php?id=<?= $product["id"] ?>"
                        >
                            Edit
                        </a>

                        <a
                            class="delete"
                            href="delete-product.php?id=<?= $product["id"] ?>"
                            onclick="return confirm('Delete this product?')"
                        >
                            Delete
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="6">
                    No products found.
                </td>
            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</main>

</body>
</html>