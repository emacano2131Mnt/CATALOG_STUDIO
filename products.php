<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/image_upload.php';

$pageTitle = 'Productos';
$products = $pdo->query('SELECT p.*, c.name catalog_name, cat.name category_name FROM products p JOIN catalogs c ON c.id = p.catalog_id LEFT JOIN categories cat ON cat.id = p.category_id ORDER BY p.created_at DESC')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading">
  <div><span class="eyebrow">INVENTARIO</span><h1>Productos</h1><p>Consulta y administra los productos registrados en todos tus catálogos.</p></div>
  <a class="btn btn-primary" href="product_create.php">＋ Agregar producto</a>
</div>
<div class="toolbar"><div class="search-box"><span>⌕</span><input id="productSearch" type="search" placeholder="Buscar producto o catálogo..."></div><span class="muted"><?= count($products) ?> producto(s)</span></div>
<?php if (!$products): ?>
  <div class="empty-state"><div class="empty-icon">◇</div><h3>Todavía no hay productos registrados</h3><p>Agrega un producto y asígnalo a uno de tus catálogos.</p><a class="btn btn-primary" href="product_create.php">Agregar producto</a></div>
<?php else: ?>
  <div class="panel table-panel"><table><thead><tr><th>Producto</th><th>Catálogo</th><th>Categoría</th><th>Precio</th><th>Acciones</th></tr></thead><tbody>
  <?php foreach ($products as $product): ?>
    <?php $imageUrl = product_image_url($product['image']); ?>
    <tr class="product-row">
      <td><div class="product-listing-name"><div class="product-thumb"><?php if ($imageUrl !== null): ?><img src="<?= h($imageUrl) ?>" alt=""><?php else: ?><span class="product-thumb-placeholder" aria-label="Sin fotografía">◇</span><?php endif; ?></div><strong><?= h($product['name']) ?></strong></div></td>
      <td><a class="text-link" href="catalog_view.php?id=<?= (int)$product['catalog_id'] ?>"><?= h($product['catalog_name']) ?></a></td>
      <td><?= h($product['category_name'] ?: '—') ?></td>
      <td>$<?= number_format((float)$product['price'], 0, ',', '.') ?></td>
      <td><div class="table-actions">
        <a class="icon-link" title="Editar producto" aria-label="Editar producto" href="product_edit.php?id=<?= (int)$product['id'] ?>">✎</a>
        <form method="post" action="product_delete.php" onsubmit="return confirm('¿Eliminar este producto? Esta acción no se puede deshacer.');">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
          <button class="icon-link danger" type="submit" title="Eliminar producto" aria-label="Eliminar producto">⌫</button>
        </form>
      </div></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
