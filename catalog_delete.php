<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/image_upload.php';
require_once __DIR__ . '/includes/catalog_design_store.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalogs.php');
    exit;
}
if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    reject_request('La sesión venció o el token de seguridad no es válido. Recarga la lista e inténtalo de nuevo.', 403);
}

$id = positive_id($_POST['id'] ?? null);
if ($id === null) {
    reject_request('El identificador del catálogo no es válido.');
}

try {
    $pdo->beginTransaction();
    $statement = $pdo->prepare('SELECT image FROM products WHERE catalog_id = ? FOR UPDATE');
    $statement->execute([$id]);
    $productImages = $statement->fetchAll(PDO::FETCH_COLUMN);

    $statement = $pdo->prepare('DELETE FROM catalogs WHERE id = ?');
    $statement->execute([$id]);
    if ($statement->rowCount() !== 1) {
        $pdo->rollBack();
        flash_set('error', 'El catálogo ya no existe o ya fue eliminado.');
        header('Location: catalogs.php');
        exit;
    }
    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash_set('error', 'No fue posible eliminar el catálogo. No se realizaron cambios.');
    header('Location: catalogs.php');
    exit;
}

$cleanupWarnings = [];
$designWarning = catalog_design_delete($pdo, $id);
if ($designWarning !== null) {
    $cleanupWarnings[] = $designWarning;
}
foreach ($productImages as $image) {
    $warning = delete_image_if_unreferenced($pdo, $image);
    if ($warning !== null) {
        $cleanupWarnings[] = $warning;
    }
}
if ($cleanupWarnings) {
    flash_set('warning', 'El catálogo y sus productos se eliminaron, pero algunas fotografías se conservaron: ' . implode(' ', array_unique($cleanupWarnings)));
} else {
    flash_set('success', 'El catálogo y sus productos asociados se eliminaron correctamente.');
}
header('Location: catalogs.php');
exit;
