<?php
require_once __DIR__ . '/config/db.php';

$id = positive_id($_GET['id'] ?? null);
if ($id === null) {
    reject_request('El identificador del catálogo no es válido.', 404);
}

$statement = $pdo->prepare('SELECT * FROM catalogs WHERE id = ?');
$statement->execute([$id]);
$catalog = $statement->fetch();
if (!$catalog) {
    reject_request('No se encontró el catálogo solicitado.', 404);
}

$pageTitle = 'Editar catálogo';
$errors = [];
$formData = $catalog;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = array_merge($catalog, $_POST);
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció o el token de seguridad no es válido. Recarga la página e inténtalo de nuevo.';
    } else {
        $name = request_text($_POST, 'name', 'nombre del catálogo', 120, true, $errors);
        $category = request_text($_POST, 'category', 'categoría', 80, false, $errors);
        $description = request_text($_POST, 'description', 'descripción', 5000, false, $errors);
        $formData = array_merge($catalog, compact('name', 'category', 'description'));

        if (!$errors) {
            try {
                $statement = $pdo->prepare('UPDATE catalogs SET name = ?, category = ?, description = ? WHERE id = ?');
                $statement->execute([$name, $category, $description, $id]);
                header('Location: catalog_view.php?id=' . $id);
                exit;
            } catch (PDOException $e) {
                $errors[] = 'No fue posible actualizar el catálogo. Verifica los datos e inténtalo de nuevo.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">CONFIGURACIÓN</span><h1>Editar catálogo</h1><p>Actualiza la información de tu proyecto.</p></div></div>
<?php render_form_errors($errors); require __DIR__ . '/catalog_form.php'; require __DIR__ . '/includes/footer.php'; ?>
