<?php
$isEdit = isset($catalog);
?>
<div class="form-layout"><form class="panel form-panel" method="post"><?= csrf_field() ?>
<div class="form-section"><span class="eyebrow">INFORMACIÓN GENERAL</span><h2><?= $isEdit?'Editar catálogo':'Datos del catálogo' ?></h2><p>Completa los datos que identificarán tu catálogo.</p>
<label>Nombre del catálogo <span>*</span><input name="name" required maxlength="120" placeholder="Ej. Colección primavera 2026" value="<?= h(form_value($formData, 'name', $catalog['name']??'')) ?>"></label>
<label>Categoría<input name="category" maxlength="80" placeholder="Ej. Tecnología, moda, servicios..." value="<?= h(form_value($formData, 'category', $catalog['category']??'')) ?>"></label>
<label>Descripción<textarea name="description" rows="4" placeholder="Describe brevemente el contenido de este catálogo..."><?= h(form_value($formData, 'description', $catalog['description']??'')) ?></textarea></label>
</div><div class="form-actions"><a class="btn btn-light" href="catalogs.php">Cancelar</a><button class="btn btn-primary" type="submit"><?= $isEdit?'Guardar cambios':'Crear catálogo' ?></button></div></form>
<aside class="side-tip"><div class="tip-art">✦</div><h3>Una buena presentación comienza aquí</h3><p>Usa un nombre claro y una descripción breve. Más adelante podrás agregar productos, fotografías y exportar tu catálogo en PDF.</p></aside></div>
