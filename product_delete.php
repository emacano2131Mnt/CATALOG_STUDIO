<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/image_upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}
if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    reject_request('La sesión venció o el token de seguridad no es válido. Recarga el catálogo e inténtalo de nuevo.', 403);
}

$id = positive_id($_POST['id'] ?? null);
if ($id === null) {
    reject_request('El identificador del producto no es válido.');
}

try {
    $pdo->beginTransaction();
    $statement = $pdo->prepare('SELECT catalog_id, image FROM products WHERE id = ? FOR UPDATE');
    $statement->execute([$id]);
    $product = $statement->fetch();
    if (!$product) {
        $pdo->rollBack();
        reject_request('No se encontró el producto que intentas eliminar.', 404);
    }

    $statement = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $statement->execute([$id]);
    if ($statement->rowCount() !== 1) {
        $pdo->rollBack();
        flash_set('error', 'El producto ya no existe o ya fue eliminado.');
        header('Location: products.php');
        exit;
    }
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash_set('error', 'No fue posible eliminar el producto. No se realizaron cambios.');
    header('Location: products.php');
    exit;
}

$cleanupWarning = delete_image_if_unreferenced($pdo, $product['image']);
if ($cleanupWarning !== null) {
    flash_set('warning', 'El producto se eliminó, pero ' . lcfirst($cleanupWarning));
} else {
    flash_set('success', 'El producto se eliminó correctamente.');
}

header('Location: catalog_view.php?id=' . (int)$product['catalog_id']);
exit;
