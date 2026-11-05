<?php
if (!isset($pageTitle)) $pageTitle = 'Catalog Studio';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> | Catalog Studio</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
<aside class="sidebar">
  <a class="brand" href="index.php"><span class="brand-symbol">C</span><span>catalog<span class="brand-light">studio</span><small>CREATIVE WORKSPACE</small></span></a>
  <div class="side-label">MENÚ PRINCIPAL</div>
  <nav class="nav-menu">
    <a class="<?= $current==='index.php'?'active':'' ?>" href="index.php"><span>▦</span> Panel principal</a>
    <a class="<?= in_array($current,['catalogs.php','catalog_create.php','catalog_edit.php','catalog_view.php'])?'active':'' ?>" href="catalogs.php"><span>▤</span> Mis catálogos</a>
    <a class="<?= $current==='products.php'?'active':'' ?>" href="products.php"><span>◇</span> Productos</a>
  </nav>
  <div class="sidebar-bottom"><div class="help-card"><span class="help-icon">✦</span><strong>Un catálogo que destaca</strong><p>Organiza tus productos y comparte tu propuesta con estilo.</p></div><div class="profile"><div class="avatar">U</div><div><strong>Mi espacio</strong><small>Administrador</small></div><span class="profile-dot"></span></div></div>
</aside>
<main class="main-content">
<header class="topbar"><div class="breadcrumb">Workspace <span>/</span> <?= htmlspecialchars($pageTitle) ?></div><div class="topbar-right"><span class="status-dot"></span><span>Servidor local</span><div class="top-avatar">U</div></div></header>
<div class="content-wrap">
