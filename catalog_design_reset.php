<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/catalog_design_store.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalogs.php');
    exit;
}
if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    reject_request('La sesión venció o el token de seguridad no es válido. Recarga el diseñador e inténtalo de nuevo.', 403);
}

$catalogId = positive_id($_POST['id'] ?? null);
if ($catalogId === null) {
    reject_request('El identificador del catálogo no es válido.');
}
$statement = $pdo->prepare('SELECT id FROM catalogs WHERE id = ?');
$statement->execute([$catalogId]);
if (!$statement->fetchColumn()) {
    reject_request('No se encontró el catálogo solicitado.', 404);
}

$warning = catalog_design_delete($pdo, $catalogId);
if ($warning !== null) {
    flash_set('warning', 'El diseño no pudo restaurarse por completo: ' . $warning);
} else {
    flash_set('success', 'Se restauró el diseño predeterminado del catálogo.');
}
header('Location: catalog_designer.php?id=' . $catalogId);
exit;
