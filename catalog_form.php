<?php $isEdit = isset($catalog); ?>
<div class="form-layout">
  <form class="panel form-panel" method="post">
    <?= csrf_field() ?>
    <section class="form-section">
      <span class="eyebrow">INFORMACIÓN GENERAL</span>
      <h2><?= $isEdit ? 'Editar catálogo' : 'Datos del catálogo' ?></h2>
      <p>Define la información que verán tus clientes en el catálogo.</p>

      <div class="form-grid">
        <label class="field-wide">Nombre del catálogo <span>*</span>
          <input name="name" required maxlength="120" placeholder="Ej. Colección primavera 2026" value="<?= h(form_value($formData, 'name', $catalog['name'] ?? '')) ?>">
          <small class="field-help">Usa un nombre breve y fácil de reconocer.</small>
        </label>
        <label>Categoría
          <input name="category" maxlength="80" placeholder="Ej. Moda, hogar o tecnología" value="<?= h(form_value($formData, 'category', $catalog['category'] ?? '')) ?>">
        </label>
        <label>Descripción
          <textarea name="description" rows="4" maxlength="5000" placeholder="Describe brevemente el contenido de este catálogo…"><?= h(form_value($formData, 'description', $catalog['description'] ?? '')) ?></textarea>
          <small class="field-help">Puedes actualizar esta información cuando quieras.</small>
        </label>
      </div>
    </section>
    <div class="form-actions">
      <a class="btn btn-light" href="catalogs.php">Cancelar</a>
      <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Guardar cambios' : 'Crear catálogo' ?></button>
    </div>
  </form>

  <aside class="side-tip">
    <div class="tip-art" aria-hidden="true">✦</div>
    <h3>Una buena presentación comienza aquí</h3>
    <p>Un nombre claro y una descripción útil ayudarán a tus clientes a entender tu propuesta.</p>
  </aside>
</div>
