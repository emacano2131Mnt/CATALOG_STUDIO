<?php
require_once __DIR__ . '/image_upload.php';

function catalog_design_directory(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'catalog_designs';
}

function catalog_design_path(int $catalogId): string
{
    return catalog_design_directory() . DIRECTORY_SEPARATOR . 'catalog-' . $catalogId . '.php';
}

function catalog_design_palette(string $template): array
{
    $palettes = [
        'minimalista' => ['#6755B8', '#AAA0D8', '#FFFFFF', '#29263D'],
        'premium' => ['#C9A96E', '#554A3C', '#F3EFE7', '#28231E'],
        'comercial' => ['#D94E5D', '#F0B64B', '#FFF9F0', '#302832'],
        'corporativo' => ['#315B94', '#6B9D9A', '#F4F6F9', '#263449'],
    ];

    return $palettes[$template] ?? $palettes['minimalista'];
}

function catalog_design_palettes(): array
{
    return [
        'minimalista' => ['#6755B8', '#AAA0D8', '#FFFFFF', '#29263D', '#FFFFFF', '#29263D', '#29263D', '#29263D', '#6755B8', '#E7E4EF', '#6755B8', '#6755B8', '#FFFFFF'],
        'elegante' => ['#483B45', '#B99A78', '#FBF8F4', '#29242A', '#FFFFFF', '#29242A', '#51464E', '#51464E', '#9B704A', '#E6DED4', '#B99A78', '#483B45', '#FFFFFF'],
        'corporativa' => ['#315B94', '#6B9D9A', '#F4F6F9', '#263449', '#FFFFFF', '#263449', '#47566C', '#47566C', '#315B94', '#DCE2EA', '#315B94', '#315B94', '#FFFFFF'],
        'calida' => ['#B74E36', '#E5A14E', '#FFF8EE', '#3C3028', '#FFFFFF', '#3C3028', '#58463A', '#58463A', '#A9422F', '#EAD9C7', '#B74E36', '#B74E36', '#FFFFFF'],
        'premium_oscuro' => ['#D6B875', '#806E4C', '#211F20', '#F5F0E6', '#2E2B2A', '#F5F0E6', '#D6D0C4', '#D6D0C4', '#E2C37B', '#514B43', '#D6B875', '#211F20', '#F5F0E6'],
        'moderna' => ['#4C58C9', '#25A7A1', '#F5F7FC', '#252943', '#FFFFFF', '#30344E', '#545B73', '#545B73', '#3545B5', '#E0E4F0', '#4C58C9', '#4C58C9', '#FFFFFF'],
        'comercial' => ['#D94E5D', '#F0B64B', '#FFF9F0', '#302832', '#FFFFFF', '#302832', '#574B50', '#574B50', '#C43E50', '#F0D7CC', '#D94E5D', '#D94E5D', '#FFFFFF'],
    ];
}

function catalog_design_fonts(): array
{
    return ['DejaVu Sans', 'DejaVu Serif', 'DejaVu Sans Mono'];
}

function catalog_design_enum_options(): array
{
    return [
        'template' => ['minimalista', 'premium', 'comercial', 'corporativo'],
        'font' => catalog_design_fonts(),
        'typography_combo' => ['elegante', 'moderna', 'editorial', 'minimalista', 'comercial'],
        'title_font' => catalog_design_fonts(), 'subtitle_font' => catalog_design_fonts(),
        'description_font' => catalog_design_fonts(), 'price_font' => catalog_design_fonts(), 'category_font' => catalog_design_fonts(),
        'title_align' => ['left', 'center', 'right'], 'subtitle_align' => ['left', 'center', 'right'],
        'description_align' => ['left', 'center', 'right'], 'price_align' => ['left', 'center', 'right'],
        'title_transform' => ['none', 'uppercase', 'lowercase'], 'subtitle_transform' => ['none', 'uppercase', 'lowercase'],
        'title_weight' => ['400', '700'], 'subtitle_weight' => ['400', '700'], 'description_weight' => ['400', '700'],
        'price_weight' => ['400', '700'], 'category_weight' => ['400', '700'],
        'price_style' => ['highlight', 'subtle'], 'category_style' => ['plain', 'pill', 'uppercase'],
        'cover_style' => ['minimalista', 'fotografica', 'editorial', 'corporativa', 'premium', 'centered', 'banner'],
        'cover_image_position' => ['center', 'top', 'bottom', 'left', 'right'],
        'cover_image_fit' => ['cover', 'contain'], 'logo_position' => ['left', 'center', 'right'],
        'cover_title_position' => ['top', 'center', 'bottom'], 'cover_alignment' => ['left', 'center', 'right'],
        'columns' => ['1', '2', '3'], 'image_size' => ['small', 'medium', 'large'],
        'text_alignment' => ['left', 'center', 'right'], 'card_style' => ['minimal', 'bordered', 'filled'],
        'photo_position' => ['top', 'left', 'right', 'editorial'], 'card_corners' => ['square', 'rounded'],
        'image_ratio' => ['original', 'square', 'landscape', 'portrait'],
        'page_size' => ['A4', 'letter'], 'page_orientation' => ['portrait', 'landscape'],
    ];
}

function catalog_design_numeric_limits(): array
{
    return [
        'title_size' => [18, 48, 1], 'subtitle_size' => [9, 24, 1], 'description_size' => [7, 14, 1],
        'price_size' => [9, 24, 1], 'category_size' => [6, 12, 1],
        'title_line_height' => [1.0, 2.0, 0.1], 'subtitle_line_height' => [1.0, 2.0, 0.1],
        'description_line_height' => [1.0, 2.0, 0.1], 'title_letter_spacing' => [-1.0, 4.0, 0.1],
        'subtitle_letter_spacing' => [-1.0, 4.0, 0.1], 'page_margin' => [12, 35, 1],
        'section_spacing' => [4, 24, 1], 'product_gap' => [4, 20, 1], 'card_radius' => [0, 16, 1],
        'logo_size' => [60, 180, 5], 'cover_title_size' => [24, 54, 1],
    ];
}

function catalog_design_color_fields(): array
{
    return ['primary_color', 'secondary_color', 'background_color', 'text_color', 'card_background', 'title_color', 'subtitle_color', 'description_color', 'price_color', 'border_color', 'category_color', 'cover_color', 'cover_title_color'];
}

function catalog_design_boolean_fields(): array
{
    return ['show_cover_title', 'show_cover_subtitle', 'show_cover_date', 'show_cover_category', 'show_cover_product_count', 'show_decorations', 'show_prices', 'show_descriptions', 'show_categories', 'show_page_header', 'show_page_footer', 'show_page_numbers', 'show_dividers'];
}

function catalog_design_defaults(array $catalog): array
{
    $template = 'minimalista';
    [$primary, $secondary, $background, $text] = catalog_design_palette($template);

    return [
        'version' => 2,
        'template' => $template,
        'palette' => 'minimalista',
        'primary_color' => $primary,
        'secondary_color' => $secondary,
        'background_color' => $background,
        'text_color' => $text,
        'font' => 'DejaVu Sans',
        'logo' => null,
        'cover_image' => null,
        'cover_title' => (string)$catalog['name'],
        'cover_subtitle' => (string)($catalog['description'] ?? ''),
        'cover_style' => 'editorial',
        'cover_color' => $primary,
        'cover_title_color' => '#FFFFFF',
        'cover_image_position' => 'center',
        'cover_image_fit' => 'cover',
        'logo_position' => 'left',
        'logo_size' => 110,
        'cover_title_position' => 'center',
        'cover_title_size' => 34,
        'cover_alignment' => 'left',
        'show_cover_title' => true,
        'show_cover_subtitle' => true,
        'show_cover_date' => true,
        'show_cover_category' => true,
        'show_cover_product_count' => true,
        'show_decorations' => true,
        'columns' => 2,
        'show_prices' => true,
        'show_descriptions' => true,
        'show_categories' => true,
        'image_size' => 'medium',
        'text_alignment' => 'left',
        'card_style' => 'bordered',
        'card_background' => '#FFFFFF',
        'title_color' => $text,
        'subtitle_color' => $text,
        'description_color' => $text,
        'price_color' => $primary,
        'border_color' => '#E7E4EF',
        'category_color' => $primary,
        'typography_combo' => 'moderna',
        'title_font' => 'DejaVu Sans', 'subtitle_font' => 'DejaVu Sans',
        'description_font' => 'DejaVu Sans', 'price_font' => 'DejaVu Sans', 'category_font' => 'DejaVu Sans',
        'title_size' => 11, 'subtitle_size' => 11, 'description_size' => 8, 'price_size' => 12, 'category_size' => 7,
        'title_weight' => '700', 'subtitle_weight' => '400', 'description_weight' => '400', 'price_weight' => '700', 'category_weight' => '400',
        'title_align' => 'left', 'subtitle_align' => 'left', 'description_align' => 'left', 'price_align' => 'left',
        'title_line_height' => 1.3, 'subtitle_line_height' => 1.5, 'description_line_height' => 1.4,
        'title_letter_spacing' => 0.0, 'subtitle_letter_spacing' => 0.0,
        'title_transform' => 'none', 'subtitle_transform' => 'none',
        'price_style' => 'highlight', 'category_style' => 'uppercase',
        'photo_position' => 'top', 'card_corners' => 'rounded', 'card_radius' => 10, 'product_gap' => 8, 'image_ratio' => 'landscape',
        'page_size' => 'A4', 'page_orientation' => 'portrait', 'page_margin' => 18, 'section_spacing' => 10,
        'show_page_header' => false, 'show_page_footer' => true, 'show_page_numbers' => true, 'show_dividers' => false,
    ];
}

function catalog_design_normalize(array $design, array $catalog): array
{
    $normalized = catalog_design_defaults($catalog);
    $templates = ['minimalista', 'premium', 'comercial', 'corporativo'];
    $normalized['template'] = in_array($design['template'] ?? null, $templates, true) ? $design['template'] : $normalized['template'];
    [$primary, $secondary, $background, $text] = catalog_design_palette($normalized['template']);
    $normalized['primary_color'] = preg_match('/\A#[0-9a-f]{6}\z/i', (string)($design['primary_color'] ?? '')) ? strtoupper($design['primary_color']) : $primary;
    $normalized['secondary_color'] = preg_match('/\A#[0-9a-f]{6}\z/i', (string)($design['secondary_color'] ?? '')) ? strtoupper($design['secondary_color']) : $secondary;
    $normalized['background_color'] = preg_match('/\A#[0-9a-f]{6}\z/i', (string)($design['background_color'] ?? '')) ? strtoupper($design['background_color']) : $background;
    $normalized['text_color'] = preg_match('/\A#[0-9a-f]{6}\z/i', (string)($design['text_color'] ?? '')) ? strtoupper($design['text_color']) : $text;
    $normalized['palette'] = in_array($design['palette'] ?? null, array_keys(catalog_design_palettes()), true) ? $design['palette'] : 'minimalista';
    foreach (catalog_design_color_fields() as $field) {
        $value = $design[$field] ?? null;
        if (is_string($value) && preg_match('/\A#[0-9a-f]{6}\z/i', $value)) $normalized[$field] = strtoupper($value);
    }
    foreach (catalog_design_enum_options() as $field => $allowed) {
        if ($field === 'columns' && isset($design[$field]) && in_array((string)$design[$field], $allowed, true)) {
            $normalized[$field] = (int)$design[$field];
        } elseif (isset($design[$field]) && in_array($design[$field], $allowed, true)) {
            $normalized[$field] = $design[$field];
        }
    }
    foreach (catalog_design_numeric_limits() as $field => [$minimum, $maximum]) {
        $value = $design[$field] ?? null;
        if ((is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) && (float)$value >= $minimum && (float)$value <= $maximum) {
            $normalized[$field] = is_float($minimum) ? (float)$value : (int)$value;
        }
    }

    foreach (['cover_title' => 120, 'cover_subtitle' => 700] as $field => $limit) {
        $value = $design[$field] ?? $normalized[$field];
        if (is_string($value) && preg_match('//u', $value) && mb_strlen($value, 'UTF-8') <= $limit) {
            $normalized[$field] = $value;
        }
    }
    foreach (catalog_design_boolean_fields() as $field) {
        if (array_key_exists($field, $design)) {
            $normalized[$field] = $design[$field] === true;
        }
    }
    foreach (['logo', 'cover_image'] as $field) {
        $name = $design[$field] ?? null;
        $normalized[$field] = is_safe_image_name($name) && stored_image_path($name) !== null ? $name : null;
    }

    return $normalized;
}

function catalog_design_load(int $catalogId, array $catalog): array
{
    $path = catalog_design_path($catalogId);
    if (!is_file($path)) {
        return catalog_design_defaults($catalog);
    }

    if (!defined('CATALOG_DESIGN_STORE_ACCESS')) {
        define('CATALOG_DESIGN_STORE_ACCESS', true);
    }
    try {
        $stored = include $path;
    } catch (Throwable $e) {
        return catalog_design_defaults($catalog);
    }

    return is_array($stored) ? catalog_design_normalize($stored, $catalog) : catalog_design_defaults($catalog);
}

function catalog_design_validate_post(array $post, array $current, array $catalog, array &$errors): array
{
    $candidate = $current;
    foreach (catalog_design_enum_options() as $field => $allowed) {
        $value = $post[$field] ?? null;
        if (!is_string($value) || !in_array($value, $allowed, true)) {
            $errors[] = 'Hay una opcion de diseno no valida (' . $field . ').';
        } else {
            $candidate[$field] = $field === 'columns' ? (int)$value : $value;
        }
    }

    foreach (catalog_design_color_fields() as $field) {
        $value = $post[$field] ?? null;
        if (!is_string($value) || !preg_match('/\A#[0-9a-f]{6}\z/i', $value)) {
            $errors[] = 'El color debe ser un hexadecimal valido (' . $field . ').';
        } else {
            $candidate[$field] = strtoupper($value);
        }
    }

    foreach (catalog_design_numeric_limits() as $field => [$minimum, $maximum, $step]) {
        $value = $post[$field] ?? null;
        $pattern = is_float($step) ? '/\A-?\d{1,2}(?:\.\d)?\z/' : '/\A\d{1,3}\z/';
        $stepValue = is_string($value) && is_numeric($value) ? ((float)$value - $minimum) / $step : -1;
        if (!is_string($value) || !preg_match($pattern, $value) || (float)$value < $minimum || (float)$value > $maximum || $stepValue < 0 || abs($stepValue - round($stepValue)) > 0.0001) {
            $errors[] = 'El ajuste numerico esta fuera del rango permitido (' . $field . ').';
        } else {
            $candidate[$field] = is_float($step) ? (float)$value : (int)$value;
        }
    }
    $candidate['cover_title'] = request_text($post, 'cover_title', 'titulo de portada', 120, false, $errors);
    $candidate['cover_subtitle'] = request_text($post, 'cover_subtitle', 'subtitulo de portada', 700, false, $errors);
    foreach (catalog_design_boolean_fields() as $field) {
        $value = $post[$field] ?? '0';
        if (!is_string($value) || !in_array($value, ['0', '1'], true)) {
            $errors[] = 'La opcion de visualizacion no es valida (' . $field . ').';
        }
        $candidate[$field] = $value === '1';
    }

    $candidate['version'] = 2;
    $candidate['logo'] = $current['logo'];
    $candidate['cover_image'] = $current['cover_image'];

    return $candidate;
}
function catalog_design_save(int $catalogId, array $design): ?string
{
    $directory = catalog_design_directory();
    if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) {
        return 'No fue posible preparar el almacenamiento de diseños.';
    }
    if (!is_writable($directory)) {
        return 'La carpeta de diseños no permite guardar cambios.';
    }

    $path = catalog_design_path($catalogId);
    $lockPath = $directory . DIRECTORY_SEPARATOR . 'catalog-' . $catalogId . '.lock';
    $lock = @fopen($lockPath, 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) {
        if (is_resource($lock)) fclose($lock);
        return 'No fue posible bloquear el diseño para guardarlo.';
    }

    $temporary = $directory . DIRECTORY_SEPARATOR . 'catalog-' . $catalogId . '-' . bin2hex(random_bytes(8)) . '.php';
    $backup = $directory . DIRECTORY_SEPARATOR . 'catalog-' . $catalogId . '-' . bin2hex(random_bytes(8)) . '-backup.php';
    $source = "<?php\nif (!defined('CATALOG_DESIGN_STORE_ACCESS')) { http_response_code(404); exit; }\nreturn " . var_export($design, true) . ";\n";
    $saved = false;
    $error = null;

    try {
        $handle = @fopen($temporary, 'xb');
        if ($handle === false) {
            throw new RuntimeException('No fue posible crear un archivo temporal de diseño.');
        }
        $written = fwrite($handle, $source);
        fflush($handle);
        fclose($handle);
        if ($written !== strlen($source)) {
            throw new RuntimeException('No fue posible escribir todos los ajustes del diseño.');
        }

        if (is_file($path) && !@rename($path, $backup)) {
            throw new RuntimeException('No fue posible proteger el diseño anterior durante el guardado.');
        }
        if (!@rename($temporary, $path)) {
            if (is_file($backup)) @rename($backup, $path);
            throw new RuntimeException('No fue posible activar el nuevo diseño.');
        }
        if (is_file($backup)) @unlink($backup);
        if (function_exists('opcache_invalidate')) @opcache_invalidate($path, true);
        $saved = true;
    } catch (Throwable $e) {
        $error = 'No fue posible guardar el diseño. Verifica los permisos de almacenamiento.';
    } finally {
        if (is_file($temporary)) @unlink($temporary);
        if (!$saved && is_file($backup) && !is_file($path)) @rename($backup, $path);
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    return $error;
}

function catalog_design_asset_is_referenced(PDO $pdo, string $name): bool
{
    if (!is_safe_image_name($name)) {
        return true;
    }

    try {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM products WHERE image = ?');
        $statement->execute([$name]);
        if ((int)$statement->fetchColumn() > 0) return true;
    } catch (Throwable $e) {
        return true;
    }

    $directory = catalog_design_directory();
    foreach (glob($directory . DIRECTORY_SEPARATOR . 'catalog-*.php') ?: [] as $path) {
        if (!preg_match('/\Acatalog-[1-9][0-9]*\.php\z/', basename($path))) continue;
        if (!defined('CATALOG_DESIGN_STORE_ACCESS')) define('CATALOG_DESIGN_STORE_ACCESS', true);
        try {
            $settings = include $path;
        } catch (Throwable $e) {
            return true;
        }
        if (is_array($settings) && in_array($name, [$settings['logo'] ?? null, $settings['cover_image'] ?? null], true)) {
            return true;
        }
    }

    return false;
}

function catalog_design_cleanup_asset(PDO $pdo, ?string $name): ?string
{
    if ($name === null || $name === '') return null;
    $path = stored_image_path($name);
    if ($path === null || catalog_design_asset_is_referenced($pdo, $name)) return null;
    if (!@unlink($path)) return 'No se pudo eliminar una imagen de diseño que quedó sin referencias.';
    return null;
}

function catalog_design_delete(PDO $pdo, int $catalogId): ?string
{
    $path = catalog_design_path($catalogId);
    $settings = is_file($path) ? catalog_design_load($catalogId, ['name' => '', 'description' => '']) : null;
    if (is_file($path) && !@unlink($path)) {
        return 'El diseño se conservó porque no se pudo retirar su archivo de ajustes.';
    }

    $warnings = [];
    if (is_array($settings)) {
        foreach ([$settings['logo'] ?? null, $settings['cover_image'] ?? null] as $asset) {
            $warning = catalog_design_cleanup_asset($pdo, $asset);
            if ($warning !== null) $warnings[] = $warning;
        }
    }
    return $warnings ? implode(' ', array_unique($warnings)) : null;
}
