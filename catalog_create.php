<?php
require_once __DIR__.'/config/db.php';
$pageTitle='Crear catálogo';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $name=trim($_POST['name']??''); $category=trim($_POST['category']??''); $description=trim($_POST['description']??'');
 if($name!==''){ $s=$pdo->prepare("INSERT INTO catalogs(name,category,description) VALUES(?,?,?)"); $s->execute([$name,$category,$description]); header('Location: catalog_view.php?id='.$pdo->lastInsertId()); exit; }
}
require __DIR__.'/includes/header.php'; ?>
<div class="page-heading"><div><span class="eyebrow">NUEVO PROYECTO</span><h1>Crear catálogo</h1><p>Configura la información inicial de tu nuevo catálogo.</p></div></div>
<?php require __DIR__.'/catalog_form.php'; require __DIR__.'/includes/footer.php'; ?>