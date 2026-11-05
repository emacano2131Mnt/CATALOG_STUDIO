<?php
require_once __DIR__.'/config/db.php';
$id=(int)($_GET['id']??0); $s=$pdo->prepare("SELECT * FROM catalogs WHERE id=?");$s->execute([$id]);$catalog=$s->fetch();
if(!$catalog){http_response_code(404);exit('Catálogo no encontrado');}
$pageTitle='Editar catálogo';
if($_SERVER['REQUEST_METHOD']==='POST'){ $name=trim($_POST['name']??''); if($name!==''){ $s=$pdo->prepare("UPDATE catalogs SET name=?,category=?,description=? WHERE id=?");$s->execute([$name,trim($_POST['category']??''),trim($_POST['description']??''),$id]);header('Location: catalog_view.php?id='.$id);exit;}}
require __DIR__.'/includes/header.php'; ?>
<div class="page-heading"><div><span class="eyebrow">CONFIGURACIÓN</span><h1>Editar catálogo</h1><p>Actualiza la información de tu proyecto.</p></div></div>
<?php require __DIR__.'/catalog_form.php'; require __DIR__.'/includes/footer.php'; ?>