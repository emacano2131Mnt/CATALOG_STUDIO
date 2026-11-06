<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/catalog_design_store.php';
require_once __DIR__ . '/includes/product_order_store.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalogs.php');
    exit;
}
if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    reject_request('La sesión venció o el token CSRF no es válido. Recarga la lista e inténtalo de nuevo.', 403);
}

$sourceId = positive_id($_POST['id'] ?? null);
if ($sourceId === null) reject_request('El identificador del catálogo no es válido.');

$statement = $pdo->prepare('SELECT id, name, category, description FROM catalogs WHERE id = ?');
$statement->execute([$sourceId]);
$sourceCatalog = $statement->fetch();
if (!$sourceCatalog) reject_request('No se encontró el catálogo solicitado.', 404);

$statement = $pdo->prepare('SELECT id, category_id, name, description, price, image FROM products WHERE catalog_id = ? ORDER BY created_at ASC, id ASC');
$statement->execute([$sourceId]);
$sourceProducts = $statement->fetchAll();
$sourceIds = array_map('intval', array_column($sourceProducts, 'id'));
$sourceOrder = catalog_product_order_load($sourceId, $sourceIds);
$productsById = [];
foreach ($sourceProducts as $product) $productsById[(int)$product['id']] = $product;
$sourceProducts = array_map(static fn(int $productId): array => $productsById[$productId], $sourceOrder);
$newId = null;

try {
    $pdo->beginTransaction();
    $copyName = mb_substr('Copia de ' . $sourceCatalog['name'], 0, 120, 'UTF-8');
    $statement = $pdo->prepare('INSERT INTO catalogs (name, category, description) VALUES (?, ?, ?)');
    $statement->execute([$copyName, $sourceCatalog['category'], $sourceCatalog['description']]);
    $newId = (int)$pdo->lastInsertId();

    $insertProduct = $pdo->prepare('INSERT INTO products (catalog_id, category_id, name, description, price, image) VALUES (?, ?, ?, ?, ?, ?)');
    $idMap = [];
    foreach ($sourceProducts as $product) {
        $insertProduct->execute([$newId, $product['category_id'], $product['name'], $product['description'], $product['price'], $product['image']]);
        $idMap[(int)$product['id']] = (int)$pdo->lastInsertId();
    }

    if (is_file(catalog_design_path($sourceId))) {
        $sourceDesign = catalog_design_load($sourceId, $sourceCatalog);
        $designError = catalog_design_save($newId, $sourceDesign);
        if ($designError !== null) throw new RuntimeException($designError);
    }

    if (is_file(catalog_product_order_path($sourceId))) {
        $copiedOrder = array_values(array_map(static fn(int $sourceProductId): int => $idMap[$sourceProductId], $sourceOrder));
        $orderError = catalog_product_order_save($newId, $copiedOrder);
        if ($orderError !== null) throw new RuntimeException($orderError);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($newId !== null) {
        $designPath = catalog_design_path($newId);
        if (is_file($designPath)) @unlink($designPath);
        $designLockPath = catalog_design_directory() . DIRECTORY_SEPARATOR . 'catalog-' . $newId . '.lock';
        if (is_file($designLockPath)) @unlink($designLockPath);
        catalog_product_order_delete($newId);
    }
    error_log('Catalog Studio could not duplicate catalog id ' . $sourceId . '.');
    flash_set('error', 'No fue posible duplicar el catálogo. No se copiaron imágenes ni se modificó el catálogo original.');
    header('Location: catalogs.php');
    exit;
}

flash_set('success', 'Se duplicó el catálogo y sus productos. Las fotografías se comparten sin crear copias físicas.');
header('Location: catalog_view.php?id=' . $newId);
exit;
