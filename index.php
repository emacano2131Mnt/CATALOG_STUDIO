<?php
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Dashboard';
$totalCatalogs = (int)$pdo->query('SELECT COUNT(*) FROM catalogs')->fetchColumn();
$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCategories = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$recent = $pdo->query('SELECT c.*, COUNT(p.id) AS product_count FROM catalogs c LEFT JOIN products p ON p.catalog_id = c.id GROUP BY c.id ORDER BY c.updated_at DESC LIMIT 6')->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
  <div class="page-heading-copy">
    <span class="eyebrow">RESUMEN GENERAL</span>
    <h1>Tu espacio creativo</h1>
    <p>Administra tus catálogos y organiza tus productos desde un solo lugar.</p>
  </div>
  <div class="heading-actions">
    <a class="btn btn-light" href="product_create.php">＋ Agregar producto</a>
    <a class="btn btn-primary" href="catalog_create.php">＋ Crear catálogo</a>
  </div>
</section>

<section class="stats-grid" aria-label="Indicadores del espacio de trabajo">
  <article class="stat-card">
    <div class="stat-icon purple" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16M8 7h8"/></svg></div>
    <div><span>Catálogos</span><strong><?= $totalCatalogs ?></strong><small>Proyectos en tu espacio</small></div>
  </article>
  <article class="stat-card">
    <div class="stat-icon green" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 8.5 4.7v8.6L12 21l-8.5-4.7V7.7z"/><path d="m3.8 7.8 8.2 4.7 8.2-4.7M12 12.5V21"/></svg></div>
    <div><span>Productos</span><strong><?= $totalProducts ?></strong><small>Elementos en inventario</small></div>
  </article>
  <article class="stat-card">
    <div class="stat-icon orange" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/><circle cx="8" cy="6" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="10" cy="18" r="2"/></svg></div>
    <div><span>Categorías</span><strong><?= $totalCategories ?></strong><small>Opciones disponibles</small></div>
  </article>
</section>

<section aria-labelledby="recentHeading">
  <div class="section-heading">
    <div><span class="eyebrow">ACTIVIDAD RECIENTE</span><h2 id="recentHeading">Catálogos actualizados</h2></div>
    <a class="text-link" href="catalogs.php">Ver todos <span aria-hidden="true">→</span></a>
  </div>

  <?php if (!$recent): ?>
    <div class="empty-state">
      <div class="empty-icon" aria-hidden="true">▤</div>
      <h3>Aún no tienes catálogos</h3>
      <p>Crea tu primer catálogo y comienza a organizar tus productos.</p>
      <a class="btn btn-primary" href="catalog_create.php">Crear mi primer catálogo</a>
    </div>
  <?php else: ?>
    <div class="catalog-grid">
      <?php foreach ($recent as $catalog): ?>
        <article class="catalog-card">
          <div class="catalog-cover"><span class="cover-mark" aria-hidden="true">CS</span><span class="cover-label">CATÁLOGO</span></div>
          <div class="catalog-card-body">
            <div class="card-top"><span class="pill"><?= h($catalog['category'] ?: 'General') ?></span><span class="muted"><?= (int)$catalog['product_count'] ?> productos</span></div>
            <h3 title="<?= h($catalog['name']) ?>"><?= h($catalog['name']) ?></h3>
            <p><?= h($catalog['description'] ?: 'Sin descripción') ?></p>
            <div class="catalog-meta"><span>Actualizado</span><time datetime="<?= h(date('Y-m-d', strtotime($catalog['updated_at']))) ?>"><?= h(date('d/m/Y', strtotime($catalog['updated_at']))) ?></time></div>
            <div class="card-actions"><a class="btn btn-light" href="catalog_view.php?id=<?= (int)$catalog['id'] ?>">Abrir catálogo</a><a class="icon-link" title="Editar catálogo" aria-label="Editar catálogo" href="catalog_edit.php?id=<?= (int)$catalog['id'] ?>">✎</a></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
