<?php
require_once __DIR__.'/config/db.php';
require_once __DIR__ . '/includes/image_upload.php';
$id = positive_id($_GET['id'] ?? null);
if ($id === null) {
    reject_request('El identificador del catálogo no es válido.', 404);
}
$s=$pdo->prepare("SELECT * FROM catalogs WHERE id=?");$s->execute([$id]);$catalog=$s->fetch();
if(!$catalog){reject_request('No se encontró el catálogo solicitado.', 404);}
$s=$pdo->prepare("SELECT p.*, cat.name category_name FROM products p LEFT JOIN categories cat ON cat.id=p.category_id WHERE p.catalog_id=? ORDER BY p.created_at DESC");$s->execute([$id]);$products=$s->fetchAll();
$pageTitle=$catalog['name'];require __DIR__.'/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">DETALLE DEL PROYECTO</span><h1><?= h($catalog['name']) ?></h1><p><?= h($catalog['description']?:'Administra los productos de este catálogo.') ?></p></div><div class="heading-actions"><a class="btn btn-light" href="catalog_edit.php?id=<?= $id ?>">✎ Editar</a><a class="btn btn-primary" href="product_create.php?catalog_id=<?= $id ?>">＋ Agregar producto</a></div></div>
<div class="detail-strip"><span class="pill"><?= h($catalog['category']?:'General') ?></span><span><?= count($products) ?> productos</span><a class="text-link" href="export_pdf.php?id=<?= $id ?>" target="_blank" rel="noopener">Ver PDF</a><a class="text-link" href="export_pdf.php?id=<?= $id ?>&amp;download=1">Descargar PDF</a></div>
<?php if(!$products): ?><div class="empty-state"><div class="empty-icon">◇</div><h3>Este catálogo todavía no tiene productos</h3><p>Agrega el primero para comenzar a construir tu presentación.</p><a class="btn btn-primary" href="product_create.php?catalog_id=<?= $id ?>">Agregar producto</a></div>
<?php else: ?><div class="product-grid"><?php foreach($products as $p): $imageUrl=product_image_url($p['image']); ?><article class="product-card"><div class="product-image"><?php if($imageUrl!==null): ?><img src="<?= h($imageUrl) ?>" alt="Fotografía de <?= h($p['name']) ?>"><?php else: ?><span aria-label="Sin fotografía">◇</span><?php endif; ?></div><div class="product-info"><span class="muted"><?= h($p['category_name']?:'Sin categoría') ?></span><h3><?= h($p['name']) ?></h3><p><?= h($p['description']?:'Sin descripción') ?></p><div class="product-bottom"><strong>$<?= number_format((float)$p['price'],0,',','.') ?></strong><div class="inline-actions"><a class="icon-link" href="product_edit.php?id=<?= (int)$p['id'] ?>">✎</a><form method="post" action="product_delete.php" onsubmit="return confirm('¿Eliminar producto?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="catalog_id" value="<?= $id ?>"><button class="icon-link danger">⌫</button></form></div></div></div></article><?php endforeach; ?></div><?php endif; ?>
<?php require __DIR__.'/includes/footer.php'; ?>
