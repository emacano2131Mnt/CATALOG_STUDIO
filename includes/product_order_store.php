<?php

function catalog_product_order_directory(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'product_orders';
}

function catalog_product_order_path(int $catalogId): string
{
    return catalog_product_order_directory() . DIRECTORY_SEPARATOR . 'catalog-' . $catalogId . '.php';
}

function catalog_product_order_load(int $catalogId, array $defaultIds): array
{
    $validIds = [];
    foreach ($defaultIds as $id) {
        $positiveId = positive_id($id);
        if ($positiveId !== null) $validIds[$positiveId] = true;
    }

    $storedOrder = [];
    $path = catalog_product_order_path($catalogId);
    if (is_file($path)) {
        if (!defined('CATALOG_PRODUCT_ORDER_ACCESS')) define('CATALOG_PRODUCT_ORDER_ACCESS', true);
        try {
            $stored = include $path;
            if (is_array($stored)) {
                foreach ($stored as $id) {
                    $positiveId = positive_id($id);
                    if ($positiveId !== null && isset($validIds[$positiveId]) && !in_array($positiveId, $storedOrder, true)) {
                        $storedOrder[] = $positiveId;
                    }
                }
            }
        } catch (Throwable $e) {
            $storedOrder = [];
        }
    }

    foreach (array_keys($validIds) as $id) {
        if (!in_array($id, $storedOrder, true)) $storedOrder[] = $id;
    }

    return $storedOrder;
}

function catalog_product_order_apply(array $products, int $catalogId): array
{
    $byId = [];
    $defaultIds = [];
    foreach ($products as $product) {
        $id = positive_id($product['id'] ?? null);
        if ($id === null) continue;
        $byId[$id] = $product;
        $defaultIds[] = $id;
    }

    $ordered = [];
    foreach (catalog_product_order_load($catalogId, $defaultIds) as $id) {
        if (isset($byId[$id])) $ordered[] = $byId[$id];
    }

    return $ordered;
}

function catalog_product_order_save(int $catalogId, array $productIds): ?string
{
    $orderedIds = [];
    foreach ($productIds as $id) {
        $positiveId = positive_id($id);
        if ($positiveId === null || in_array($positiveId, $orderedIds, true)) {
            return 'No fue posible guardar el orden de productos porque la lista no es válida.';
        }
        $orderedIds[] = $positiveId;
    }

    if (count($orderedIds) > 10000) return 'El catálogo contiene demasiados productos para guardar su orden.';

    $directory = catalog_product_order_directory();
    if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) {
        return 'No fue posible preparar el almacenamiento del orden.';
    }
    if (!is_writable($directory)) return 'La carpeta de orden no permite guardar cambios.';

    $path = catalog_product_order_path($catalogId);
    $lockPath = $directory . DIRECTORY_SEPARATOR . 'catalog-' . $catalogId . '.lock';
    $lock = @fopen($lockPath, 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) {
        if (is_resource($lock)) fclose($lock);
        return 'No fue posible bloquear el orden para guardarlo.';
    }

    $temporary = $directory . DIRECTORY_SEPARATOR . 'catalog-' . $catalogId . '-' . bin2hex(random_bytes(8)) . '.php';
    $backup = $directory . DIRECTORY_SEPARATOR . 'catalog-' . $catalogId . '-' . bin2hex(random_bytes(8)) . '-backup.php';
    $source = "<?php\nif (!defined('CATALOG_PRODUCT_ORDER_ACCESS')) { http_response_code(404); exit; }\nreturn " . var_export($orderedIds, true) . ";\n";
    $saved = false;
    $error = null;

    try {
        $handle = @fopen($temporary, 'xb');
        if ($handle === false) throw new RuntimeException('No fue posible crear el archivo temporal de orden.');
        $offset = 0;
        $length = strlen($source);
        while ($offset < $length) {
            $written = fwrite($handle, substr($source, $offset));
            if ($written === false || $written === 0) throw new RuntimeException('No fue posible completar el archivo temporal de orden.');
            $offset += $written;
        }
        fflush($handle);
        fclose($handle);

        if (is_file($path) && !@rename($path, $backup)) throw new RuntimeException('No fue posible proteger el orden anterior.');
        if (!@rename($temporary, $path)) {
            if (is_file($backup)) @rename($backup, $path);
            throw new RuntimeException('No fue posible activar el nuevo orden.');
        }
        if (is_file($backup)) @unlink($backup);
        if (function_exists('opcache_invalidate')) @opcache_invalidate($path, true);
        $saved = true;
    } catch (Throwable $e) {
        $error = 'No fue posible guardar el orden. Verifica los permisos de almacenamiento.';
    } finally {
        if (is_resource($handle ?? null)) fclose($handle);
        if (is_file($temporary)) @unlink($temporary);
        if (!$saved && is_file($backup) && !is_file($path)) @rename($backup, $path);
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    return $error;
}

function catalog_product_order_delete(int $catalogId): ?string
{
    foreach ([catalog_product_order_path($catalogId), catalog_product_order_directory() . DIRECTORY_SEPARATOR . 'catalog-' . $catalogId . '.lock'] as $path) {
        if (is_file($path) && !@unlink($path)) return 'No se pudo eliminar el archivo de orden del catálogo.';
    }
    return null;
}
