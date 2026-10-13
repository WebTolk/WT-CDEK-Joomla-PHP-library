/**
 * Declarative Joomla Form dependencies and shared Script Options / AJAX data providers.
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
  if (Joomla.Watchfield) return;

  const targets = new Map();
  const sources = new Map();
  const own = (value, key) => Object.prototype.hasOwnProperty.call(value, key);
  const text = (key) => Joomla.Text._(`LIB_WTCDEK_FIELD_${key}`);
  const emit = (state, name, extra = {}) => state.target.dispatchEvent(new CustomEvent(`watchfield:${name}`, {
    bubbles: true, detail: { values: state.values, initial: state.initial, ...extra },
  }));
  const pathValue = (data, path = '') => {
    for (const key of path ? path.split('.') : []) {
      if (['__proto__', 'prototype', 'constructor'].includes(key) || data == null || !own(Object(data), key)) {
        throw new Error(text('WATCHFIELD_DATA_ERROR'));
      }
      data = data[key];
    }
    if (data == null) throw new Error(text('WATCHFIELD_DATA_ERROR'));
    return data;
  };
  const read = (nodes) => {
    const checked = nodes.filter((node) => !['radio', 'checkbox'].includes(node.type) || node.checked);
    const values = checked.flatMap((node) => node.tagName === 'SELECT' && node.multiple
      ? Array.from(node.selectedOptions, (option) => option.value)
      : [String(node.value ?? '').trim()]);
    return nodes.some((node) => node.multiple) || (nodes.length > 1 && nodes[0].type === 'checkbox')
      ? values : (values[0] ?? '');
  };
  const snapshot = (state) => Object.fromEntries(Object.entries(state.names).map(([key, name]) => [
    key, read(state.nodes.filter((node) => node.name === name)),
  ]));
  const cancel = (state) => {
    clearTimeout(state.timer);
    state.controller?.abort();
    state.controller = null;
    state.generation += 1;
    state.target.removeAttribute('aria-busy');
  };
  const load = async (state) => {
    cancel(state);
    state.values = snapshot(state);
    const key = JSON.stringify(state.values);
    if (key === state.key) return;
    state.key = key;
    state.initial = state.loads === 0;
    state.loads += 1;
    const generation = state.generation;
    try {
      if (state.config.error || Object.values(state.names).some((name) => !state.nodes.some((node) => node.name === name))) {
        throw new Error(text('WATCHFIELD_SOURCE_ERROR'));
      }
      const parameters = { ...state.config.parameters, ...state.values };
      if (state.config.source === 'ajax' && state.config.requiredAny?.length
          && !state.config.requiredAny.some((name) => String(parameters[name] ?? '').trim() !== '')) {
        emit(state, 'empty');
        return;
      }
      let data;
      if (state.config.source === 'scriptoptions') {
        data = pathValue(Joomla.getOptions(state.config.options), state.config.path);
      } else if (state.config.source === 'ajax') {
        const url = new URL(state.config.url, document.baseURI);
        if (url.origin !== location.origin) throw new Error(text('WATCHFIELD_DATA_ERROR'));
        const token = Joomla.getOptions('csrf.token');
        if (!token) throw new Error(text('WATCHFIELD_DATA_ERROR'));
        const body = new URLSearchParams();
        Object.entries(parameters).forEach(([name, value]) => {
          if (Array.isArray(value)) value.forEach((item) => body.append(`${name}[]`, item));
          else body.set(name, String(value ?? ''));
        });
        body.set(token, '1');
        state.controller = new AbortController();
        state.target.setAttribute('aria-busy', 'true');
        emit(state, 'loading');
        const response = await fetch(url, {
          method: 'POST', body, credentials: 'same-origin', signal: state.controller.signal,
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) throw new Error(text('WATCHFIELD_DATA_ERROR'));
        const result = await response.json();
        if (result.success === false) throw new Error(text('WATCHFIELD_DATA_ERROR'));
        data = pathValue(result, state.config.path);
      } else throw new Error(text('WATCHFIELD_DATA_ERROR'));
      if (generation !== state.generation || !state.target.isConnected) return;
      emit(state, 'data', { data });
    } catch (error) {
      if (generation !== state.generation || error.name === 'AbortError') return;
      state.key = null;
      emit(state, 'error', { message: error.message });
    } finally {
      if (generation === state.generation) state.target.removeAttribute('aria-busy');
    }
  };
  const schedule = (state, input) => {
    const values = snapshot(state);
    if (JSON.stringify(values) === state.key) return;
    cancel(state);
    // Invalidate immediately; an old response must not win during the debounce interval.
    state.key = null;
    state.values = values;
    emit(state, 'invalidate', { initial: false });
    state.timer = setTimeout(() => load(state), input && state.config.source === 'ajax' ? 250 : 0);
  };
  const unbind = (state) => {
    state.nodes.forEach((node) => {
      const record = sources.get(node);
      record?.targets.delete(state);
      if (record && !record.targets.size) {
        node.removeEventListener('input', record.input);
        node.removeEventListener('change', record.change);
        sources.delete(node);
      }
    });
    state.nodes = [];
  };
  const rebase = (target, name) => {
    const rows = [];
    let row = target.closest('.subform-repeatable-group');
    while (row) {
      rows.unshift(row);
      row = row.parentElement?.closest('.subform-repeatable-group');
    }
    rows.forEach((group) => {
      if (group.dataset.baseName && group.dataset.group) {
        name = name.replace(`[${group.dataset.baseName}X]`, `[${group.dataset.group}]`);
      }
    });
    return name;
  };
  const bind = (state) => {
    const form = state.target.closest('form');
    const candidates = form ? Array.from(form.elements) : Array.from(document.querySelectorAll('input[name],select[name],textarea[name]'));
    const nodes = candidates.filter((node) => Object.values(state.names).includes(node.name));
    if (nodes.length === state.nodes.length && nodes.every((node, index) => node === state.nodes[index])) return;
    unbind(state);
    state.nodes = nodes;
    state.key = null;
    nodes.forEach((node) => {
      let record = sources.get(node);
      if (!record) {
        record = { targets: new Set() };
        record.input = () => record.targets.forEach((target) => schedule(target, true));
        record.change = () => record.targets.forEach((target) => schedule(target, false));
        sources.set(node, record);
        node.addEventListener('input', record.input);
        node.addEventListener('change', record.change);
      }
      record.targets.add(state);
    });
  };
  const scan = () => {
    targets.forEach((state, target) => {
      if (!target.isConnected) { cancel(state); unbind(state); targets.delete(target); }
    });
    document.querySelectorAll('[data-watchfield]').forEach((target) => {
      let state = targets.get(target);
      if (!state) {
        try {
          const names = JSON.parse(target.dataset.watchfield);
          const config = JSON.parse(target.dataset.watchfieldOptions);
          if (!names || Array.isArray(names) || typeof names !== 'object'
              || Object.values(names).some((name) => typeof name !== 'string' || !name)) throw new Error();
          state = { target, config, names: Object.fromEntries(Object.entries(names).map(([key, name]) => [key, rebase(target, name)])),
            nodes: [], values: {}, key: null, generation: 0, loads: 0 };
          targets.set(target, state);
        } catch (error) {
          target.dispatchEvent(new CustomEvent('watchfield:error', { bubbles: true, detail: { message: text('WATCHFIELD_SOURCE_ERROR') } }));
          return;
        }
      }
      bind(state);
      if (state.key === null) load(state);
    });
  };
  Joomla.Watchfield = { scan };
  document.addEventListener('joomla:updated', scan);
  const boot = () => {
    scan();
    new MutationObserver((records) => {
      const detached = Array.from(targets.values()).some((state) => !state.target.isConnected || state.nodes.some((node) => !node.isConnected));
      if (detached || records.some((record) => Array.from(record.addedNodes).some((node) =>
        node.nodeType === 1 && (node.matches('[data-watchfield]') || node.querySelector('[data-watchfield]'))))) scan();
    }).observe(document.body, { childList: true, subtree: true });
  };
  // Deferred dependencies run while readyState is interactive. Wait for the renderers too.
  if (document.readyState !== 'complete') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else queueMicrotask(boot);
})();
