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
  const bindProductFilters = () => {
    const search = document.getElementById('productSearch');
    const catalog = document.getElementById('productCatalogFilter');
    const category = document.getElementById('productCategoryFilter');
    if (!search || !catalog || !category) return;
    const rows = [...document.querySelectorAll('.product-row')];
    const empty = document.getElementById('productSearchEmpty');
    const update = () => {
      const term = search.value.trim().toLocaleLowerCase('es');
      const catalogId = catalog.value;
      const categoryId = category.value;
      let visible = 0;
      rows.forEach((row) => {
        const matches = (row.dataset.productName || '').includes(term)
          && (!catalogId || row.dataset.catalogId === catalogId)
          && (!categoryId || row.dataset.categoryId === categoryId);
        row.hidden = !matches;
        if (matches) visible += 1;
      });
      if (empty) empty.hidden = !(term || catalogId || categoryId) || visible > 0;
    };
    search.addEventListener('input', update);
    catalog.addEventListener('change', update);
    category.addEventListener('change', update);
  };
  bindProductFilters();

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

  const designForm = document.getElementById('catalogDesignForm');
  const designPreview = document.getElementById('designPreview');
  if (designForm && designPreview) {
    const palettes = {
      minimalista: ['#6755B8', '#AAA0D8', '#FFFFFF', '#29263D', '#FFFFFF', '#29263D', '#29263D', '#29263D', '#6755B8', '#E7E4EF', '#6755B8', '#6755B8', '#FFFFFF'],
      elegante: ['#483B45', '#B99A78', '#FBF8F4', '#29242A', '#FFFFFF', '#29242A', '#51464E', '#51464E', '#9B704A', '#E6DED4', '#B99A78', '#483B45', '#FFFFFF'],
      corporativa: ['#315B94', '#6B9D9A', '#F4F6F9', '#263449', '#FFFFFF', '#263449', '#47566C', '#47566C', '#315B94', '#DCE2EA', '#315B94', '#315B94', '#FFFFFF'],
      calida: ['#B74E36', '#E5A14E', '#FFF8EE', '#3C3028', '#FFFFFF', '#3C3028', '#58463A', '#58463A', '#A9422F', '#EAD9C7', '#B74E36', '#B74E36', '#FFFFFF'],
      premium_oscuro: ['#D6B875', '#806E4C', '#211F20', '#F5F0E6', '#2E2B2A', '#F5F0E6', '#D6D0C4', '#D6D0C4', '#E2C37B', '#514B43', '#D6B875', '#211F20', '#F5F0E6'],
      moderna: ['#4C58C9', '#25A7A1', '#F5F7FC', '#252943', '#FFFFFF', '#30344E', '#545B73', '#545B73', '#3545B5', '#E0E4F0', '#4C58C9', '#4C58C9', '#FFFFFF'],
      comercial: ['#D94E5D', '#F0B64B', '#FFF9F0', '#302832', '#FFFFFF', '#302832', '#574B50', '#574B50', '#C43E50', '#F0D7CC', '#D94E5D', '#D94E5D', '#FFFFFF']
    };
    const colorFields = ['primary_color', 'secondary_color', 'background_color', 'text_color', 'card_background', 'title_color', 'subtitle_color', 'description_color', 'price_color', 'border_color', 'category_color', 'cover_color', 'cover_title_color'];
    const colorVars = ['primary', 'secondary', 'background', 'text', 'card-background', 'title-color', 'subtitle-color', 'description-color', 'price-color', 'border', 'category-color', 'cover-color', 'cover-title-color'];
    const fontFields = ['title_font', 'subtitle_font', 'description_font', 'price_font', 'category_font'];
    const fontVars = ['title', 'subtitle', 'description', 'price', 'category'];
    const paletteSelect = designForm.elements.namedItem('palette');
    const templateSelect = designForm.elements.namedItem('template');
    const liveStatus = document.querySelector('.preview-live');
    const imageUrls = new Map();
    const setVisible = (selector, visible) => designPreview.querySelectorAll(selector).forEach((node) => { node.hidden = !visible; });
    const checked = (name) => Boolean(designForm.querySelector(`input[type="checkbox"][name="${name}"]`)?.checked);
    const updatePreview = (isDirty = true) => {
      const read = (name) => designForm.elements.namedItem(name);
      const setVar = (name, value) => designPreview.style.setProperty(name, value);
      const selectedTemplate = read('template').value;
      designPreview.classList.remove('template-minimalista', 'template-premium', 'template-comercial', 'template-corporativo');
      designPreview.classList.add(`template-${selectedTemplate}`);
      colorFields.forEach((field, index) => setVar(`--design-${colorVars[index]}`, read(field).value));
      fontFields.forEach((field, index) => setVar(`--font-${fontVars[index]}`, `'${read(field).value}'`));
      ['title_weight', 'subtitle_weight', 'description_weight', 'price_weight', 'category_weight', 'title_letter_spacing', 'subtitle_letter_spacing', 'title_transform', 'subtitle_transform', 'title_align', 'subtitle_align', 'description_align', 'price_align', 'category_style', 'price_style', 'cover_alignment', 'cover_image_position', 'cover_image_fit'].forEach((field) => {
        const control = read(field);
        if (control) setVar(`--${field.replaceAll('_', '-')}`, control.value);
      });
      const columns = read('columns').value;
      setVar('--preview-columns', columns);
      const imageSize = read('image_size').value;
      const ratio = read('image_ratio').value;
      let imageHeight = imageSize === 'small' ? 74 : imageSize === 'large' ? 142 : 108;
      if (ratio === 'square') imageHeight *= .86;
      if (ratio === 'portrait') imageHeight *= 1.3;
      if (ratio === 'landscape') imageHeight *= .68;
      setVar('--preview-image-height', `${Math.round(imageHeight)}px`);
      setVar('--preview-gap', `${read('product_gap').value}px`);
      setVar('--preview-section-spacing', `${read('section_spacing').value}px`);
      setVar('--preview-radius', read('card_corners').value === 'square' ? '0px' : `${read('card_radius').value}px`);
      setVar('--preview-margin', `${Math.max(8, Math.min(22, Number(read('page_margin').value)))}px`);
      setVar('--preview-title-size', `${read('title_size').value}pt`);
      setVar('--preview-subtitle-size', `${read('subtitle_size').value}pt`);
      setVar('--preview-description-size', `${read('description_size').value}pt`);
      setVar('--preview-price-size', `${read('price_size').value}pt`);
      setVar('--preview-category-size', `${read('category_size').value}pt`);
      setVar('--preview-cover-size', `${read('cover_title_size').value}pt`);
      setVar('--preview-title-line', read('title_line_height').value);
      setVar('--preview-subtitle-line', read('subtitle_line_height').value);
      setVar('--preview-description-line', read('description_line_height').value);
      setVar('--logo-size', `${read('logo_size').value}px`);
      designPreview.style.textAlign = read('text_alignment').value;

      ['card-minimal', 'card-bordered', 'card-filled', 'corners-square', 'corners-rounded', 'price-highlight', 'price-subtle', 'category-plain', 'category-pill', 'category-uppercase', 'paper-A4', 'paper-letter', 'paper-portrait', 'paper-landscape', 'photo-top', 'photo-left', 'photo-right', 'photo-editorial', 'has-dividers'].forEach((className) => designPreview.classList.remove(className));
      designPreview.classList.add(`card-${read('card_style').value}`, `corners-${read('card_corners').value}`, `price-${read('price_style').value}`, `category-${read('category_style').value}`, `paper-${read('page_size').value}`, `paper-${read('page_orientation').value}`, `photo-${read('photo_position').value}`);
      if (checked('show_dividers')) designPreview.classList.add('has-dividers');
      const pageSpec = document.getElementById('previewPageSpec');
      if (pageSpec) pageSpec.textContent = `${read('page_size').value} · ${read('page_orientation').value === 'portrait' ? 'vertical' : 'horizontal'} · ${read('page_margin').value} mm de margen`;

      const cover = designPreview.querySelector('.preview-cover');
      ['minimalista', 'fotografica', 'editorial', 'corporativa', 'premium', 'centered', 'banner'].forEach((style) => cover.classList.remove(`cover-${style}`));
      ['top', 'center', 'bottom'].forEach((position) => cover.classList.remove(`cover-position-${position}`));
      cover.classList.add(`cover-${read('cover_style').value}`, `cover-position-${read('cover_title_position').value}`);
      cover.style.textAlign = read('cover_alignment').value;
      cover.style.setProperty('--cover-alignment', read('cover_alignment').value);
      const logo = designPreview.querySelector('#previewLogo');
      logo.classList.remove('logo-left', 'logo-center', 'logo-right');
      logo.classList.add(`logo-${read('logo_position').value}`);
      const title = designPreview.querySelector('#previewTitle');
      const subtitle = designPreview.querySelector('#previewSubtitle');
      title.textContent = read('cover_title').value;
      subtitle.textContent = read('cover_subtitle').value;
      setVisible('#previewTitle', checked('show_cover_title') && title.textContent.trim() !== '');
      setVisible('#previewSubtitle', checked('show_cover_subtitle') && subtitle.textContent.trim() !== '');
      setVisible('#previewDate', checked('show_cover_date'));
      setVisible('#previewCategory', checked('show_cover_category'));
      setVisible('#previewProductCount', checked('show_cover_product_count'));
      setVisible('#previewKicker', checked('show_decorations'));
      setVisible('#previewPageHeader', checked('show_page_header'));
      setVisible('#previewPageFooter', checked('show_page_footer'));
      setVisible('#previewPageNumber', checked('show_page_numbers'));
      setVisible('.preview-product-category', checked('show_categories'));
      setVisible('.preview-product-description', checked('show_descriptions'));
      setVisible('.preview-product-price', checked('show_prices'));
      designPreview.querySelectorAll('.preview-product').forEach((card) => {
        card.classList.remove('photo-position-top', 'photo-position-left', 'photo-position-right', 'photo-position-editorial');
        card.classList.add(`photo-position-${read('photo_position').value}`);
        card.classList.toggle('product-wide-preview', card.dataset.longDescription === '1' || read('photo_position').value === 'editorial');
      });
      designPreview.querySelectorAll('[data-range-output]').forEach((output) => {
        const control = read(output.dataset.rangeOutput);
        const rangeName = output.dataset.rangeOutput;
        const suffix = ['title_size', 'subtitle_size', 'description_size', 'price_size', 'category_size', 'cover_title_size'].includes(rangeName) ? ' pt' : rangeName === 'page_margin' || rangeName === 'section_spacing' ? ' mm' : ['product_gap', 'card_radius', 'logo_size', 'title_letter_spacing', 'subtitle_letter_spacing'].includes(rangeName) ? ' px' : '';
        output.textContent = `${control.value}${suffix}`;
      });

      if (liveStatus && isDirty) {
        liveStatus.classList.add('is-dirty');
        liveStatus.textContent = 'Cambios sin guardar';
      }
    };
    const applyPalette = (name) => {
      const colors = palettes[name];
      if (!colors) return;
      paletteSelect.value = name;
      colorFields.forEach((field, index) => { designForm.elements.namedItem(field).value = colors[index]; });
      updatePreview();
    };
    const comboPresets = {
      elegante: { title_font: 'DejaVu Serif', subtitle_font: 'DejaVu Sans', description_font: 'DejaVu Sans', price_font: 'DejaVu Sans', category_font: 'DejaVu Sans', title_weight: '700', price_weight: '700' },
      moderna: { title_font: 'DejaVu Sans', subtitle_font: 'DejaVu Sans', description_font: 'DejaVu Sans', price_font: 'DejaVu Sans', category_font: 'DejaVu Sans', title_weight: '700', price_weight: '700' },
      editorial: { title_font: 'DejaVu Serif', subtitle_font: 'DejaVu Serif', description_font: 'DejaVu Serif', price_font: 'DejaVu Serif', category_font: 'DejaVu Sans', title_weight: '400', price_weight: '700' },
      minimalista: { title_font: 'DejaVu Sans', subtitle_font: 'DejaVu Sans', description_font: 'DejaVu Sans', price_font: 'DejaVu Sans', category_font: 'DejaVu Sans', title_weight: '400', price_weight: '400' },
      comercial: { title_font: 'DejaVu Sans', subtitle_font: 'DejaVu Sans', description_font: 'DejaVu Sans', price_font: 'DejaVu Sans', category_font: 'DejaVu Sans', title_weight: '700', price_weight: '700', price_style: 'highlight' }
    };
    const templatePalette = { minimalista: 'minimalista', premium: 'premium_oscuro', comercial: 'comercial', corporativo: 'corporativa' };
    templateSelect.addEventListener('change', () => applyPalette(templatePalette[templateSelect.value] || 'minimalista'));
    paletteSelect.addEventListener('change', () => applyPalette(paletteSelect.value));
    designForm.elements.namedItem('typography_combo').addEventListener('change', (event) => {
      Object.entries(comboPresets[event.target.value] || {}).forEach(([field, value]) => { designForm.elements.namedItem(field).value = value; });
      updatePreview();
    });
    designForm.querySelectorAll('[data-design-control]').forEach((control) => {
      control.addEventListener('input', () => updatePreview());
      control.addEventListener('change', () => updatePreview());
    });

    [
      { inputId: 'designerLogoInput', previewId: 'designerLogoPreview', liveId: 'previewLogo', wrapId: 'designerLogoPreviewWrap', removeName: 'remove_logo' },
      { inputId: 'designerCoverInput', previewId: 'designerCoverPreview', liveId: 'previewCoverImage', wrapId: 'designerCoverPreviewWrap', removeName: 'remove_cover_image' }
    ].forEach(({ inputId, previewId, liveId, wrapId, removeName }) => {
      const input = document.getElementById(inputId);
      const image = document.getElementById(previewId);
      const liveImage = document.getElementById(liveId);
      const wrap = document.getElementById(wrapId);
      const remove = designForm.querySelector(`[name="${removeName}"][type="checkbox"]`);
      if (!input || !image || !wrap) return;
      input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        const priorUrl = imageUrls.get(inputId);
        if (priorUrl) URL.revokeObjectURL(priorUrl);
        imageUrls.delete(inputId);
        if (!file) {
          const currentSrc = image.dataset.currentSrc || '';
          image.setAttribute('src', currentSrc);
          image.hidden = currentSrc === '';
          if (liveImage) { liveImage.setAttribute('src', currentSrc); liveImage.hidden = currentSrc === ''; }
          wrap.hidden = currentSrc === '';
          updatePreview();
          return;
        }
        const allowed = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type);
        const status = document.getElementById('designerUploadStatus');
        if (!allowed || file.size > 3 * 1024 * 1024) {
          input.value = '';
          if (status) status.textContent = !allowed ? 'Usa una imagen JPG, PNG o WEBP.' : 'La imagen seleccionada supera el límite de 3 MB.';
          const currentSrc = image.dataset.currentSrc || '';
          image.setAttribute('src', currentSrc);
          image.hidden = currentSrc === '';
          if (liveImage) { liveImage.setAttribute('src', currentSrc); liveImage.hidden = currentSrc === ''; }
          wrap.hidden = currentSrc === '';
          return;
        }
        const url = URL.createObjectURL(file);
        imageUrls.set(inputId, url);
        image.src = url;
        image.hidden = false;
        if (liveImage) { liveImage.src = url; liveImage.hidden = false; }
        wrap.hidden = false;
        if (remove) remove.checked = false;
        if (status) status.textContent = '';
        updatePreview();
      });
      if (remove) remove.addEventListener('change', () => {
        if (remove.checked) {
          input.value = '';
          image.hidden = true;
          if (liveImage) liveImage.hidden = true;
        } else {
          const currentSrc = image.dataset.currentSrc || '';
          image.setAttribute('src', currentSrc);
          image.hidden = currentSrc === '';
          if (liveImage) { liveImage.setAttribute('src', currentSrc); liveImage.hidden = currentSrc === ''; }
        }
        wrap.hidden = image.hidden;
        updatePreview();
      });
    });
    designForm.addEventListener('submit', () => {
      if (designForm.checkValidity()) {
        designForm.querySelectorAll('button[type="submit"]').forEach((button) => {
          if (button === document.activeElement) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
          }
        });
      }
    });
    window.addEventListener('beforeunload', () => imageUrls.forEach((url) => URL.revokeObjectURL(url)));
    updatePreview(false);
  }
});
