<?php
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Crear catálogo';
$errors = [];
$formData = ['name' => '', 'category' => '', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = $_POST;
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció o el token de seguridad no es válido. Recarga la página e inténtalo de nuevo.';
    } else {
        $name = request_text($_POST, 'name', 'nombre del catálogo', 120, true, $errors);
        $category = request_text($_POST, 'category', 'categoría', 80, false, $errors);
        $description = request_text($_POST, 'description', 'descripción', 5000, false, $errors);
        $formData = compact('name', 'category', 'description');

        if (!$errors) {
            try {
                $statement = $pdo->prepare('INSERT INTO catalogs (name, category, description) VALUES (?, ?, ?)');
                $statement->execute([$name, $category, $description]);
                flash_set('success', 'El catálogo se creó correctamente.');
                header('Location: catalog_view.php?id=' . $pdo->lastInsertId());
                exit;
            } catch (PDOException $e) {
                $errors[] = 'No fue posible guardar el catálogo. Verifica los datos e inténtalo de nuevo.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">NUEVO PROYECTO</span><h1>Crear catálogo</h1><p>Configura la información inicial de tu nuevo catálogo.</p></div></div>
<?php render_form_errors($errors); require __DIR__ . '/catalog_form.php'; require __DIR__ . '/includes/footer.php'; ?>
