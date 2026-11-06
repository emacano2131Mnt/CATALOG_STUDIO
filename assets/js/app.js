document.addEventListener('DOMContentLoaded', () => {
  const shell = document.getElementById('appShell');
  const menuToggle = document.getElementById('menuToggle');
  const backdrop = document.getElementById('sidebarBackdrop');
  const sidebar = document.getElementById('appSidebar');

  const setMenuOpen = (open) => {
    if (!shell || !menuToggle) return;
    shell.classList.toggle('sidebar-open', open);
    menuToggle.setAttribute('aria-expanded', String(open));
    menuToggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    document.body.classList.toggle('menu-open', open);
    if (sidebar) {
      const isMobile = window.innerWidth <= 820;
      sidebar.inert = isMobile && !open;
      sidebar.setAttribute('aria-hidden', String(isMobile && !open));
    }
  };

  if (menuToggle) {
    menuToggle.addEventListener('click', () => {
      setMenuOpen(menuToggle.getAttribute('aria-expanded') !== 'true');
    });
  }
  if (backdrop) backdrop.addEventListener('click', () => setMenuOpen(false));
  if (sidebar) {
    sidebar.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => setMenuOpen(false));
    });
  }
  setMenuOpen(false);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && shell && shell.classList.contains('sidebar-open')) {
      setMenuOpen(false);
      menuToggle?.focus();
    }
  });
  window.addEventListener('resize', () => {
    if (window.innerWidth > 820) {
      setMenuOpen(false);
    } else {
      setMenuOpen(Boolean(shell && shell.classList.contains('sidebar-open')));
    }
  });

  const bindSearch = (inputId, itemSelector, emptyId) => {
    const input = document.getElementById(inputId);
    if (!input) return;
    const emptyState = emptyId ? document.getElementById(emptyId) : null;

    const updateResults = () => {
      const term = input.value.trim().toLocaleLowerCase('es');
      let visibleCount = 0;
      document.querySelectorAll(itemSelector).forEach((item) => {
        const matches = item.textContent.toLocaleLowerCase('es').includes(term);
        item.hidden = !matches;
        if (matches) visibleCount += 1;
      });
      if (emptyState) emptyState.hidden = term === '' || visibleCount > 0;
    };

    input.addEventListener('input', updateResults);
  };

  bindSearch('catalogSearch', '#catalogGrid .searchable', 'catalogSearchEmpty');
  bindSearch('productSearch', '.product-row', 'productSearchEmpty');

  document.querySelectorAll('.form-panel').forEach((form) => {
    form.addEventListener('submit', () => {
      if (!form.checkValidity()) return;
      const submitButton = form.querySelector('button[type="submit"]');
      if (!submitButton) return;
      submitButton.disabled = true;
      submitButton.classList.add('is-loading');
      submitButton.setAttribute('aria-busy', 'true');
      submitButton.textContent = 'Guardando…';
    });
  });

  const imageInput = document.getElementById('imageInput');
  const imagePreview = document.getElementById('imagePreview');
  const imagePlaceholder = document.getElementById('imagePlaceholder');
  const imagePreviewStatus = document.getElementById('imagePreviewStatus');
  let previewUrl = null;

  if (imageInput && imagePreview && imagePlaceholder) {
    imageInput.addEventListener('change', () => {
      const file = imageInput.files && imageInput.files[0];
      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = null;

      if (!file) {
        const currentUrl = imagePreview.dataset.currentSrc || '';
        imagePreview.src = currentUrl;
        imagePreview.hidden = currentUrl === '';
        imagePlaceholder.hidden = currentUrl !== '';
        if (imagePreviewStatus) imagePreviewStatus.textContent = '';
        return;
      }

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
    window.addEventListener('beforeunload', () => {
      if (previewUrl) URL.revokeObjectURL(previewUrl);
    });
  }
});
