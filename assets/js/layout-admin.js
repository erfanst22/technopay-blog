/* TechnoPay Mag — چیدمان صفحات (کشیدن و رها کردن). وابسته به SortableJS (MIT)، بدون jQuery. */
(function () {
  'use strict';

  var cfgEl = document.getElementById('tpl-config');
  var root = document.getElementById('tpl-app');
  if (!cfgEl || !root || typeof window.Sortable === 'undefined') return;

  var cfg = JSON.parse(cfgEl.textContent);
  var t = cfg.i18n;
  var DIGITS = '۰۱۲۳۴۵۶۷۸۹';
  var uid = 0;
  var dirty = false;

  function fa(n) { return String(n).replace(/\d/g, function (d) { return DIGITS.charAt(d); }); }

  function el(tag, attrs, children) {
    var node = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (k) {
      var v = attrs[k];
      if (k === 'class') node.className = v;
      else if (k === 'text') node.textContent = v;
      else if (v !== false && v !== null && v !== undefined) node.setAttribute(k, v === true ? '' : v);
    });
    (children || []).forEach(function (c) { if (c) node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c); });
    return node;
  }

  var live = el('div', { 'class': 'tpl-sr', role: 'status', 'aria-live': 'polite' });
  var dirtyEl = document.getElementById('tpl-dirty');

  function announce(msg) { live.textContent = ''; setTimeout(function () { live.textContent = msg; }, 30); }

  function markDirty() {
    dirty = true;
    if (dirtyEl) dirtyEl.textContent = t.unsaved;
  }

  function defaultsOf(blockId) {
    var out = {};
    var schema = cfg.blocks[blockId].settings || {};
    Object.keys(schema).forEach(function (k) { out[k] = schema[k].default; });
    return out;
  }

  /* ------------------------------------------------------------------ */
  /* فیلدهای تنظیمات هر بلوک و گزینه‌های صفحه                            */
  /* ------------------------------------------------------------------ */
  function buildField(key, field, target, onChange) {
    var id = 'tpl-f-' + (++uid);
    var value = target[key];
    var input;

    if (field.type === 'toggle') {
      input = el('input', { type: 'checkbox', id: id });
      input.checked = !!value;
      input.addEventListener('change', function () { target[key] = input.checked; onChange(); });
      return el('div', { 'class': 'tpl-field tpl-field--toggle' }, [el('label', { 'for': id }, [input, ' ' + field.label])]);
    }

    if (field.type === 'select' || field.type === 'category') {
      input = el('select', { id: id });
      if (field.type === 'category') {
        input.appendChild(el('option', { value: '0', text: t.auto }));
        cfg.categories.forEach(function (c) { input.appendChild(el('option', { value: String(c.id), text: c.name })); });
      } else {
        Object.keys(field.options).forEach(function (k) { input.appendChild(el('option', { value: k, text: field.options[k] })); });
      }
      input.value = String(value === undefined || value === null ? '' : value);
      input.addEventListener('change', function () { target[key] = field.type === 'category' ? parseInt(input.value, 10) || 0 : input.value; onChange(); });
    } else if (field.type === 'number') {
      input = el('input', { type: 'number', id: id, min: field.min, max: field.max, 'class': 'small-text' });
      input.value = value;
      input.addEventListener('change', function () {
        var n = parseInt(input.value, 10);
        if (isNaN(n)) n = field.default;
        n = Math.min(field.max, Math.max(field.min, n));
        input.value = n;
        target[key] = n;
        onChange();
      });
    } else {
      input = el('input', { type: 'text', id: id, maxlength: '80', 'class': 'regular-text' });
      input.value = value || '';
      input.addEventListener('input', function () { target[key] = input.value; onChange(); });
    }
    return el('div', { 'class': 'tpl-field' }, [el('label', { 'for': id, text: field.label }), input]);
  }

  /* ------------------------------------------------------------------ */
  /* یک بلوک (آیتم لیست)                                                   */
  /* ------------------------------------------------------------------ */
  function buildItem(blockId, settings, region) {
    var def = cfg.blocks[blockId];
    var required = region.required.indexOf(blockId) > -1;
    var li = el('li', { 'class': 'tpl-item', 'data-id': blockId, 'data-required': required ? '1' : '0' });
    li._settings = Object.assign(defaultsOf(blockId), settings || {});

    var schema = def.settings || {};
    var keys = Object.keys(schema);
    var panel = null;
    var gear = null;
    if (keys.length) {
      panel = el('div', { 'class': 'tpl-settings', id: 'tpl-s-' + (++uid), hidden: true });
      keys.forEach(function (k) { panel.appendChild(buildField(k, schema[k], li._settings, markDirty)); });
      gear = el('button', { type: 'button', 'class': 'button tpl-act tpl-act--gear', 'aria-expanded': 'false', 'aria-controls': panel.id, text: t.settings });
      gear.addEventListener('click', function () {
        var open = panel.hidden;
        panel.hidden = !open;
        gear.setAttribute('aria-expanded', open ? 'true' : 'false');
        li.classList.toggle('is-open', open);
      });
    }

    var up = el('button', { type: 'button', 'class': 'button tpl-act tpl-act--up', 'aria-label': t.moveUp + ': ' + def.label, title: t.moveUp, text: '↑' });
    var down = el('button', { type: 'button', 'class': 'button tpl-act tpl-act--down', 'aria-label': t.moveDown + ': ' + def.label, title: t.moveDown, text: '↓' });
    var add = el('button', { type: 'button', 'class': 'button tpl-act tpl-act--add', 'aria-label': t.add + ': ' + def.label, text: '+ ' + t.add });
    var remove = el('button', { type: 'button', 'class': 'button tpl-act tpl-act--remove', 'aria-label': t.remove + ': ' + def.label, text: '× ' + t.remove });

    up.addEventListener('click', function () {
      var prev = li.previousElementSibling;
      if (prev) { li.parentNode.insertBefore(li, prev); markDirty(); announce(def.label + ': ' + t.moved); up.focus(); }
    });
    down.addEventListener('click', function () {
      var next = li.nextElementSibling;
      if (next) { li.parentNode.insertBefore(next, li); markDirty(); announce(def.label + ': ' + t.moved); down.focus(); }
    });
    add.addEventListener('click', function () {
      li.closest('.tpl-region').querySelector('.tpl-list[data-list="active"]').appendChild(li);
      markDirty(); announce(def.label + ': ' + t.added); remove.focus();
    });
    remove.addEventListener('click', function () {
      li.closest('.tpl-region').querySelector('.tpl-list[data-list="pool"]').appendChild(li);
      markDirty(); announce(def.label + ': ' + t.removed); add.focus();
    });

    li.appendChild(el('div', { 'class': 'tpl-item__head' }, [
      el('span', { 'class': 'tpl-handle', 'aria-hidden': 'true', title: t.drag, text: '⠿' }),
      el('div', { 'class': 'tpl-item__text' }, [
        el('strong', { text: def.label }),
        required ? el('span', { 'class': 'tpl-badge', text: t.required }) : null,
        el('small', { text: def.desc })
      ]),
      el('div', { 'class': 'tpl-item__actions' }, [gear, up, down, add, remove])
    ]));
    if (panel) li.appendChild(panel);
    return li;
  }

  /* ------------------------------------------------------------------ */
  /* ناحیه = دو لیست (فعال / غیرفعال) با کشیدن و رها کردن                    */
  /* ------------------------------------------------------------------ */
  function buildRegion(pageId, regionId, region) {
    var items = cfg.state[pageId].regions[regionId] || [];
    var activeIds = items.map(function (i) { return i.id; });
    var activeList = el('ul', { 'class': 'tpl-list', 'data-list': 'active', 'data-empty': t.emptyActive, 'aria-label': region.label + ' — ' + t.active });
    var poolList = el('ul', { 'class': 'tpl-list', 'data-list': 'pool', 'data-empty': t.emptyPool, 'aria-label': region.label + ' — ' + t.pool });

    items.forEach(function (it) { activeList.appendChild(buildItem(it.id, it.settings, region)); });
    region.blocks.forEach(function (id) { if (activeIds.indexOf(id) === -1) poolList.appendChild(buildItem(id, null, region)); });

    var opts = {
      group: pageId + ':' + regionId,
      handle: '.tpl-handle',
      animation: 150,
      ghostClass: 'is-ghost',
      chosenClass: 'is-chosen',
      dragClass: 'is-drag',
      emptyInsertThreshold: 32,
      onMove: function (evt) {
        // بلوک‌های لازم را نمی‌شود غیرفعال کرد.
        if (evt.dragged.getAttribute('data-required') === '1' && evt.to.getAttribute('data-list') === 'pool') return false;
        return true;
      },
      onAdd: markDirty,
      onUpdate: markDirty
    };
    window.Sortable.create(activeList, opts);
    window.Sortable.create(poolList, opts);

    return el('section', { 'class': 'tpl-region', 'data-region': regionId }, [
      el('header', { 'class': 'tpl-region__head' }, [el('h3', { text: region.label }), region.desc ? el('p', { 'class': 'description', text: region.desc }) : null]),
      el('div', { 'class': 'tpl-cols' }, [
        el('div', { 'class': 'tpl-col tpl-col--active' }, [el('h4', { text: t.active }), activeList]),
        el('div', { 'class': 'tpl-col tpl-col--pool' }, [el('h4', { text: t.pool }), poolList])
      ])
    ]);
  }

  /* ------------------------------------------------------------------ */
  /* صفحه‌ها و تب‌ها                                                       */
  /* ------------------------------------------------------------------ */
  var pageIds = (cfg.order || Object.keys(cfg.pages)).filter(function (id) { return cfg.pages[id]; });
  var tabs = el('div', { 'class': 'tpl-tabs', role: 'tablist', 'aria-label': 'صفحه‌ها' });
  var panels = el('div', { 'class': 'tpl-panels' });
  var tabInput = document.getElementById('tpl-tab');

  function buildPanel(pageId) {
    var page = cfg.pages[pageId];
    var panel = el('section', { 'class': 'tpl-panel', role: 'tabpanel', id: 'tpl-panel-' + pageId, 'aria-labelledby': 'tpl-tab-' + pageId, hidden: true, 'data-page': pageId });

    var reset = el('button', { type: 'button', 'class': 'button tpl-reset', text: t.reset });
    reset.addEventListener('click', function () {
      if (!window.confirm(t.resetAsk)) return;
      dirty = false;
      document.getElementById('tpl-reset-page').value = pageId;
      document.getElementById('tpl-reset-form').submit();
    });
    panel.appendChild(el('div', { 'class': 'tpl-panel__head' }, [
      el('h2', { text: page.label }),
      el('div', { 'class': 'tpl-panel__tools' }, [el('a', { 'class': 'button', href: page.preview, target: '_blank', rel: 'noopener', text: t.preview }), reset])
    ]));

    var optKeys = Object.keys(page.options || {});
    if (optKeys.length) {
      var optBox = el('div', { 'class': 'tpl-options' }, [el('h3', { text: t.pageOptions })]);
      var optState = Object.assign({}, cfg.state[pageId].options);
      optKeys.forEach(function (k) {
        var field = buildField(k, page.options[k], optState, markDirty);
        field.querySelector('select, input').setAttribute('data-option', k);
        optBox.appendChild(field);
      });
      panel.appendChild(optBox);
    }

    Object.keys(page.regions).forEach(function (rid) { panel.appendChild(buildRegion(pageId, rid, page.regions[rid])); });
    return panel;
  }

  function select(pageId, focus) {
    pageIds.forEach(function (id) {
      var active = id === pageId;
      var tab = document.getElementById('tpl-tab-' + id);
      tab.setAttribute('aria-selected', active ? 'true' : 'false');
      tab.tabIndex = active ? 0 : -1;
      tab.classList.toggle('is-active', active);
      document.getElementById('tpl-panel-' + id).hidden = !active;
    });
    if (tabInput) tabInput.value = pageId;
    if (focus) document.getElementById('tpl-tab-' + pageId).focus();
    try { history.replaceState(null, '', location.href.replace(/([?&])tab=[^&]*/, '$1tab=' + pageId).replace(/\?$/, '')); } catch (e) { /* ignore */ }
  }

  pageIds.forEach(function (id, i) {
    var tab = el('button', { type: 'button', 'class': 'tpl-tab', role: 'tab', id: 'tpl-tab-' + id, 'aria-controls': 'tpl-panel-' + id, 'aria-selected': 'false', tabindex: '-1', text: cfg.pages[id].label });
    tab.addEventListener('click', function () { select(id); });
    tab.addEventListener('keydown', function (e) {
      // در راست‌چین، فلش چپ یعنی «بعدی».
      var next = { ArrowLeft: i + 1, ArrowRight: i - 1, Home: 0, End: pageIds.length - 1 }[e.key];
      if (next === undefined) return;
      e.preventDefault();
      select(pageIds[(next + pageIds.length) % pageIds.length], true);
    });
    tabs.appendChild(tab);
    panels.appendChild(buildPanel(id));
  });

  root.textContent = '';
  root.appendChild(tabs);
  root.appendChild(panels);
  root.appendChild(live);
  select(cfg.tab in cfg.pages ? cfg.tab : pageIds[0]);

  /* ------------------------------------------------------------------ */
  /* ذخیره                                                                */
  /* ------------------------------------------------------------------ */
  function serialize() {
    var out = { version: 1, pages: {} };
    pageIds.forEach(function (pageId) {
      var panel = document.getElementById('tpl-panel-' + pageId);
      var page = { options: {}, regions: {} };
      panel.querySelectorAll('[data-option]').forEach(function (input) { page.options[input.getAttribute('data-option')] = input.value; });
      panel.querySelectorAll('.tpl-region').forEach(function (region) {
        var rid = region.getAttribute('data-region');
        page.regions[rid] = Array.prototype.map.call(region.querySelectorAll('.tpl-list[data-list="active"] > .tpl-item'), function (li) {
          return { id: li.getAttribute('data-id'), settings: li._settings };
        });
      });
      out.pages[pageId] = page;
    });
    return out;
  }

  var form = document.getElementById('tpl-form');
  if (form) {
    form.addEventListener('submit', function () {
      document.getElementById('tpl-layout').value = JSON.stringify(serialize());
      dirty = false;
    });
  }
  window.addEventListener('beforeunload', function (e) {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });

  // برای تست‌ها و برنامه‌نویس‌ها.
  window.technopayLayoutAdmin = { serialize: serialize, fa: fa };
})();
