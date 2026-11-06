<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/image_upload.php';

$id = positive_id($_GET['id'] ?? null);
if ($id === null) {
    reject_request('El identificador del producto no es válido.', 404);
}

$statement = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$statement->execute([$id]);
$product = $statement->fetch();
if (!$product) {
    reject_request('No se encontró el producto solicitado.', 404);
}

$pageTitle = 'Editar producto';
$selectedCatalog = (int)$product['catalog_id'];
$catalogOptions = $pdo->query('SELECT id, name FROM catalogs ORDER BY name')->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];
$formData = $product;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = array_merge($product, $_POST);
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
        $formData = array_merge($product, compact('name', 'description'));
        if (is_string($_POST['catalog_id'] ?? null)) {
            $formData['catalog_id'] = $_POST['catalog_id'];
        }
        if (is_string($_POST['category_id'] ?? null)) {
            $formData['category_id'] = $_POST['category_id'];
        }
        if (is_string($_POST['price'] ?? null)) {
            $formData['price'] = $_POST['price'];
        }

        if (!$errors) {
            $upload = store_uploaded_image($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
            if ($upload['error'] !== null) {
                $errors[] = $upload['error'];
            } else {
                try {
                    $pdo->beginTransaction();
                    $currentStatement = $pdo->prepare('SELECT image FROM products WHERE id = ? FOR UPDATE');
                    $currentStatement->execute([$id]);
                    $currentProduct = $currentStatement->fetch();
                    if (!$currentProduct) {
                        throw new RuntimeException('El producto dejó de existir durante la edición.');
                    }

                    $oldImage = $currentProduct['image'];
                    $image = $upload['name'] ?? $oldImage;
                    $statement = $pdo->prepare('UPDATE products SET catalog_id = ?, category_id = ?, name = ?, description = ?, price = ?, image = ? WHERE id = ?');
                    $statement->execute([$catalogId, $categoryId, $name, $description, $price, $image, $id]);
                    $pdo->commit();
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    if ($upload['name'] !== null) {
                        @unlink(__DIR__ . '/uploads/' . $upload['name']);
                    }
                    $errors[] = 'No fue posible actualizar el producto. Verifica los datos e inténtalo de nuevo.';
                }

                if (!$errors) {
                    $cleanupWarning = null;
                    if ($upload['name'] !== null && $oldImage !== $upload['name']) {
                        $cleanupWarning = delete_image_if_unreferenced($pdo, $oldImage);
                    }
                    if ($cleanupWarning !== null) {
                        flash_set('warning', 'El producto se actualizó, pero ' . lcfirst($cleanupWarning));
                    } else {
                        flash_set('success', 'Los cambios del producto se guardaron correctamente.');
                    }
                    header('Location: catalog_view.php?id=' . $catalogId);
                    exit;
                }
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
render_form_errors($errors);
require __DIR__ . '/product_form.php';
require __DIR__ . '/includes/footer.php';
