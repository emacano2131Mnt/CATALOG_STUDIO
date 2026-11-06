<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/image_upload.php';

$id = positive_id($_GET['id'] ?? null);
if ($id === null) {
    reject_request('El identificador del catálogo no es válido.', 404);
}

$statement = $pdo->prepare('SELECT * FROM catalogs WHERE id = ?');
$statement->execute([$id]);
$catalog = $statement->fetch();
if (!$catalog) {
    reject_request('No se encontró el catálogo solicitado.', 404);
}

$statement = $pdo->prepare('SELECT p.*, cat.name AS category_name FROM products p LEFT JOIN categories cat ON cat.id = p.category_id WHERE p.catalog_id = ? ORDER BY p.created_at DESC');
$statement->execute([$id]);
$products = $statement->fetchAll();
$pageTitle = $catalog['name'];

require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
  <div class="page-heading-copy"><span class="eyebrow">DETALLE DEL CATÁLOGO</span><h1><?= h($catalog['name']) ?></h1><p><?= h($catalog['description'] ?: 'Administra los productos de este catálogo.') ?></p></div>
  <div class="heading-actions">
    <a class="btn btn-light" href="catalog_designer.php?id=<?= $id ?>">✦ Personalizar diseño</a>
    <a class="btn btn-light" href="catalog_edit.php?id=<?= $id ?>">✎ Editar catálogo</a>
    <a class="btn btn-primary" href="product_create.php?catalog_id=<?= $id ?>">＋ Agregar producto</a>
  </div>
</section>

<div class="detail-strip">
  <span class="pill"><?= h($catalog['category'] ?: 'General') ?></span>
  <span><?= count($products) ?> productos</span>
  <a class="text-link" href="export_pdf.php?id=<?= $id ?>" target="_blank" rel="noopener">Ver PDF</a>
  <a class="text-link" href="export_pdf.php?id=<?= $id ?>&amp;download=1">Descargar PDF</a>
</div>

<?php if (!$products): ?>
  <div class="empty-state">
    <div class="empty-icon" aria-hidden="true">◇</div>
    <h3>Este catálogo todavía no tiene productos</h3>
    <p>Agrega el primero para comenzar a construir tu presentación.</p>
    <a class="btn btn-primary" href="product_create.php?catalog_id=<?= $id ?>">Agregar producto</a>
  </div>
<?php else: ?>
  <div class="product-grid">
    <?php foreach ($products as $product): ?>
      <?php $imageUrl = product_image_url($product['image']); ?>
      <article class="product-card">
        <div class="product-image">
          <?php if ($imageUrl !== null): ?>
            <img src="<?= h($imageUrl) ?>" alt="Fotografía de <?= h($product['name']) ?>" loading="lazy">
          <?php else: ?>
            <span aria-label="Sin fotografía">◇</span>
          <?php endif; ?>
        </div>
        <div class="product-info">
          <span class="muted"><?= h($product['category_name'] ?: 'Sin categoría') ?></span>
          <h3 title="<?= h($product['name']) ?>"><?= h($product['name']) ?></h3>
          <p><?= h($product['description'] ?: 'Sin descripción') ?></p>
          <div class="product-bottom">
            <strong>$<?= number_format((float)$product['price'], 2, ',', '.') ?></strong>
            <div class="inline-actions">
              <a class="icon-link" title="Editar producto" aria-label="Editar producto" href="product_edit.php?id=<?= (int)$product['id'] ?>">✎</a>
              <form method="post" action="product_delete.php" onsubmit="return confirm('¿Eliminar este producto? Esta acción no se puede deshacer.');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                <input type="hidden" name="catalog_id" value="<?= $id ?>">
                <button class="icon-link danger" type="submit" title="Eliminar producto" aria-label="Eliminar producto">⌫</button>
              </form>
            </div>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
