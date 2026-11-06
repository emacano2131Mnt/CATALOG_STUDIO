<?php
require_once __DIR__ . '/config/db.php';

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

$statement = $pdo->prepare('SELECT catalog_id FROM products WHERE id = ?');
$statement->execute([$id]);
$catalogId = $statement->fetchColumn();
if ($catalogId === false) {
    reject_request('No se encontró el producto que intentas eliminar.', 404);
}

$statement = $pdo->prepare('DELETE FROM products WHERE id = ?');
$statement->execute([$id]);
header('Location: catalog_view.php?id=' . (int)$catalogId);
exit;
