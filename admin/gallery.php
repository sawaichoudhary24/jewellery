<?php
session_start();
if(!isset($_SESSION["admin_id"])){header("Location:login.php");exit;}
require_once "../db.php";
$dir="../uploads/gallery/";
if(!is_dir($dir)) mkdir($dir,0777,true);
$error="";
if(isset($_GET["delete"])){
 $id=(int)$_GET["delete"];
 $q=$conn->prepare("SELECT image FROM gallery_items WHERE id=?"); $q->bind_param("i",$id); $q->execute();
 $r=$q->get_result()->fetch_assoc();
 if($r && !empty($r["image"]) && is_file($dir.$r["image"])) @unlink($dir.$r["image"]);
 $q=$conn->prepare("DELETE FROM gallery_items WHERE id=?"); $q->bind_param("i",$id); $q->execute();
 header("Location:gallery.php"); exit;
}
if($_SERVER["REQUEST_METHOD"]==="POST"){
 $title=trim($_POST["title"]??""); $desc=trim($_POST["description"]??"");
 $status=(($_POST["status"]??"active")==="inactive")?"inactive":"active";
 if($title==="" || empty($_FILES["image"]["name"])) {
  $error="Title and image are required.";
 } else {
  $f=$_FILES["image"]; $type=mime_content_type($f["tmp_name"]);
  $allowed=["image/jpeg","image/png","image/webp"];
  if(!in_array($type,$allowed,true)) {
   $error="Only JPG, PNG and WEBP images are allowed.";
  } else {
   $ext=strtolower(pathinfo($f["name"],PATHINFO_EXTENSION)); $name=uniqid("gallery_",true).".".$ext;
   if(move_uploaded_file($f["tmp_name"],$dir.$name)){
    $q=$conn->prepare("INSERT INTO gallery_items(title,image,description,status) VALUES(?,?,?,?)");
    $q->bind_param("ssss",$title,$name,$desc,$status); $q->execute();
    header("Location:gallery.php?added=1"); exit;
   }
   $error="Image upload failed.";
  }
 }
}
$items=$conn->query("SELECT * FROM gallery_items ORDER BY id DESC");
?><!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Gallery | Admin</title><style>
body{margin:0;font:14px Arial;background:#f6f6f6;color:#222}.sidebar{position:fixed;width:240px;height:100vh;background:#181818;color:#fff;padding:25px 15px;box-sizing:border-box}.sidebar h2{text-align:center;margin-bottom:30px}.sidebar a{display:block;color:#fff;text-decoration:none;padding:13px 15px;margin-bottom:5px;border-radius:5px}.sidebar a:hover{background:#b8860b}.main{margin-left:240px;padding:30px}.grid{display:grid;grid-template-columns:320px 1fr;gap:25px}.box{background:#fff;padding:25px;border-radius:10px}.box h2{margin-top:0}label{display:block;font-weight:bold;font-size:12px;margin:15px 0 7px}input,textarea,select{width:100%;box-sizing:border-box;padding:11px;border:1px solid #ddd;border-radius:5px}textarea{min-height:100px}button{background:#b8860b;color:#fff;border:0;padding:12px 20px;border-radius:5px;margin-top:15px;cursor:pointer}.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.card{background:#fff;padding:12px}.card img{width:100%;height:180px;object-fit:cover}.card h3{margin:10px 0 5px}.delete{color:#b00020;text-decoration:none;font-size:12px}@media(max-width:800px){.sidebar{position:relative;width:100%;height:auto}.main{margin-left:0}.grid,.cards{grid-template-columns:1fr}}
</style></head><body><aside class="sidebar"><h2>Jewellery Admin</h2><a href="dashboard.php">Dashboard</a><a href="products.php">Products</a><a href="add-product.php">Add Product</a><a href="categories.php">Categories</a><a href="gallery.php">Gallery</a><a href="blog.php">Blog</a><a href="orders.php">Orders</a><a href="users.php">Users</a><a href="messages.php">Messages</a><a href="logout.php">Logout</a></aside><main class="main"><h1>Gallery Management</h1><div class="grid"><div class="box"><h2>Add Gallery Image</h2><?php if($error):?><p style="color:#b00020"><?=htmlspecialchars($error)?></p><?php endif;?><form method="POST" enctype="multipart/form-data"><label>Title</label><input name="title" required><label>Image</label><input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required><label>Description</label><textarea name="description"></textarea><label>Status</label><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select><button>Add to Gallery</button></form></div><div class="cards"><?php while($item=$items->fetch_assoc()):?><div class="card"><img src="../uploads/gallery/<?=htmlspecialchars($item["image"])?>" alt=""><h3><?=htmlspecialchars($item["title"])?></h3><p><?=htmlspecialchars($item["description"]??"")?></p><a class="delete" href="gallery.php?delete=<?=$item["id"]?>" onclick="return confirm('Delete this gallery item?')">Delete</a></div><?php endwhile;?></div></div></main></body></html>