<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Panel principal';
$totalCatalogs = (int)$pdo->query("SELECT COUNT(*) FROM catalogs")->fetchColumn();
$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$recent = $pdo->query("SELECT c.*, COUNT(p.id) AS product_count FROM catalogs c LEFT JOIN products p ON p.catalog_id=c.id GROUP BY c.id ORDER BY c.updated_at DESC LIMIT 6")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading">
  <div><span class="eyebrow">RESUMEN GENERAL</span><h1>Bienvenido a Catalog Studio</h1><p>Administra tus catálogos y convierte tus ideas en presentaciones profesionales.</p></div>
  <a class="btn btn-primary" href="catalog_create.php">＋ Crear catálogo</a>
</div>
<section class="stats-grid">
  <article class="stat-card"><div class="stat-icon purple">▤</div><div><span>Catálogos creados</span><strong><?= $totalCatalogs ?></strong><small>En tu espacio de trabajo</small></div></article>
  <article class="stat-card"><div class="stat-icon green">◇</div><div><span>Productos registrados</span><strong><?= $totalProducts ?></strong><small>Disponibles para organizar</small></div></article>
  <article class="stat-card"><div class="stat-icon orange">↗</div><div><span>Plataforma</span><strong>Activa</strong><small>Entorno local · Laragon</small></div></article>
</section>
<div class="section-heading"><div><span class="eyebrow">TU CONTENIDO</span><h2>Catálogos recientes</h2></div><a class="text-link" href="catalogs.php">Ver todos →</a></div>
<?php if (!$recent): ?>
  <div class="empty-state"><div class="empty-icon">▤</div><h3>Aún no tienes catálogos</h3><p>Crea tu primer catálogo y comienza a agregar productos.</p><a class="btn btn-primary" href="catalog_create.php">Crear mi primer catálogo</a></div>
<?php else: ?>
<div class="catalog-grid">
<?php foreach ($recent as $c): ?>
  <article class="catalog-card"><div class="catalog-cover"><span class="cover-mark">CS</span><span class="cover-label">CATÁLOGO</span></div><div class="catalog-card-body"><div class="card-top"><span class="pill"><?= htmlspecialchars($c['category'] ?: 'General') ?></span><span class="muted"><?= (int)$c['product_count'] ?> productos</span></div><h3><?= htmlspecialchars($c['name']) ?></h3><p><?= htmlspecialchars($c['description'] ?: 'Sin descripción') ?></p><div class="card-actions"><a class="btn btn-light" href="catalog_view.php?id=<?= $c['id'] ?>">Abrir catálogo</a><a class="icon-link" title="Editar" href="catalog_edit.php?id=<?= $c['id'] ?>">✎</a></div></div></article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>