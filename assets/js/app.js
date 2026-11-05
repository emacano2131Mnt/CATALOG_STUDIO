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
});