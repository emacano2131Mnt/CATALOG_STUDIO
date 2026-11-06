<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/catalog_design_store.php';

$catalogId = positive_id($_GET['id'] ?? $_POST['id'] ?? null);
if ($catalogId === null) {
    reject_request('El identificador del catálogo no es válido.', 404);
}
$statement = $pdo->prepare('SELECT id, name, category, description FROM catalogs WHERE id = ?');
$statement->execute([$catalogId]);
$catalog = $statement->fetch();
if (!$catalog) {
    reject_request('No se encontró el catálogo solicitado.', 404);
}

$design = catalog_design_load($catalogId, $catalog);
$previousAssets = [$design['logo'], $design['cover_image']];
$errors = [];
$warnings = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        reject_request('La sesión del diseñador venció o el token de seguridad no es válido. Recarga la página e inténtalo de nuevo.', 403);
    }

    $design = catalog_design_validate_post($_POST, $design, $catalog, $errors);
    foreach (['remove_logo', 'remove_cover_image'] as $field) {
        $value = $_POST[$field] ?? '0';
        if (!is_string($value) || !in_array($value, ['0', '1'], true)) {
            $errors[] = 'La opción para retirar una imagen no es válida.';
        }
    }

    if (!$errors) {
        $newAssets = [];
        foreach (['logo_file' => 'logo', 'cover_file' => 'cover_image'] as $uploadField => $designField) {
            $removeField = $designField === 'logo' ? 'remove_logo' : 'remove_cover_image';
            if (($_POST[$removeField] ?? '0') === '1') {
                $design[$designField] = null;
            }

            $upload = store_uploaded_image($_FILES[$uploadField] ?? ['error' => UPLOAD_ERR_NO_FILE]);
            if ($upload['error'] !== null) {
                $errors[] = $upload['error'];
                break;
            }
            if ($upload['name'] !== null) {
                $newAssets[] = $upload['name'];
                $design[$designField] = $upload['name'];
            }
        }

        if (!$errors) {
            $savedDesign = catalog_design_validate_post($_POST, catalog_design_load($catalogId, $catalog), $catalog, $errors);
            $savedDesign['logo'] = $design['logo'];
            $savedDesign['cover_image'] = $design['cover_image'];

            if (!$errors) {
                $saveError = catalog_design_save($catalogId, $savedDesign);
                if ($saveError !== null) {
                    $errors[] = $saveError;
                } else {
                    $design = $savedDesign;
                    foreach (array_unique($previousAssets) as $oldAsset) {
                        if ($oldAsset !== null && !in_array($oldAsset, [$design['logo'], $design['cover_image']], true)) {
                            $warning = catalog_design_cleanup_asset($pdo, $oldAsset);
                            if ($warning !== null) $warnings[] = $warning;
                        }
                    }

                    if (($_POST['action'] ?? '') === 'save_export') {
                        header('Location: export_pdf.php?id=' . $catalogId . '&download=1');
                        exit;
                    }
                    flash_set($warnings ? 'warning' : 'success', $warnings ? implode(' ', $warnings) : 'El diseño del catálogo se guardó correctamente.');
                    header('Location: catalog_designer.php?id=' . $catalogId);
                    exit;
                }
            }
        }

        if ($errors) {
            foreach (array_unique($newAssets ?? []) as $newAsset) {
                catalog_design_cleanup_asset($pdo, $newAsset);
            }
        }
    }
}

$statement = $pdo->prepare('SELECT COUNT(*) FROM products WHERE catalog_id = ?');
$statement->execute([$catalogId]);
$productCount = (int)$statement->fetchColumn();
$statement = $pdo->prepare('SELECT p.name, p.description, p.price, p.image, cat.name AS category_name FROM products p LEFT JOIN categories cat ON cat.id = p.category_id WHERE p.catalog_id = ? ORDER BY p.name LIMIT 4');
$statement->execute([$catalogId]);
$previewProducts = $statement->fetchAll();
$designSelect = static function (string $field, string $label, array $options) use (&$design): string {
    $html = '<label class="designer-field">' . h($label) . '<select name="' . h($field) . '" data-design-control>';
    foreach ($options as $value => $optionLabel) {
        $html .= '<option value="' . h((string)$value) . '"' . ((string)$design[$field] === (string)$value ? ' selected' : '') . '>' . h($optionLabel) . '</option>';
    }
    return $html . '</select></label>';
};
$designRange = static function (string $field, string $label, string $min, string $max, string $step, string $unit = '') use (&$design): string {
    return '<label class="designer-field designer-range">' . h($label) . ' <output data-range-output="' . h($field) . '">' . h((string)$design[$field]) . h($unit) . '</output><input type="range" name="' . h($field) . '" min="' . h($min) . '" max="' . h($max) . '" step="' . h($step) . '" value="' . h((string)$design[$field]) . '" data-design-control></label>';
};
$pageTitle = 'Diseñador de catálogo';

require __DIR__ . '/includes/header.php';
?>
<section class="page-heading designer-heading">
  <div class="page-heading-copy"><span class="eyebrow">PERSONALIZACIÓN</span><h1>Diseña tu catálogo</h1><p>Define una identidad visual para <strong><?= h($catalog['name']) ?></strong>. Los cambios se reflejan en la vista previa y el PDF.</p></div>
  <div class="heading-actions">
    <a class="btn btn-light" href="catalog_view.php?id=<?= $catalogId ?>">Volver al catálogo</a>
    <a class="btn btn-primary" href="export_pdf.php?id=<?= $catalogId ?>" target="_blank" rel="noopener">Ver PDF guardado</a>
  </div>
</section>

<?php render_form_errors($errors); ?>
<?php if ($warnings): ?><div class="flash-message flash-warning" role="status"><?= h(implode(' ', $warnings)) ?></div><?php endif; ?>

<div class="designer-layout">
  <form class="designer-controls panel" id="catalogDesignForm" method="post" enctype="multipart/form-data" action="catalog_designer.php?id=<?= $catalogId ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $catalogId ?>">

    <details class="designer-section" open>
      <summary class="designer-section-heading"><span class="designer-section-number">01</span><div><h2>Plantilla</h2><p>Elige una base y personalízala.</p></div></summary>
      <label class="designer-field">Diseño inicial
        <select name="template" id="designTemplate" data-design-control>
          <option value="minimalista" <?= $design['template'] === 'minimalista' ? 'selected' : '' ?>>Minimalista · limpio y sencillo</option>
          <option value="premium" <?= $design['template'] === 'premium' ? 'selected' : '' ?>>Premium · oscuro y sofisticado</option>
          <option value="comercial" <?= $design['template'] === 'comercial' ? 'selected' : '' ?>>Comercial · cálido y llamativo</option>
          <option value="corporativo" <?= $design['template'] === 'corporativo' ? 'selected' : '' ?>>Corporativo · sobrio y profesional</option>
        </select>
      </label>
      <label class="designer-field">Tipografía
        <select name="font" data-design-control>
          <option value="DejaVu Sans" <?= $design['font'] === 'DejaVu Sans' ? 'selected' : '' ?>>DejaVu Sans</option>
          <option value="DejaVu Serif" <?= $design['font'] === 'DejaVu Serif' ? 'selected' : '' ?>>DejaVu Serif</option>
          <option value="DejaVu Sans Mono" <?= $design['font'] === 'DejaVu Sans Mono' ? 'selected' : '' ?>>DejaVu Sans Mono</option>
        </select>
        <small>Fuentes disponibles en Dompdf; conservan tildes y caracteres españoles.</small>
      </label>
    </details>

    <details class="designer-advanced">
      <summary><span class="designer-section-number">03</span><span><strong>Tipografía avanzada</strong><small>Familias, tamaños, jerarquía y alineación.</small></span></summary>
      <div class="designer-field-grid">
        <?= $designSelect('typography_combo', 'Combinación tipográfica', ['elegante' => 'Elegante', 'moderna' => 'Moderna', 'editorial' => 'Editorial', 'minimalista' => 'Minimalista', 'comercial' => 'Comercial']) ?>
        <?php foreach (['title_font' => 'Familia de títulos', 'subtitle_font' => 'Familia de subtítulos', 'description_font' => 'Familia de descripciones', 'price_font' => 'Familia de precios', 'category_font' => 'Familia de categorías'] as $field => $label): ?>
          <?= $designSelect($field, $label, ['DejaVu Sans' => 'DejaVu Sans', 'DejaVu Serif' => 'DejaVu Serif', 'DejaVu Sans Mono' => 'DejaVu Sans Mono']) ?>
        <?php endforeach; ?>
        <?= $designRange('title_size', 'Tamaño de títulos', '18', '48', '1', ' pt') ?>
        <?= $designRange('subtitle_size', 'Tamaño de subtítulos', '9', '24', '1', ' pt') ?>
        <?= $designRange('description_size', 'Tamaño de descripciones', '7', '14', '1', ' pt') ?>
        <?= $designRange('price_size', 'Tamaño de precios', '9', '24', '1', ' pt') ?>
        <?= $designRange('category_size', 'Tamaño de categorías', '6', '12', '1', ' pt') ?>
        <?php foreach (['title_weight' => 'Grosor de títulos', 'subtitle_weight' => 'Grosor de subtítulos', 'description_weight' => 'Grosor de descripciones', 'price_weight' => 'Grosor de precios', 'category_weight' => 'Grosor de categorías'] as $field => $label): ?>
          <?= $designSelect($field, $label, ['400' => 'Regular', '700' => 'Negrita']) ?>
        <?php endforeach; ?>
        <?= $designSelect('title_align', 'Alineación de títulos', ['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha']) ?>
        <?= $designSelect('subtitle_align', 'Alineación de subtítulos', ['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha']) ?>
        <?= $designSelect('description_align', 'Alineación de descripciones', ['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha']) ?>
        <?= $designSelect('price_align', 'Alineación de precios', ['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha']) ?>
        <?= $designSelect('title_transform', 'Transformación de títulos', ['none' => 'Normal', 'uppercase' => 'Mayúsculas', 'lowercase' => 'Minúsculas']) ?>
        <?= $designSelect('subtitle_transform', 'Transformación de subtítulos', ['none' => 'Normal', 'uppercase' => 'Mayúsculas', 'lowercase' => 'Minúsculas']) ?>
        <?= $designSelect('price_style', 'Énfasis del precio', ['highlight' => 'Destacado', 'subtle' => 'Discreto']) ?>
        <?= $designSelect('category_style', 'Presentación de categorías', ['plain' => 'Sencilla', 'pill' => 'Etiqueta', 'uppercase' => 'Mayúsculas']) ?>
        <?= $designRange('title_line_height', 'Interlineado de títulos', '1.0', '2.0', '0.1') ?>
        <?= $designRange('subtitle_line_height', 'Interlineado de subtítulos', '1.0', '2.0', '0.1') ?>
        <?= $designRange('description_line_height', 'Interlineado de descripciones', '1.0', '2.0', '0.1') ?>
        <?= $designRange('title_letter_spacing', 'Espaciado de títulos', '-1.0', '4.0', '0.1', ' px') ?>
        <?= $designRange('subtitle_letter_spacing', 'Espaciado de subtítulos', '-1.0', '4.0', '0.1', ' px') ?>
      </div>
      <p class="designer-note">Se usan fuentes locales DejaVu Sans, Serif y Sans Mono incluidas con Dompdf; no requieren conexión externa.</p>
    </details>

    <details class="designer-section">
      <summary class="designer-section-heading"><span class="designer-section-number">02</span><div><h2>Colores</h2><p>Ajusta los tonos de tu marca.</p></div></summary>
      <label class="designer-field">Paleta
        <select name="palette" id="designPalette" data-design-control data-palette-control>
          <?php foreach (['elegante' => 'Elegante', 'minimalista' => 'Minimalista', 'corporativa' => 'Corporativa', 'calida' => 'Cálida', 'premium_oscuro' => 'Premium oscuro', 'moderna' => 'Moderna', 'comercial' => 'Comercial'] as $value => $label): ?>
            <option value="<?= h($value) ?>" <?= $design['palette'] === $value ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <div class="designer-color-grid">
        <?php foreach (['primary_color' => 'Principal', 'secondary_color' => 'Secundario', 'background_color' => 'Fondo de página', 'card_background' => 'Fondo de tarjetas', 'title_color' => 'Títulos', 'subtitle_color' => 'Subtítulos', 'description_color' => 'Descripciones', 'price_color' => 'Precios', 'border_color' => 'Bordes', 'category_color' => 'Categorías', 'cover_color' => 'Portada', 'cover_title_color' => 'Título de portada', 'text_color' => 'Texto base'] as $field => $label): ?>
          <label class="color-control"><span><?= h($label) ?></span><input type="color" name="<?= h($field) ?>" value="<?= h($design[$field]) ?>" data-design-control aria-label="Color <?= h(mb_strtolower($label, 'UTF-8')) ?>"></label>
        <?php endforeach; ?>
      </div>
    </details>

    <details class="designer-section">
      <summary class="designer-section-heading"><span class="designer-section-number">03</span><div><h2>Portada</h2><p>Personaliza el primer vistazo.</p></div></summary>
      <div class="designer-field-grid">
        <?= $designSelect('cover_style', 'Composición de portada', ['minimalista' => 'Minimalista', 'fotografica' => 'Fotográfica', 'editorial' => 'Editorial', 'corporativa' => 'Corporativa', 'premium' => 'Premium', 'centered' => 'Centrada (compatibilidad)', 'banner' => 'Banda de color (compatibilidad)']) ?>
        <?= $designSelect('cover_alignment', 'Alineación del contenido', ['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha']) ?>
        <?= $designSelect('cover_title_position', 'Posición del título', ['top' => 'Arriba', 'center' => 'Centro', 'bottom' => 'Abajo']) ?>
        <?= $designSelect('cover_image_position', 'Posición de la fotografía', ['center' => 'Centro', 'top' => 'Arriba', 'bottom' => 'Abajo', 'left' => 'Izquierda', 'right' => 'Derecha']) ?>
        <?= $designSelect('cover_image_fit', 'Ajuste de la fotografía', ['cover' => 'Cubrir el espacio', 'contain' => 'Mostrar completa']) ?>
        <?= $designSelect('logo_position', 'Posición del logotipo', ['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha']) ?>
      </div>
      <?= $designRange('logo_size', 'Tamaño máximo del logotipo', '60', '180', '5', ' px') ?>
      <?= $designRange('cover_title_size', 'Tamaño del título de portada', '24', '54', '1', ' pt') ?>
      <label class="designer-field">Título
        <input type="text" name="cover_title" maxlength="120" value="<?= h($design['cover_title']) ?>" data-design-control>
      </label>
      <label class="designer-field">Subtítulo o descripción
        <textarea name="cover_subtitle" rows="3" maxlength="700" data-design-control><?= h($design['cover_subtitle']) ?></textarea>
      </label>
      <div class="designer-toggle-list">
        <?php foreach (['show_cover_title' => 'Mostrar título', 'show_cover_subtitle' => 'Mostrar subtítulo', 'show_cover_date' => 'Mostrar fecha', 'show_cover_category' => 'Mostrar categoría del catálogo', 'show_cover_product_count' => 'Mostrar cantidad de productos', 'show_decorations' => 'Mostrar detalles decorativos'] as $field => $label): ?>
          <label class="designer-toggle"><span><?= h($label) ?></span><input type="hidden" name="<?= h($field) ?>" value="0"><input type="checkbox" name="<?= h($field) ?>" value="1" <?= $design[$field] ? 'checked' : '' ?> data-design-control></label>
        <?php endforeach; ?>
      </div>
      <div class="designer-upload-grid">
        <div class="designer-upload">
          <label class="designer-field" for="designerLogoInput">Logotipo <input id="designerLogoInput" type="file" name="logo_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><small>JPG, PNG o WEBP · máximo 3 MB</small></label>
          <div class="designer-upload-preview" id="designerLogoPreviewWrap" <?= $design['logo'] === null ? 'hidden' : '' ?>><img id="designerLogoPreview" src="<?= $design['logo'] === null ? '' : h(product_image_url($design['logo']) ?? '') ?>" alt="Vista previa del logotipo" data-current-src="<?= $design['logo'] === null ? '' : h(product_image_url($design['logo']) ?? '') ?>"><label class="designer-remove"><input type="hidden" name="remove_logo" value="0"><input type="checkbox" name="remove_logo" value="1" data-remove-image="logo"> Quitar logotipo</label></div>
        </div>
        <div class="designer-upload">
          <label class="designer-field" for="designerCoverInput">Imagen de portada <input id="designerCoverInput" type="file" name="cover_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><small>JPG, PNG o WEBP · máximo 3 MB</small></label>
          <div class="designer-upload-preview" id="designerCoverPreviewWrap" <?= $design['cover_image'] === null ? 'hidden' : '' ?>><img id="designerCoverPreview" src="<?= $design['cover_image'] === null ? '' : h(product_image_url($design['cover_image']) ?? '') ?>" alt="Vista previa de la portada" data-current-src="<?= $design['cover_image'] === null ? '' : h(product_image_url($design['cover_image']) ?? '') ?>"><label class="designer-remove"><input type="hidden" name="remove_cover_image" value="0"><input type="checkbox" name="remove_cover_image" value="1" data-remove-image="cover"> Quitar imagen</label></div>
        </div>
      </div>
      <small class="field-help" id="designerUploadStatus" role="status"></small>
    </details>

    <details class="designer-section">
      <summary class="designer-section-heading"><span class="designer-section-number">04</span><div><h2>Productos</h2><p>Elige cómo se presenta cada artículo.</p></div></summary>
      <div class="designer-field-grid">
        <label class="designer-field">Columnas
          <select name="columns" data-design-control>
            <option value="1" <?= $design['columns'] === 1 ? 'selected' : '' ?>>Una columna</option>
            <option value="2" <?= $design['columns'] === 2 ? 'selected' : '' ?>>Dos columnas</option>
            <option value="3" <?= $design['columns'] === 3 ? 'selected' : '' ?>>Tres columnas</option>
          </select>
        </label>
        <label class="designer-field">Tamaño de fotografías
          <select name="image_size" data-design-control>
            <option value="small" <?= $design['image_size'] === 'small' ? 'selected' : '' ?>>Pequeño</option>
            <option value="medium" <?= $design['image_size'] === 'medium' ? 'selected' : '' ?>>Mediano</option>
            <option value="large" <?= $design['image_size'] === 'large' ? 'selected' : '' ?>>Grande</option>
          </select>
        </label>
        <label class="designer-field">Alineación de textos
          <select name="text_alignment" data-design-control>
            <option value="left" <?= $design['text_alignment'] === 'left' ? 'selected' : '' ?>>Izquierda</option>
            <option value="center" <?= $design['text_alignment'] === 'center' ? 'selected' : '' ?>>Centro</option>
            <option value="right" <?= $design['text_alignment'] === 'right' ? 'selected' : '' ?>>Derecha</option>
          </select>
        </label>
        <label class="designer-field">Estilo de tarjetas
          <select name="card_style" data-design-control>
            <option value="minimal" <?= $design['card_style'] === 'minimal' ? 'selected' : '' ?>>Minimalista</option>
            <option value="bordered" <?= $design['card_style'] === 'bordered' ? 'selected' : '' ?>>Con borde</option>
            <option value="filled" <?= $design['card_style'] === 'filled' ? 'selected' : '' ?>>Fondo suave</option>
          </select>
        </label>
        <?= $designSelect('photo_position', 'Posición de la fotografía', ['top' => 'Arriba', 'left' => 'A la izquierda', 'right' => 'A la derecha', 'editorial' => 'Grande tipo editorial']) ?>
        <?= $designSelect('card_corners', 'Esquinas de tarjetas', ['square' => 'Cuadradas', 'rounded' => 'Redondeadas']) ?>
        <?= $designSelect('image_ratio', 'Proporción de fotografías', ['original' => 'Original', 'square' => 'Cuadrada', 'landscape' => 'Horizontal', 'portrait' => 'Vertical']) ?>
      </div>
      <?= $designRange('product_gap', 'Separación entre productos', '4', '20', '1', ' px') ?>
      <?= $designRange('card_radius', 'Radio de esquinas', '0', '16', '1', ' px') ?>
      <div class="designer-toggle-list">
        <?php foreach (['show_prices' => 'Mostrar precios', 'show_descriptions' => 'Mostrar descripciones', 'show_categories' => 'Mostrar categorías'] as $field => $label): ?>
          <label class="designer-toggle"><span><?= h($label) ?></span><input type="hidden" name="<?= h($field) ?>" value="0"><input type="checkbox" name="<?= h($field) ?>" value="1" <?= $design[$field] ? 'checked' : '' ?> data-design-control></label>
        <?php endforeach; ?>
      </div>
    </details>

    <details class="designer-advanced">
      <summary><span class="designer-section-number">07</span><span><strong>Página y márgenes</strong><small>Formato de papel, orientación, espacios y paginación.</small></span></summary>
      <div class="designer-field-grid">
        <?= $designSelect('page_size', 'Tamaño de página', ['A4' => 'A4', 'letter' => 'Carta']) ?>
        <?= $designSelect('page_orientation', 'Orientación', ['portrait' => 'Vertical', 'landscape' => 'Horizontal']) ?>
        <?= $designRange('page_margin', 'Márgenes', '12', '35', '1', ' mm') ?>
        <?= $designRange('section_spacing', 'Separación entre secciones', '4', '24', '1', ' mm') ?>
      </div>
      <div class="designer-toggle-list">
        <?php foreach (['show_page_header' => 'Mostrar encabezado de página', 'show_page_footer' => 'Mostrar pie de página', 'show_page_numbers' => 'Numerar páginas', 'show_dividers' => 'Mostrar divisores de sección'] as $field => $label): ?>
          <label class="designer-toggle"><span><?= h($label) ?></span><input type="hidden" name="<?= h($field) ?>" value="0"><input type="checkbox" name="<?= h($field) ?>" value="1" <?= $design[$field] ? 'checked' : '' ?> data-design-control></label>
        <?php endforeach; ?>
      </div>
    </details>

    <div class="designer-actions">
      <button class="btn btn-light" type="submit" name="action" value="save">Guardar diseño</button>
      <button class="btn btn-primary" type="submit" name="action" value="save_export">Guardar y generar PDF</button>
    </div>
  </form>

  <aside class="designer-preview-panel" aria-label="Vista previa del diseño">
    <div class="designer-preview-heading"><div><span class="eyebrow">VISTA PREVIA</span><h2>Así se verá tu catálogo</h2></div><span class="preview-live"><i></i> En vivo</span></div>
    <div class="preview-page-spec" id="previewPageSpec"><?= h($design['page_size']) ?> · <?= $design['page_orientation'] === 'portrait' ? 'vertical' : 'horizontal' ?> · <?= (int)$design['page_margin'] ?> mm de margen</div>
    <div class="designer-preview template-<?= h($design['template']) ?> cover-<?= h($design['cover_style']) ?> card-<?= h($design['card_style']) ?> photo-<?= h($design['photo_position']) ?> corners-<?= h($design['card_corners']) ?> price-<?= h($design['price_style']) ?> category-<?= h($design['category_style']) ?> paper-<?= h($design['page_size']) ?> paper-<?= h($design['page_orientation']) ?> <?= $design['show_dividers'] ? 'has-dividers' : '' ?>" id="designPreview" style="--design-primary:<?= h($design['primary_color']) ?>;--design-secondary:<?= h($design['secondary_color']) ?>;--design-background:<?= h($design['background_color']) ?>;--design-text:<?= h($design['text_color']) ?>;--design-card-background:<?= h($design['card_background']) ?>;--design-title-color:<?= h($design['title_color']) ?>;--design-subtitle-color:<?= h($design['subtitle_color']) ?>;--design-description-color:<?= h($design['description_color']) ?>;--design-price-color:<?= h($design['price_color']) ?>;--design-border:<?= h($design['border_color']) ?>;--design-category-color:<?= h($design['category_color']) ?>;--design-cover-color:<?= h($design['cover_color']) ?>;--design-cover-title-color:<?= h($design['cover_title_color']) ?>;--design-font:'<?= h($design['font']) ?>';--font-title:'<?= h($design['title_font']) ?>';--font-subtitle:'<?= h($design['subtitle_font']) ?>';--font-description:'<?= h($design['description_font']) ?>';--font-price:'<?= h($design['price_font']) ?>';--font-category:'<?= h($design['category_font']) ?>';--preview-columns:<?= (int)$design['columns'] ?>;--preview-image-height:<?= $design['image_size'] === 'small' ? '74px' : ($design['image_size'] === 'large' ? '142px' : '108px') ?>;--preview-gap:<?= (int)$design['product_gap'] ?>px;--preview-section-spacing:<?= (int)$design['section_spacing'] ?>px;--preview-radius:<?= $design['card_corners'] === 'square' ? 0 : (int)$design['card_radius'] ?>px;--preview-margin:<?= max(8, min(22, (int)$design['page_margin'])) ?>px;--preview-title-size:<?= (int)$design['title_size'] ?>pt;--preview-subtitle-size:<?= (int)$design['subtitle_size'] ?>pt;--preview-description-size:<?= (int)$design['description_size'] ?>pt;--preview-price-size:<?= (int)$design['price_size'] ?>pt;--preview-category-size:<?= (int)$design['category_size'] ?>pt;--preview-cover-size:<?= (int)$design['cover_title_size'] ?>pt;--preview-title-line:<?= (float)$design['title_line_height'] ?>;--preview-subtitle-line:<?= (float)$design['subtitle_line_height'] ?>;--preview-description-line:<?= (float)$design['description_line_height'] ?>;--title-weight:<?= h($design['title_weight']) ?>;--subtitle-weight:<?= h($design['subtitle_weight']) ?>;--description-weight:<?= h($design['description_weight']) ?>;--price-weight:<?= h($design['price_weight']) ?>;--category-weight:<?= h($design['category_weight']) ?>;--title-letter-spacing:<?= h((string)$design['title_letter_spacing']) ?>px;--subtitle-letter-spacing:<?= h((string)$design['subtitle_letter_spacing']) ?>px;--title-transform:<?= h($design['title_transform']) ?>;--subtitle-transform:<?= h($design['subtitle_transform']) ?>;--title-align:<?= h($design['title_align']) ?>;--subtitle-align:<?= h($design['subtitle_align']) ?>;--description-align:<?= h($design['description_align']) ?>;--price-align:<?= h($design['price_align']) ?>;--cover-alignment:<?= h($design['cover_alignment']) ?>;--cover-image-position:<?= h($design['cover_image_position']) ?>;--cover-image-fit:<?= h($design['cover_image_fit']) ?>;--logo-size:<?= (int)$design['logo_size'] ?>px;text-align:<?= h($design['text_alignment']) ?>">
      <div class="preview-page-header" id="previewPageHeader" <?= $design['show_page_header'] ? '' : 'hidden' ?>><?= h($catalog['name']) ?></div>
      <div class="preview-cover">
        <img class="preview-cover-image" id="previewCoverImage" src="<?= $design['cover_image'] === null ? '' : h(product_image_url($design['cover_image']) ?? '') ?>" alt="" data-current-src="<?= $design['cover_image'] === null ? '' : h(product_image_url($design['cover_image']) ?? '') ?>" <?= $design['cover_image'] === null ? 'hidden' : '' ?>>
        <div class="preview-cover-content">
          <img class="preview-logo" id="previewLogo" src="<?= $design['logo'] === null ? '' : h(product_image_url($design['logo']) ?? '') ?>" alt="Logotipo" data-current-src="<?= $design['logo'] === null ? '' : h(product_image_url($design['logo']) ?? '') ?>" <?= $design['logo'] === null ? 'hidden' : '' ?>>
          <span class="preview-kicker" id="previewKicker" <?= $design['show_decorations'] ? '' : 'hidden' ?>>CATALOG STUDIO</span>
          <h3 id="previewTitle" <?= $design['show_cover_title'] ? '' : 'hidden' ?>><?= h($design['cover_title']) ?></h3>
          <p id="previewSubtitle" <?= $design['show_cover_subtitle'] && $design['cover_subtitle'] !== '' ? '' : 'hidden' ?>><?= h($design['cover_subtitle']) ?></p>
          <div class="preview-cover-meta"><span id="previewDate" <?= $design['show_cover_date'] ? '' : 'hidden' ?>><?= date('d/m/Y') ?></span><span id="previewCategory" <?= $design['show_cover_category'] ? '' : 'hidden' ?>><?= h($catalog['category'] ?: 'General') ?></span><span id="previewProductCount" <?= $design['show_cover_product_count'] ? '' : 'hidden' ?>><?= $productCount ?> productos</span></div>
        </div>
      </div>
      <div class="preview-page-footer" id="previewPageFooter" <?= $design['show_page_footer'] ? '' : 'hidden' ?>><span><?= h($catalog['name']) ?></span><span id="previewPageNumber" <?= $design['show_page_numbers'] ? '' : 'hidden' ?>>1</span></div>
      <div class="preview-product-grid" id="previewProductGrid">
        <?php if (!$previewProducts): ?>
          <div class="preview-empty">Los productos aparecerán aquí cuando el catálogo tenga contenido.</div>
        <?php else: ?>
          <?php foreach ($previewProducts as $product): ?>
            <?php $imageUrl = product_image_url($product['image']); ?>
            <article class="preview-product photo-position-<?= h($design['photo_position']) ?> <?= mb_strlen(trim((string)$product['description']), 'UTF-8') > 400 ? 'product-wide-preview' : '' ?>" data-long-description="<?= mb_strlen(trim((string)$product['description']), 'UTF-8') > 400 ? '1' : '0' ?>">
              <div class="preview-product-image"><?php if ($imageUrl !== null): ?><img src="<?= h($imageUrl) ?>" alt=""><?php else: ?><span>◇</span><?php endif; ?></div>
              <div class="preview-product-info">
                <span class="preview-product-category" <?= $design['show_categories'] ? '' : 'hidden' ?>><?= h($product['category_name'] ?: 'Sin categoría') ?></span>
                <h4><?= h($product['name']) ?></h4>
                <p class="preview-product-description" <?= $design['show_descriptions'] && trim((string)$product['description']) !== '' ? '' : 'hidden' ?>><?= h($product['description']) ?></p>
                <strong class="preview-product-price" <?= $design['show_prices'] ? '' : 'hidden' ?>>$<?= number_format((float)$product['price'], 2, ',', '.') ?></strong>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <form class="designer-reset-form" method="post" action="catalog_design_reset.php" onsubmit="return confirm('¿Restaurar el diseño predeterminado? Se quitarán el logotipo y la portada personalizados.');">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= $catalogId ?>">
      <button class="btn btn-light" type="submit">Restaurar diseño predeterminado</button>
      <a class="text-link" href="export_pdf.php?id=<?= $catalogId ?>&amp;download=1">Generar PDF guardado</a>
    </form>
  </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
