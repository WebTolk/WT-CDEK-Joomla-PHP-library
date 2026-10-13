/** Template-based tariff renderer shared by every watched tariff field. */
(() => {
  'use strict';
  const text = (key) => Joomla.Text._(`LIB_WTCDEK_FIELD_${key}`);
  const render = (container, templateName, literals = {}) => {
    const target = container.querySelector('[data-role="render-target"]');
    const template = container.querySelector(`template[data-template="${templateName}"]`);
    if (!target || !template) return;
    const fragment = template.content.cloneNode(true);
    fragment.querySelectorAll('[data-field]').forEach((node) => {
      node.textContent = Object.prototype.hasOwnProperty.call(literals, node.dataset.field)
        ? String(literals[node.dataset.field] ?? '') : '';
    });
    target.replaceChildren(fragment);
  };
  document.addEventListener('watchfield:data', (event) => {
    const container = event.target;
    if (!container.matches('.wt-cdek-tariffinfo-field')) return;
    const values = event.detail.values;
    const code = String(values.tariff_code ?? values.value ?? Object.values(values)[0] ?? '').trim();
    if (!code) { render(container, 'state-empty', { message: text('TARIFFINFO_SELECT') }); return; }
    const data = event.detail.data;
    if (!data || typeof data !== 'object') {
      render(container, 'state-error', { message: text('WATCHFIELD_DATA_ERROR') }); return;
    }
    const info = Object.prototype.hasOwnProperty.call(data, code) ? data[code] : null;
    if (!info || typeof info !== 'object') {
      render(container, 'state-not-found', { tariff_code: code, message: text('TARIFFINFO_NOT_FOUND') }); return;
    }
    render(container, 'tariff-card', info);
  });
  document.addEventListener('watchfield:loading', (event) => {
    if (event.target.matches('.wt-cdek-tariffinfo-field')) render(event.target, 'state-empty', { message: text('WATCHFIELD_LOADING') });
  });
  document.addEventListener('watchfield:error', (event) => {
    if (event.target.matches('.wt-cdek-tariffinfo-field')) render(event.target, 'state-error', { message: event.detail.message });
  });
})();
