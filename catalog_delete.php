<?php
require_once __DIR__ . '/config/db.php';

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

$statement = $pdo->prepare('DELETE FROM catalogs WHERE id = ?');
$statement->execute([$id]);
header('Location: catalogs.php');
exit;
