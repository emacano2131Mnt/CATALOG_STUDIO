<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/image_upload.php';

$pageTitle = 'Agregar producto';
$selectedCatalog = positive_id($_GET['catalog_id'] ?? null) ?? 0;
$catalogOptions = $pdo->query('SELECT id, name FROM catalogs ORDER BY name')->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];
$formData = ['catalog_id' => (string)$selectedCatalog, 'category_id' => '', 'name' => '', 'description' => '', 'price' => '0'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = $_POST;
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció o el token de seguridad no es válido. Recarga la página e inténtalo de nuevo.';
    } else {
        $catalogId = positive_id($_POST['catalog_id'] ?? null);
        if ($catalogId === null) {
            $errors[] = 'Selecciona un catálogo válido.';
        } else {
            $statement = $pdo->prepare('SELECT id FROM catalogs WHERE id = ?');
            $statement->execute([$catalogId]);
            if (!$statement->fetchColumn()) {
                $errors[] = 'El catálogo seleccionado ya no existe. Elige otro catálogo.';
            }
        }

        $categoryId = null;
        $rawCategoryId = $_POST['category_id'] ?? '';
        if (!is_string($rawCategoryId)) {
            $errors[] = 'La categoría seleccionada no es válida.';
        } elseif ($rawCategoryId !== '') {
            $categoryId = positive_id($rawCategoryId);
            if ($categoryId === null) {
                $errors[] = 'Selecciona una categoría válida.';
            } else {
                $statement = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
                $statement->execute([$categoryId]);
                if (!$statement->fetchColumn()) {
                    $errors[] = 'La categoría seleccionada no existe. Elige otra categoría.';
                }
            }
        }

        $name = request_text($_POST, 'name', 'nombre del producto', 140, true, $errors);
        $description = request_text($_POST, 'description', 'descripción', 5000, false, $errors);
        $price = valid_price($_POST['price'] ?? null, $errors);
        $formData = array_merge($_POST, compact('name', 'description'));

        if (!$errors) {
            $upload = store_uploaded_image($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
            if ($upload['error'] !== null) {
                $errors[] = $upload['error'];
            } else {
                try {
                    $statement = $pdo->prepare('INSERT INTO products (catalog_id, category_id, name, description, price, image) VALUES (?, ?, ?, ?, ?, ?)');
                    $statement->execute([$catalogId, $categoryId, $name, $description, $price, $upload['name']]);
                    flash_set('success', 'El producto se agregó correctamente al catálogo.');
                    header('Location: catalog_view.php?id=' . $catalogId);
                    exit;
                } catch (PDOException $e) {
                    if ($upload['name'] !== null) {
                        @unlink(__DIR__ . '/uploads/' . $upload['name']);
                    }
                    $errors[] = 'No fue posible guardar el producto. Verifica los datos e inténtalo de nuevo.';
                }
            }
        }
    }
}

if (!$catalogOptions) {
    header('Location: catalog_create.php');
    exit;
}

require __DIR__ . '/includes/header.php';
render_form_errors($errors);
require __DIR__ . '/product_form.php';
require __DIR__ . '/includes/footer.php';
