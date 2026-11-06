<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/image_upload.php';

$pageTitle = 'Productos';
$products = $pdo->query('SELECT p.*, c.name AS catalog_name, cat.name AS category_name FROM products p JOIN catalogs c ON c.id = p.catalog_id LEFT JOIN categories cat ON cat.id = p.category_id ORDER BY p.created_at DESC')->fetchAll();
$catalogFilters = $pdo->query('SELECT id, name FROM catalogs ORDER BY name')->fetchAll();
$categoryFilters = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
  <div class="page-heading-copy"><span class="eyebrow">INVENTARIO</span><h1>Productos</h1><p>Consulta y administra los productos de todos tus catálogos.</p></div>
  <a class="btn btn-primary" href="product_create.php">＋ Agregar producto</a>
</section>

<div class="toolbar">
  <label class="search-box" for="productSearch">
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>
    <input id="productSearch" type="search" placeholder="Buscar por nombre de producto…" autocomplete="off">
  </label>
  <div class="product-filters">
    <label class="filter-control">Catálogo
      <select id="productCatalogFilter"><option value="">Todos los catálogos</option><?php foreach ($catalogFilters as $filter): ?><option value="<?= (int)$filter['id'] ?>"><?= h($filter['name']) ?></option><?php endforeach; ?></select>
    </label>
    <label class="filter-control">Categoría
      <select id="productCategoryFilter"><option value="">Todas las categorías</option><?php foreach ($categoryFilters as $filter): ?><option value="<?= (int)$filter['id'] ?>"><?= h($filter['name']) ?></option><?php endforeach; ?></select>
    </label>
  </div>
  <span class="muted"><?= count($products) ?> producto(s)</span>
</div>
<p class="empty-search" id="productSearchEmpty" role="status" hidden>No hay productos que coincidan con la búsqueda.</p>

<?php if (!$products): ?>
  <div class="empty-state">
    <div class="empty-icon" aria-hidden="true">◇</div>
    <h3>Todavía no hay productos</h3>
    <p>Agrega un producto y asígnalo a uno de tus catálogos.</p>
    <a class="btn btn-primary" href="product_create.php">Agregar producto</a>
  </div>
<?php else: ?>
  <div class="panel table-panel">
    <div class="table-scroll" tabindex="0" role="region" aria-label="Inventario de productos, desplazamiento horizontal disponible en pantallas pequeñas">
      <table>
        <thead><tr><th>Producto</th><th>Catálogo</th><th>Categoría</th><th>Precio</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($products as $product): ?>
          <?php $imageUrl = product_image_url($product['image']); ?>
          <tr class="product-row" data-product-name="<?= h(mb_strtolower($product['name'], 'UTF-8')) ?>" data-catalog-id="<?= (int)$product['catalog_id'] ?>" data-category-id="<?= $product['category_id'] === null ? '' : (int)$product['category_id'] ?>">
            <td><div class="product-listing-name"><div class="product-thumb"><?php if ($imageUrl !== null): ?><img src="<?= h($imageUrl) ?>" alt=""><?php else: ?><span class="product-thumb-placeholder" aria-label="Sin fotografía">◇</span><?php endif; ?></div><strong><?= h($product['name']) ?></strong></div></td>
            <td><a class="text-link" href="catalog_view.php?id=<?= (int)$product['catalog_id'] ?>"><?= h($product['catalog_name']) ?></a></td>
            <td><?= h($product['category_name'] ?: '—') ?></td>
            <td><strong>$<?= number_format((float)$product['price'], 2, ',', '.') ?></strong></td>
            <td><div class="table-actions">
              <a class="icon-link" title="Editar producto" aria-label="Editar producto" href="product_edit.php?id=<?= (int)$product['id'] ?>">✎</a>
              <form method="post" action="product_delete.php" onsubmit="return confirm('¿Eliminar este producto? Esta acción no se puede deshacer.');">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                <button class="icon-link danger" type="submit" title="Eliminar producto" aria-label="Eliminar producto">⌫</button>
              </form>
            </div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
