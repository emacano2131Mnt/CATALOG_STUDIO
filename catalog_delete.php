<?php
require_once __DIR__.'/config/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){ $id=(int)($_POST['id']??0);$s=$pdo->prepare("DELETE FROM catalogs WHERE id=?");$s->execute([$id]);}
header('Location: catalogs.php');exit;