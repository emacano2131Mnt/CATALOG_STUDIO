<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Catalog Studio';
}
$current = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$catalogRoutes = ['catalogs.php', 'catalog_create.php', 'catalog_edit.php', 'catalog_view.php', 'catalog_designer.php'];
$productRoutes = ['products.php', 'product_create.php', 'product_edit.php'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f7f7fb">
  <title><?= h($pageTitle) ?> | Catalog Studio</title>
  <link rel="stylesheet" href="assets/css/style.css?v=7">
</head>
<body>
<div class="app-shell" id="appShell">
  <aside class="sidebar" id="appSidebar" aria-label="Navegación principal">
    <a class="brand" href="index.php" aria-label="Catalog Studio, inicio">
      <span class="brand-symbol" aria-hidden="true">C</span>
      <span class="brand-name">catalog<span class="brand-light">studio</span><small>PRODUCT WORKSPACE</small></span>
    </a>

    <div class="side-label">ESPACIO DE TRABAJO</div>
    <nav class="nav-menu">
      <a class="<?= $current === 'index.php' ? 'active' : '' ?>" href="index.php" <?= $current === 'index.php' ? 'aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg>
        <span>Dashboard</span>
      </a>
      <a class="<?= in_array($current, $catalogRoutes, true) ? 'active' : '' ?>" href="catalogs.php" <?= in_array($current, $catalogRoutes, true) ? 'aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16M8 7h8M8 11h8"/></svg>
        <span>Catálogos</span>
      </a>
      <a class="<?= in_array($current, $productRoutes, true) ? 'active' : '' ?>" href="products.php" <?= in_array($current, $productRoutes, true) ? 'aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8.5 4.7v8.6L12 21l-8.5-4.7V7.7z"/><path d="m3.8 7.8 8.2 4.7 8.2-4.7M12 12.5V21"/></svg>
        <span>Productos</span>
      </a>
    </nav>

    <div class="side-label side-label-actions">CREAR</div>
    <nav class="nav-menu nav-menu-secondary" aria-label="Acciones rápidas">
      <a class="<?= $current === 'catalog_create.php' ? 'active' : '' ?>" href="catalog_create.php">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        <span>Nuevo catálogo</span>
      </a>
      <a class="<?= $current === 'product_create.php' ? 'active' : '' ?>" href="product_create.php">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/><circle cx="12" cy="12" r="9"/></svg>
        <span>Nuevo producto</span>
      </a>
    </nav>

    <div class="sidebar-bottom">
      <div class="help-card">
        <span class="help-icon" aria-hidden="true">✦</span>
        <strong>Tu próximo catálogo</strong>
        <p>Organiza tus productos y comparte una presentación profesional.</p>
        <a href="catalog_create.php">Crear catálogo <span aria-hidden="true">→</span></a>
      </div>
      <div class="workspace-note"><span class="workspace-avatar">C</span><span><strong>Catalog Studio</strong><small>Espacio local</small></span><span class="status-dot" aria-label="Conectado"></span></div>
    </div>
  </aside>

  <button class="sidebar-backdrop" id="sidebarBackdrop" type="button" aria-label="Cerrar menú" tabindex="-1"></button>

  <div class="main-column">
    <header class="topbar">
      <button class="menu-toggle" id="menuToggle" type="button" aria-label="Abrir menú" aria-controls="appSidebar" aria-expanded="false">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="breadcrumb"><a href="index.php">Workspace</a><span aria-hidden="true">/</span><strong><?= h($pageTitle) ?></strong></div>
      <div class="topbar-right"><span class="environment-pill"><span class="status-dot"></span> Entorno local</span><span class="top-avatar" aria-hidden="true">C</span></div>
    </header>

    <main class="content-wrap" id="mainContent">
      <?php render_flash_message(); ?>
