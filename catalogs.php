<?php
require_once __DIR__.'/config/db.php';
$pageTitle='Mis catálogos';
$catalogs=$pdo->query("SELECT c.*, COUNT(p.id) product_count FROM catalogs c LEFT JOIN products p ON p.catalog_id=c.id GROUP BY c.id ORDER BY c.updated_at DESC")->fetchAll();
require __DIR__.'/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">BIBLIOTECA</span><h1>Mis catálogos</h1><p>Todos tus proyectos organizados en un solo lugar.</p></div><a class="btn btn-primary" href="catalog_create.php">＋ Nuevo catálogo</a></div>
<div class="toolbar"><div class="search-box"><span>⌕</span><input id="catalogSearch" type="search" placeholder="Buscar catálogo..."></div><span class="muted"><?= count($catalogs) ?> catálogo(s)</span></div>
<?php if (!$catalogs): ?><div class="empty-state"><div class="empty-icon">▤</div><h3>Tu biblioteca está vacía</h3><p>Empieza creando un catálogo para tus productos o servicios.</p><a class="btn btn-primary" href="catalog_create.php">Crear catálogo</a></div>
<?php else: ?><div class="catalog-grid" id="catalogGrid"><?php foreach($catalogs as $c): ?>
<article class="catalog-card searchable"><div class="catalog-cover"><span class="cover-mark">CS</span><span class="cover-label">CATÁLOGO</span></div><div class="catalog-card-body"><div class="card-top"><span class="pill"><?= htmlspecialchars($c['category']?:'General') ?></span><span class="muted"><?= (int)$c['product_count'] ?> productos</span></div><h3><?= htmlspecialchars($c['name']) ?></h3><p><?= htmlspecialchars($c['description']?:'Sin descripción') ?></p><div class="card-actions"><a class="btn btn-light" href="catalog_view.php?id=<?= $c['id'] ?>">Ver detalles</a><a class="icon-link" href="catalog_edit.php?id=<?= $c['id'] ?>">✎</a><form method="post" action="catalog_delete.php" onsubmit="return confirm('¿Eliminar este catálogo y sus productos?')"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="icon-link danger" title="Eliminar">⌫</button></form></div></div></article>
<?php endforeach; ?></div><?php endif; ?>
<?php require __DIR__.'/includes/footer.php'; ?>