<?php
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Catálogos';
$catalogs = $pdo->query('SELECT c.*, COUNT(p.id) AS product_count FROM catalogs c LEFT JOIN products p ON p.catalog_id = c.id GROUP BY c.id ORDER BY c.updated_at DESC')->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
  <div class="page-heading-copy"><span class="eyebrow">BIBLIOTECA</span><h1>Catálogos</h1><p>Todos tus proyectos, organizados en un solo lugar.</p></div>
  <a class="btn btn-primary" href="catalog_create.php">＋ Nuevo catálogo</a>
</section>

<div class="toolbar">
  <label class="search-box" for="catalogSearch">
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>
    <input id="catalogSearch" type="search" placeholder="Buscar por nombre o categoría…" autocomplete="off">
  </label>
  <span class="muted"><?= count($catalogs) ?> catálogo(s)</span>
</div>
<p class="empty-search" id="catalogSearchEmpty" role="status" hidden>No hay catálogos que coincidan con la búsqueda.</p>

<?php if (!$catalogs): ?>
  <div class="empty-state">
    <div class="empty-icon" aria-hidden="true">▤</div>
    <h3>Tu biblioteca está vacía</h3>
    <p>Crea un catálogo para comenzar a organizar tus productos o servicios.</p>
    <a class="btn btn-primary" href="catalog_create.php">Crear catálogo</a>
  </div>
<?php else: ?>
  <div class="catalog-grid" id="catalogGrid">
    <?php foreach ($catalogs as $catalog): ?>
      <article class="catalog-card searchable">
        <div class="catalog-cover"><span class="cover-mark" aria-hidden="true">CS</span><span class="cover-label">CATÁLOGO</span></div>
        <div class="catalog-card-body">
          <div class="card-top"><span class="pill"><?= h($catalog['category'] ?: 'General') ?></span><span class="muted"><?= (int)$catalog['product_count'] ?> productos</span></div>
          <h3 title="<?= h($catalog['name']) ?>"><?= h($catalog['name']) ?></h3>
          <p><?= h($catalog['description'] ?: 'Sin descripción') ?></p>
          <div class="catalog-meta"><span>Creado</span><time datetime="<?= h(date('Y-m-d', strtotime($catalog['created_at']))) ?>"><?= h(date('d/m/Y', strtotime($catalog['created_at']))) ?></time></div>
          <div class="card-actions">
            <a class="btn btn-light" href="catalog_view.php?id=<?= (int)$catalog['id'] ?>">Ver catálogo</a>
            <a class="icon-link" title="Ver PDF" aria-label="Ver PDF" href="export_pdf.php?id=<?= (int)$catalog['id'] ?>" target="_blank" rel="noopener">PDF</a>
            <a class="icon-link" title="Editar catálogo" aria-label="Editar catálogo" href="catalog_edit.php?id=<?= (int)$catalog['id'] ?>">✎</a>
            <form method="post" action="catalog_delete.php" onsubmit="return confirm('¿Eliminar este catálogo y sus productos? Esta acción no se puede deshacer.');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$catalog['id'] ?>">
              <button class="icon-link danger" type="submit" title="Eliminar catálogo" aria-label="Eliminar catálogo">⌫</button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
