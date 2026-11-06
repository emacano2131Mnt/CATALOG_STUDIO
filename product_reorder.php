<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/product_order_store.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalogs.php');
    exit;
}
if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    reject_request('La sesión venció o el token CSRF no es válido. Recarga el catálogo e inténtalo de nuevo.', 403);
}

$catalogId = positive_id($_POST['catalog_id'] ?? null);
$productId = positive_id($_POST['product_id'] ?? null);
$direction = $_POST['direction'] ?? null;
if ($catalogId === null || $productId === null || !is_string($direction) || !in_array($direction, ['up', 'down'], true)) {
    reject_request('Los datos para mover el producto no son válidos.');
}

$statement = $pdo->prepare('SELECT id FROM products WHERE catalog_id = ? ORDER BY created_at DESC, id DESC');
$statement->execute([$catalogId]);
$defaultIds = array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
$order = catalog_product_order_load($catalogId, $defaultIds);
$position = array_search($productId, $order, true);
if ($position === false) reject_request('El producto no pertenece al catálogo solicitado.', 404);

$targetPosition = $direction === 'up' ? $position - 1 : $position + 1;
if (!isset($order[$targetPosition])) {
    flash_set('warning', 'El producto ya está en el extremo de la lista.');
    header('Location: catalog_view.php?id=' . $catalogId);
    exit;
}

[$order[$position], $order[$targetPosition]] = [$order[$targetPosition], $order[$position]];
$error = catalog_product_order_save($catalogId, $order);
if ($error !== null) {
    flash_set('error', $error);
} else {
    flash_set('success', 'Se actualizó el orden de los productos.');
}
header('Location: catalog_view.php?id=' . $catalogId);
exit;
