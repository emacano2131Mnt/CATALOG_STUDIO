<?php
$isEdit = isset($product);
$currentImageUrl = $isEdit ? product_image_url($product['image'] ?? null) : null;
?>
<div class="form-layout">
  <form class="panel form-panel" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="form-section">
      <span class="eyebrow">DETALLES DEL PRODUCTO</span>
      <h2><?= $isEdit ? 'Editar producto' : 'Registrar producto' ?></h2>
      <p>Completa los datos para incluir este producto en tu inventario.</p>

      <div class="form-grid">
        <label>Catálogo <span>*</span>
          <select name="catalog_id" required>
            <?php foreach ($catalogOptions as $catalogOption): ?>
              <option value="<?= (int)$catalogOption['id'] ?>" <?= (int)form_value($formData, 'catalog_id', $product['catalog_id'] ?? $selectedCatalog) === (int)$catalogOption['id'] ? 'selected' : '' ?>><?= h($catalogOption['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="field-help">Elige el catálogo donde aparecerá este producto.</small>
        </label>
        <label>Categoría
          <select name="category_id">
            <option value="">Sin categoría</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= (int)$category['id'] ?>" <?= (int)form_value($formData, 'category_id', $product['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' ?>><?= h($category['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="field-wide">Nombre del producto <span>*</span>
          <input name="name" required maxlength="140" value="<?= h(form_value($formData, 'name', $product['name'] ?? '')) ?>" placeholder="Ej. Audífonos inalámbricos">
        </label>
        <label class="field-wide">Descripción
          <textarea name="description" rows="4" maxlength="5000" placeholder="Características principales…"><?= h(form_value($formData, 'description', $product['description'] ?? '')) ?></textarea>
          <small class="field-help">Incluye los detalles que ayudarán a elegir tu producto.</small>
        </label>
        <label class="field-price">Precio (COP) <span>*</span>
          <div class="input-prefix"><span aria-hidden="true">$</span><input type="number" name="price" min="0" step="0.01" required value="<?= h(form_value($formData, 'price', $product['price'] ?? '0')) ?>"></div>
        </label>
      </div>
    </section>

    <section class="form-section photo-section">
      <span class="eyebrow">IMAGEN DEL PRODUCTO</span>
      <h2>Fotografía</h2>
      <p>Una imagen clara ayuda a presentar mejor el producto.</p>
      <div class="photo-preview" aria-live="polite">
        <img id="imagePreview" data-current-src="<?= h($currentImageUrl ?? '') ?>" src="<?= h($currentImageUrl ?? '') ?>" alt="Fotografía del producto" <?= $currentImageUrl === null ? 'hidden' : '' ?>>
        <div id="imagePlaceholder" class="photo-placeholder" <?= $currentImageUrl !== null ? 'hidden' : '' ?>><span aria-hidden="true">◇</span><small><?= $isEdit ? 'Este producto no tiene fotografía' : 'La vista previa aparecerá aquí' ?></small></div>
      </div>
      <label class="file-label">Seleccionar fotografía
        <input id="imageInput" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <small class="field-help">JPG, PNG o WEBP. Tamaño máximo: 3 MB. Si no eliges una imagen, se conserva la actual.</small>
        <small id="imagePreviewStatus" class="field-help" role="status"></small>
      </label>
    </section>

    <div class="form-actions">
      <a class="btn btn-light" href="<?= $isEdit ? 'catalog_view.php?id=' . (int)$product['catalog_id'] : 'catalogs.php' ?>">Cancelar</a>
      <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Guardar cambios' : 'Guardar producto' ?></button>
    </div>
  </form>

  <aside class="side-tip">
    <div class="tip-art" aria-hidden="true">◇</div>
    <h3>Cuida los detalles</h3>
    <p>Una fotografía nítida, un nombre preciso y un precio actualizado mantienen tu catálogo claro y atractivo.</p>
  </aside>
</div>
