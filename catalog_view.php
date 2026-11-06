<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/image_upload.php';
require_once __DIR__ . '/includes/product_order_store.php';

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

$statement = $pdo->prepare('SELECT p.*, cat.name AS category_name FROM products p LEFT JOIN categories cat ON cat.id = p.category_id WHERE p.catalog_id = ? ORDER BY p.created_at DESC, p.id DESC');
$statement->execute([$id]);
$products = catalog_product_order_apply($statement->fetchAll(), $id);
$pageTitle = $catalog['name'];

require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
  <div class="page-heading-copy"><span class="eyebrow">DETALLE DEL CATÁLOGO</span><h1><?= h($catalog['name']) ?></h1><p><?= h($catalog['description'] ?: 'Administra los productos de este catálogo.') ?></p></div>
  <div class="heading-actions">
    <a class="btn btn-light" href="catalog_designer.php?id=<?= $id ?>">✦ Personalizar diseño</a>
    <form method="post" action="catalog_duplicate.php">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
      <button class="btn btn-light" type="submit">Duplicar catálogo</button>
    </form>
    <a class="btn btn-light" href="catalog_edit.php?id=<?= $id ?>">✎ Editar catálogo</a>
    <a class="btn btn-primary" href="product_create.php?catalog_id=<?= $id ?>">＋ Agregar producto</a>
  </div>
</section>

<div class="detail-strip">
  <span class="pill"><?= h($catalog['category'] ?: 'General') ?></span>
  <span><?= count($products) ?> productos</span>
  <?php if ($products): ?>
    <form class="selected-pdf-form" id="catalogPdfSelectionForm" method="post" action="export_pdf.php" target="_blank">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $id ?>">
      <input type="hidden" name="selection" value="selected">
      <button class="btn btn-light" type="submit">PDF de seleccionados</button>
    </form>
  <?php endif; ?>
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
    <?php foreach ($products as $productIndex => $product): ?>
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
          <label class="product-pdf-select"><input type="checkbox" name="product_ids[]" value="<?= (int)$product['id'] ?>" form="catalogPdfSelectionForm"> Incluir en PDF</label>
          <span class="muted"><?= h($product['category_name'] ?: 'Sin categoría') ?></span>
          <h3 title="<?= h($product['name']) ?>"><?= h($product['name']) ?></h3>
          <p><?= h($product['description'] ?: 'Sin descripción') ?></p>
          <div class="product-bottom">
            <strong>$<?= number_format((float)$product['price'], 2, ',', '.') ?></strong>
            <div class="inline-actions">
              <form method="post" action="product_reorder.php">
                <?= csrf_field() ?><input type="hidden" name="catalog_id" value="<?= $id ?>"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><input type="hidden" name="direction" value="up">
                <button class="icon-link" type="submit" title="Mover producto hacia arriba" aria-label="Mover producto hacia arriba" <?= $productIndex === 0 ? 'disabled' : '' ?>>↑</button>
              </form>
              <form method="post" action="product_reorder.php">
                <?= csrf_field() ?><input type="hidden" name="catalog_id" value="<?= $id ?>"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><input type="hidden" name="direction" value="down">
                <button class="icon-link" type="submit" title="Mover producto hacia abajo" aria-label="Mover producto hacia abajo" <?= $productIndex === count($products) - 1 ? 'disabled' : '' ?>>↓</button>
              </form>
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
