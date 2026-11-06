<?php

function store_uploaded_image($file): array
{
    if (!is_array($file)) {
        return ['name' => null, 'error' => 'La fotografía seleccionada no tiene un formato de carga válido.'];
    }
    if (!isset($file['error']) || !is_int($file['error'])) {
        return ['name' => null, 'error' => 'No fue posible leer la fotografía seleccionada.'];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['name' => null, 'error' => null];
    }

    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'La fotografía supera el límite configurado por el servidor.',
        UPLOAD_ERR_FORM_SIZE => 'La fotografía supera el tamaño permitido.',
        UPLOAD_ERR_PARTIAL => 'La fotografía se recibió incompleta. Inténtalo de nuevo.',
        UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene una carpeta temporal disponible para la fotografía.',
        UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo guardar la fotografía.',
        UPLOAD_ERR_EXTENSION => 'El servidor rechazó la carga de la fotografía.',
    ];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['name' => null, 'error' => $uploadErrors[$file['error']] ?? 'Ocurrió un error al cargar la fotografía.'];
    }

    $tmpName = $file['tmp_name'] ?? '';
    if (!is_string($tmpName) || !is_uploaded_file($tmpName)) {
        return ['name' => null, 'error' => 'La fotografía recibida no es una carga válida.'];
    }

    $size = filesize($tmpName);
    if ($size === false || $size > 3 * 1024 * 1024) {
        return ['name' => null, 'error' => 'La fotografía no puede superar 3 MB.'];
    }

    if (!class_exists('finfo')) {
        return ['name' => null, 'error' => 'El servidor no puede verificar el tipo de la fotografía.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpName);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!is_string($mime) || !isset($extensions[$mime])) {
        return ['name' => null, 'error' => 'Formato no permitido. Usa una fotografía JPG, JPEG, PNG o WEBP.'];
    }

    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory) || !is_writable($directory)) {
        return ['name' => null, 'error' => 'La carpeta de fotografías no existe o no permite guardar archivos.'];
    }

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $destination = $directory . DIRECTORY_SEPARATOR . $name;
        $reservation = @fopen($destination, 'x');
        if ($reservation === false) {
            continue;
        }
        fclose($reservation);

        if (move_uploaded_file($tmpName, $destination)) {
            return ['name' => $name, 'error' => null];
        }

        @unlink($destination);
        return ['name' => null, 'error' => 'No fue posible guardar la fotografía. Verifica los permisos de la carpeta uploads.'];
    }

    return ['name' => null, 'error' => 'No fue posible generar un nombre único para la fotografía. Inténtalo de nuevo.'];
}

function is_safe_image_name($name): bool
{
    return is_string($name)
        && preg_match('/\A(?:[a-f0-9]{24}|[a-f0-9]{32})\.(?:jpg|jpeg|png|webp)\z/i', $name) === 1;
}

function stored_image_path($name): ?string
{
    if (!is_safe_image_name($name)) {
        return null;
    }

    $directory = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads');
    if ($directory === false) {
        return null;
    }

    $path = realpath($directory . DIRECTORY_SEPARATOR . $name);
    if ($path === false || !is_file($path)) {
        return null;
    }

    $prefix = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (stripos($path, $prefix) !== 0) {
        return null;
    }

    return $path;
}

function product_image_url($name): ?string
{
    if (stored_image_path($name) === null) {
        return null;
    }

    return 'uploads/' . rawurlencode($name);
}

function product_image_data_uri($name): ?string
{
    $path = stored_image_path($name);
    if ($path === null || !class_exists('finfo')) {
        return null;
    }

    $size = filesize($path);
    if ($size === false || $size > 3 * 1024 * 1024) {
        return null;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        return null;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        return null;
    }

    return 'data:' . $mime . ';base64,' . base64_encode($contents);
}

function delete_image_if_unreferenced(PDO $pdo, $name): ?string
{
    if ($name === null || $name === '') {
        return null;
    }
    if (!is_safe_image_name($name)) {
        return 'No se pudo verificar la ruta de la fotografía anterior; se conservó por seguridad.';
    }

    $path = stored_image_path($name);
    if ($path === null) {
        return null;
    }

    try {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM products WHERE image = ?');
        $statement->execute([$name]);
        if ((int)$statement->fetchColumn() > 0) {
            return null;
        }
    } catch (PDOException $e) {
        return 'No se pudieron comprobar las referencias de la fotografía; el archivo se conservó.';
    }

    if (!@unlink($path)) {
        return 'La operación en la base de datos se completó, pero no se pudo eliminar el archivo de fotografía sin referencias.';
    }

    return null;
}
