/**
 * Delivery-point renderer. Source subscriptions and transport belong to Joomla.Watchfield.
 * @package       WT Cdek library package
 * @subpackage    Webtolk/Cdekapi
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 */
(() => {
  'use strict';
  const states = new WeakMap();
  const text = (key) => Joomla.Text._(`LIB_WTCDEK_FIELD_DELIVERYPOINTS_${key}`);
  const stateFor = (target) => {
    if (!target.matches('.wt-cdek-deliverypoints-field')) return null;
    if (!states.has(target)) states.set(target, {
      select: target.querySelector('select'), status: target.querySelector('[data-role="status"]'), saved: true,
    });
    return states.get(target);
  };
  const sync = (state) => {
    // FancySelect owns its Choices instance; rebuild from native options after AJAX.
    const fancy = state.select.closest('joomla-field-fancy-select');
    if (fancy?.choicesInstance) {
      const options = Array.from(state.select.options, (option) => ({
        value: option.value, label: option.text, selected: option.selected, disabled: option.disabled,
      }));
      fancy.choicesInstance.config.allowHTML = false;
      fancy.choicesInstance.clearStore();
      fancy.choicesInstance.setChoices(options, 'value', 'label', true);
    }
    state.select.dispatchEvent(new CustomEvent('wtcdek:options-updated', { bubbles: true }));
  };
  const clear = (state) => { state.select.replaceChildren(new Option(text('SELECT'), '')); sync(state); };
  document.addEventListener('watchfield:invalidate', (event) => {
    const state = stateFor(event.target);
    if (!state) return;
    state.saved = false;
    clear(state);
    state.status.textContent = text('CHANGED');
  });
  document.addEventListener('watchfield:loading', (event) => {
    const state = stateFor(event.target);
    if (!state) return;
    if (!event.detail.initial) { state.saved = false; clear(state); }
    state.status.textContent = text('LOADING');
  });
  document.addEventListener('watchfield:data', (event) => {
    const state = stateFor(event.target);
    if (!state) return;
    const options = event.detail.data;
    if (!Array.isArray(options) || options.some((option) => !option || typeof option.value !== 'string' || typeof option.text !== 'string')) {
      state.status.textContent = text('ERROR');
      return;
    }
    const selected = state.select.value;
    const preserve = event.detail.initial && state.saved;
    const found = options.some((option) => option.value === selected);
    const nodes = [new Option(text('SELECT'), '')];
    if (preserve && selected && !found) nodes.push(new Option(`${selected} — ${text('MISSING')}`, selected));
    options.forEach((option) => nodes.push(new Option(option.text, option.value)));
    state.select.replaceChildren(...nodes);
    state.select.value = (preserve || found) ? selected : '';
    state.status.textContent = preserve && selected && !found ? text('MISSING')
      : (!options.length ? text('EMPTY') : (!event.detail.initial ? text('CHANGED') : ''));
    sync(state);
  });
  document.addEventListener('watchfield:empty', (event) => {
    const state = stateFor(event.target);
    if (!state) return;
    if (!event.detail.initial) { state.saved = false; clear(state); }
    state.status.textContent = text('ADDRESS_REQUIRED');
  });
  document.addEventListener('watchfield:error', (event) => {
    const state = stateFor(event.target);
    if (state) state.status.textContent = text('ERROR');
  });
})();
