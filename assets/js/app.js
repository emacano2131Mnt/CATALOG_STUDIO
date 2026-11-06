document.addEventListener('DOMContentLoaded', () => {
  const bindSearch = (inputId, rowSelector) => {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('input', () => {
      const term = input.value.toLocaleLowerCase('es');
      document.querySelectorAll(rowSelector).forEach(item => {
        item.style.display = item.textContent.toLocaleLowerCase('es').includes(term) ? '' : 'none';
      });
    });
  };
  bindSearch('catalogSearch', '#catalogGrid .searchable');
  bindSearch('productSearch', '.product-row');

  const imageInput = document.getElementById('imageInput');
  const imagePreview = document.getElementById('imagePreview');
  const imagePlaceholder = document.getElementById('imagePlaceholder');
  const imagePreviewStatus = document.getElementById('imagePreviewStatus');
  let previewUrl = null;

  if (imageInput && imagePreview && imagePlaceholder) {
    imageInput.addEventListener('change', () => {
      const file = imageInput.files && imageInput.files[0];
      if (!file) {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        const currentUrl = imagePreview.dataset.currentSrc || '';
        imagePreview.src = currentUrl;
        imagePreview.hidden = currentUrl === '';
        imagePlaceholder.hidden = currentUrl !== '';
        if (imagePreviewStatus) imagePreviewStatus.textContent = '';
        return;
      }

      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = URL.createObjectURL(file);
      imagePreview.src = previewUrl;
      imagePreview.alt = 'Vista previa de la fotografía seleccionada';
      imagePreview.hidden = false;
      imagePlaceholder.hidden = true;

      if (imagePreviewStatus) {
        imagePreviewStatus.textContent = file.size > 3 * 1024 * 1024
          ? 'La imagen seleccionada supera el límite de 3 MB.'
          : '';
      }
    });

    imagePreview.addEventListener('error', () => {
      imagePreview.hidden = true;
      imagePlaceholder.hidden = false;
      if (imagePreviewStatus) imagePreviewStatus.textContent = 'No fue posible mostrar la vista previa.';
    });
  }
});
