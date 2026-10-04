<?php
require_once "db.php";
require_once "includes/header.php";
$id=(int)($_GET["id"]??0); $slug=trim($_GET["slug"]??"");
if($id>0){$st=$conn->prepare("SELECT p.*, c.name category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=? LIMIT 1");$st->bind_param("i",$id);}
else {$st=$conn->prepare("SELECT p.*, c.name category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.slug=? LIMIT 1");$st->bind_param("s",$slug);}
$st->execute(); $product=$st->get_result()->fetch_assoc();
?>
<main class="page-content">
<div class="container">
<?php if(!$product): ?><h1>Product not found</h1><p><a href="shop.php">Back to shop</a></p>
<?php else:
$price=(float)$product["price"]; $sale=$product["sale_price"]!==null?(float)$product["sale_price"]:0; $display=($sale>0&&$sale<$price)?$sale:$price;
$images=array_filter([$product["image"],$product["image2"],$product["image3"],$product["image4"]]);
?>
<div class="product-detail">
<div class="product-gallery"><?php foreach($images as $img): ?><img src="uploads/products/<?=htmlspecialchars($img)?>" alt="<?=htmlspecialchars($product["name"])?>"><?php endforeach; ?></div>
<div class="product-info"><p><?=htmlspecialchars($product["category_name"]??"Jewellery")?></p><h1><?=htmlspecialchars($product["name"])?></h1><h2>₹<?=number_format($display,2)?></h2>
<?php if($sale>0&&$sale<$price): ?><del>₹<?=number_format($price,2)?></del><?php endif; ?>
<p><?=nl2br(htmlspecialchars($product["description"]??$product["short_description"]??""))?></p>
<p>Stock: <?=max(0,(int)$product["stock"])?></p>
<?php if((int)$product["stock"]>0): ?><form method="post" action="add-to-cart.php"><input type="hidden" name="product_id" value="<?=$product["id"]?>"><input type="number" name="quantity" value="1" min="1" max="<?=$product["stock"]?>"><button class="btn" type="submit">Add to Cart</button></form><?php else: ?><strong>Out of stock</strong><?php endif; ?>
</div></div></div></main>
<style>.container{max-width:1200px;margin:auto;padding:40px 20px}.product-detail{display:grid;grid-template-columns:1fr 1fr;gap:40px}.product-gallery{display:grid;grid-template-columns:1fr 1fr;gap:15px}.product-gallery img{width:100%;aspect-ratio:1;object-fit:cover}.product-info h1{font-size:42px}.product-info h2{font-size:30px}.product-info input{padding:10px;width:80px}.btn{padding:12px 22px}@media(max-width:700px){.product-detail{grid-template-columns:1fr}}</style>
<?php endif; ?>
<?php require_once "includes/footer.php"; ?>