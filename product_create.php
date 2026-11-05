<?php
require_once __DIR__.'/config/db.php';$pageTitle='Agregar producto';$selectedCatalog=(int)($_GET['catalog_id']??0);
$catalogOptions=$pdo->query("SELECT id,name FROM catalogs ORDER BY name")->fetchAll();$categories=$pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
if(!$catalogOptions){header('Location: catalog_create.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $catalogId=(int)$_POST['catalog_id'];$name=trim($_POST['name']??'');$image=uploadImage();
 if($name!==''){ $s=$pdo->prepare("INSERT INTO products(catalog_id,category_id,name,description,price,image) VALUES(?,?,?,?,?,?)");$s->execute([$catalogId,($_POST['category_id']??'')?:null,$name,trim($_POST['description']??''),(float)$_POST['price'],$image]);header('Location: catalog_view.php?id='.$catalogId);exit;}
}
require __DIR__.'/includes/header.php';require __DIR__.'/product_form.php';require __DIR__.'/includes/footer.php';
function uploadImage(){if(empty($_FILES['image']['name']))return null;if($_FILES['image']['error']!==UPLOAD_ERR_OK||$_FILES['image']['size']>3145728) return null;$ext=strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));if(!in_array($ext,['jpg','jpeg','png','webp']))return null;$name=bin2hex(random_bytes(12)).'.'.$ext;move_uploaded_file($_FILES['image']['tmp_name'],__DIR__.'/uploads/'.$name);return $name;}
