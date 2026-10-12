/* TechnoPay Blog — ویرایشگر بصری (کشیدن و رها کردن روی خود صفحه). وابسته به SortableJS (MIT)، بدون jQuery. */
(function () {
  "use strict";

  var cfgEl = document.getElementById("tpb-config");
  if (!cfgEl || !window.Sortable) return;
  var cfg = JSON.parse(cfgEl.textContent);
  var page = cfg.page;
  if (!page) return;

  var settings = cfg.settings || {};
  var options = cfg.options || {};
  var styleVals = cfg.style.values || {};
  var brand = cfg.style.brand;
  var btnMode = cfg.style.btn;
  var dirty = !!cfg.dirty;
  var selected = null;
  var changed = false;       // تغییری که هنوز در پیش‌نویس ذخیره نشده
  var styleChanged = {};     // فقط کلیدهای سبکی که همین جلسه عوض شده‌اند (تا تنظیمات سفارشی‌سازی را زیر پا نگذاریم)
  var addTarget = "";
  var saveTimer = 0;
  var pending = Promise.resolve();
  var newCounter = 0;
  var T = {
    active: "ویرایشگر بصری",
    tabs: { elements: "عناصر", edit: "ویرایش", style: "سبک سایت", pages: "صفحه‌ها" },
    publish: "انتشار",
    discard: "دور انداختن",
    exit: "خروج",
    preview: "پیش‌نمایش",
    saved: "در پیش‌نویس ذخیره شد",
    saving: "در حال ذخیره…",
    published: "منتشر شد ✓",
    failed: "ذخیره نشد؛ دوباره تلاش کنید.",
    unsaved: "تغییرات منتشرنشده",
    clean: "همه چیز منتشر شده",
    dropHere: "اینجا رها کنید",
    auto: "— خودکار —"
  };

  function $(s, r) { return (r || document).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
  function el(tag, attrs, kids) {
    var n = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (k) {
      var v = attrs[k];
      if (k === "class") n.className = v;
      else if (k === "text") n.textContent = v;
      else if (v === true) n.setAttribute(k, "");
      else if (v !== false && v != null) n.setAttribute(k, v);
    });
    (kids || []).forEach(function (c) { if (c) n.appendChild(typeof c === "string" ? document.createTextNode(c) : c); });
    return n;
  }
  function blockDef(id) { return (cfg.blocks && cfg.blocks[id]) || { label: id, desc: "", kind: "wide", settings: {} }; }
  function regionDef(id) { return (cfg.regions && cfg.regions[id]) || null; }
  function allowedIn(regionId, blockId) { var r = regionDef(regionId); return !!r && r.blocks.indexOf(blockId) > -1; }
  function defaultsFor(id) {
    var out = {}, s = blockDef(id).settings || {};
    Object.keys(s).forEach(function (k) { out[k] = s[k].default; });
    return out;
  }
  function regionsOnPage() { return $$(".tpb-region").filter(function (r) { return r.getAttribute("data-tpb-page") === page; }); }
  function blocksOf(region) { return Array.prototype.filter.call(region.children, function (c) { return c.classList.contains("tpb-block"); }); }
  function regionOf(block) { return block.parentNode; }

  /* ---------- ذخیره ---------- */
  function post(action, data) {
    var fd = new FormData();
    fd.append("action", action);
    fd.append("nonce", cfg.nonce);
    Object.keys(data || {}).forEach(function (k) { fd.append(k, data[k]); });
    return fetch(cfg.ajax, { method: "POST", credentials: "same-origin", body: fd }).then(function (r) { return r.json(); });
  }

  function collect() {
    var regions = {};
    regionsOnPage().forEach(function (reg) {
      regions[reg.getAttribute("data-tpb-region")] = blocksOf(reg).map(function (b) {
        var uid = b.getAttribute("data-tpb-uid");
        return { id: b.getAttribute("data-tpb-id"), settings: settings[uid] || defaultsFor(b.getAttribute("data-tpb-id")) };
      });
    });
    return { options: options, regions: regions };
  }

  function styleSnapshot() {
    var s = {};
    Object.keys(cfg.style.schema).forEach(function (k) { if (styleChanged[k]) s[k] = styleVals[k]; });
    if (styleChanged.brand_color) s.brand_color = brand;
    if (styleChanged.button_text) s.button_text = btnMode;
    return s;
  }

  function setStatus(text, kind) {
    var s = $("#tpb-status");
    if (!s) return;
    s.textContent = text;
    s.setAttribute("data-kind", kind || "");
  }

  function draftPayload() {
    return { page: page, data: JSON.stringify(collect()), style: JSON.stringify(styleSnapshot()) };
  }

  function saveDraft() {
    clearTimeout(saveTimer);
    if (!changed) return pending; // چیزی عوض نشده: پیش‌نویس تازه ساخته نمی‌شود
    changed = false;
    setStatus(T.saving, "busy");
    pending = pending.then(function () {
      return post("tpb_save_draft", draftPayload());
    }).then(function (res) {
      if (!res || !res.success) throw new Error("save");
      dirty = !!(res.data && res.data.dirty);
      refreshDirty();
      setStatus(T.saved, "ok");
    }).catch(function () { changed = true; setStatus(T.failed, "err"); });
    return pending;
  }
  function scheduleSave() {
    changed = true;
    dirty = true;
    refreshDirty();
    clearTimeout(saveTimer);
    saveTimer = setTimeout(saveDraft, 350);
  }
  function flush() { clearTimeout(saveTimer); return saveDraft(); }

  // بستن تب یا رفرش درست بعد از یک تغییر: با sendBeacon در پیش‌نویس ذخیره می‌شود
  function beaconSave() {
    if (!changed) return;
    try {
      var fd = new FormData();
      fd.append("action", "tpb_save_draft");
      fd.append("nonce", cfg.nonce);
      var d = draftPayload();
      Object.keys(d).forEach(function (k) { fd.append(k, d[k]); });
      if (navigator.sendBeacon(cfg.ajax, fd)) changed = false;
    } catch (e) { /* ignore */ }
  }
  window.addEventListener("beforeunload", beaconSave);
  window.addEventListener("pagehide", beaconSave);

  function reloadPage() {
    var state = { y: window.scrollY, tab: currentTab };
    if (selected && document.body.contains(selected)) {
      var reg = regionOf(selected);
      state.sel = { region: reg.getAttribute("data-tpb-region"), index: blocksOf(reg).indexOf(selected) };
    }
    try { sessionStorage.setItem("tpb:" + page, JSON.stringify(state)); } catch (e) { /* ignore */ }
    flush().then(function () { location.reload(); });
  }

  function commit(reload) {
    changed = true;
    toggleEmpty();
    if (reload) { reloadPage(); } else { scheduleSave(); }
  }

  /* ---------- پنل ---------- */
  var panel, body, tabs = {}, panes = {}, currentTab = "elements";

  function buildPanel() {
    document.documentElement.classList.add("tpb-on");
    panel = el("aside", { id: "tpb-panel", dir: "rtl", "aria-label": T.active });

    var head = el("header", { class: "tpb-top" }, [
      el("div", { class: "tpb-top__title" }, [el("strong", { text: T.active }), el("span", { class: "tpb-top__page", text: cfg.pageLabel })]),
      el("button", { type: "button", class: "tpb-btn tpb-btn--ghost", "data-act": "exit", text: T.exit })
    ]);
    var nav = el("nav", { class: "tpb-tabs", role: "tablist" });
    ["elements", "edit", "style", "pages"].forEach(function (k) {
      tabs[k] = el("button", { type: "button", class: "tpb-tab", role: "tab", "data-tab": k, text: T.tabs[k] });
      nav.appendChild(tabs[k]);
    });
    body = el("div", { class: "tpb-body" });
    ["elements", "edit", "style", "pages"].forEach(function (k) { panes[k] = el("section", { class: "tpb-pane", "data-pane": k, role: "tabpanel" }); body.appendChild(panes[k]); });

    var foot = el("footer", { class: "tpb-foot" }, [
      el("div", { class: "tpb-foot__row" }, [el("span", { id: "tpb-status", class: "tpb-status", role: "status", "aria-live": "polite", text: "" })]),
      el("div", { class: "tpb-foot__row" }, [
        el("button", { type: "button", class: "tpb-btn", "data-act": "preview", text: T.preview }),
        el("button", { type: "button", class: "tpb-btn", "data-act": "discard", id: "tpb-discard", text: T.discard }),
        el("button", { type: "button", class: "tpb-btn tpb-btn--primary", "data-act": "publish", id: "tpb-publish", text: T.publish })
      ])
    ]);
    panel.appendChild(head); panel.appendChild(nav); panel.appendChild(body); panel.appendChild(foot);
    document.body.appendChild(panel);

    nav.addEventListener("click", function (e) { var b = e.target.closest("[data-tab]"); if (b) showTab(b.getAttribute("data-tab")); });
    panel.addEventListener("click", onPanelClick);
    buildElementsPane(); buildStylePane(); buildPagesPane(); buildEditPane(null);
    showTab("elements");
    refreshDirty();
    if (window.innerWidth < 1180) {
      panes.pages.insertBefore(el("p", { class: "tpb-note", text: "پنجرهٔ مرورگر کوچک است؛ برای ویرایش دقیق‌تر آن را بزرگ‌تر کنید (حداقل ۱۲۰۰ پیکسل)." }), panes.pages.firstChild);
    }
  }

  function showTab(k) {
    currentTab = k;
    Object.keys(tabs).forEach(function (t) {
      var on = t === k;
      tabs[t].classList.toggle("is-active", on);
      tabs[t].setAttribute("aria-selected", on ? "true" : "false");
      panes[t].hidden = !on;
    });
  }

  function refreshDirty() {
    var pub = $("#tpb-publish"), dis = $("#tpb-discard");
    if (!pub) return;
    pub.disabled = !dirty;
    dis.disabled = !dirty;
    if (!$("#tpb-status").textContent) setStatus(dirty ? T.unsaved : T.clean, dirty ? "warn" : "");
  }

  function onPanelClick(e) {
    var b = e.target.closest("[data-act]");
    if (!b) return;
    var act = b.getAttribute("data-act");
    if (act === "exit") {
      flush().then(function () {
        if (!dirty || confirm("تغییرات منتشرنشده در پیش‌نویس می‌ماند و بازدیدکنندگان آن را نمی‌بینند. خارج شوید؟")) location.href = cfg.urls.exit;
      });
    } else if (act === "preview") {
      flush().then(function () { window.open(cfg.urls.preview, "_blank", "noopener"); });
    } else if (act === "publish") {
      flush().then(function () { return post("tpb_publish"); }).then(function (res) {
        if (res && res.success) { dirty = false; changed = false; styleChanged = {}; setStatus(T.published, "ok"); refreshDirty(); } else { setStatus(T.failed, "err"); }
      }).catch(function () { setStatus(T.failed, "err"); });
    } else if (act === "discard") {
      if (confirm("همهٔ تغییرات منتشرنشده دور انداخته شود؟")) {
        clearTimeout(saveTimer);
        pending.then(function () { return post("tpb_discard"); }).then(function () { try { sessionStorage.removeItem("tpb:" + page); } catch (er) { /* ignore */ } location.reload(); });
      }
    }
  }

  /* ----- تب عناصر ----- */
  var paletteLists = [];
  var kindTitles = { wide: "بخش‌های تمام‌عرض", column: "اجزای مقاله", widget: "ابزارک‌ها" };

  function buildElementsPane() {
    var pane = panes.elements;
    pane.innerHTML = "";
    var banner = el("div", { class: "tpb-target", hidden: true });
    pane.appendChild(banner);
    pane.appendChild(el("p", { class: "tpb-hint", text: "یک عنصر را بکشید و روی صفحه رها کنید، یا روی «+» بزنید. برای افزودن به یک ناحیهٔ مشخص، دکمهٔ «+ افزودن عنصر» همان ناحیه را بزنید." }));
    var ids = Object.keys(cfg.blocks);
    ["wide", "column", "widget"].forEach(function (kind) {
      var list = ids.filter(function (id) { return cfg.blocks[id].kind === kind && regionsFor(id).length; });
      if (!list.length) return;
      pane.appendChild(el("h4", { class: "tpb-group", text: kindTitles[kind] }));
      var ul = el("ul", { class: "tpb-pal", "data-kind": kind });
      list.forEach(function (id) {
        var d = cfg.blocks[id];
        ul.appendChild(el("li", { class: "tpb-pal-item", "data-id": id }, [
          el("span", { class: "tpb-pal-item__text" }, [el("strong", { text: d.label }), el("small", { text: d.desc })]),
          el("button", { type: "button", class: "tpb-pal-add", "data-add": id, title: "افزودن", "aria-label": "افزودن " + d.label, text: "+" })
        ]));
      });
      pane.appendChild(ul);
      paletteLists.push(ul);
      Sortable.create(ul, {
        group: { name: "tpb", pull: "clone", put: false },
        sort: false,
        animation: 120,
        filter: ".tpb-pal-add",
        preventOnFilter: false,
        draggable: ".tpb-pal-item",
        fallbackOnBody: true,
        onStart: function (evt) { dropHints(evt.item.getAttribute("data-id")); },
        onEnd: function () { dropHints(null); }
      });
    });
    pane.addEventListener("click", function (e) {
      var b = e.target.closest("[data-add]");
      if (b) { e.preventDefault(); addFromPalette(b.getAttribute("data-add")); }
      var c = e.target.closest("[data-clear-target]");
      if (c) setAddTarget("");
    });
    pane._banner = banner;
    applyPaletteFilter();
  }

  function regionPresent(r) {
    return regionsOnPage().some(function (el) { return el.getAttribute("data-tpb-region") === r; });
  }
  function regionsFor(blockId) {
    // فقط ناحیه‌هایی که الان روی صفحه چاپ شده‌اند (مثلاً با «بدون سایدبار» ناحیهٔ سایدبار نیست)
    return Object.keys(cfg.regions).filter(function (r) { return allowedIn(r, blockId) && regionPresent(r); });
  }

  function setAddTarget(regionId) {
    addTarget = regionId;
    var banner = panes.elements._banner;
    if (regionId) {
      banner.hidden = false;
      banner.innerHTML = "";
      banner.appendChild(el("span", { text: "افزودن به: " + regionDef(regionId).label }));
      banner.appendChild(el("button", { type: "button", class: "tpb-link", "data-clear-target": "1", text: "لغو" }));
      showTab("elements");
    } else {
      banner.hidden = true;
    }
    applyPaletteFilter();
  }

  function applyPaletteFilter() {
    $$(".tpb-pal-item", panes.elements).forEach(function (li) {
      var id = li.getAttribute("data-id");
      var used = !!blockDef(id).unique && !!document.querySelector('.tpb-block[data-tpb-id="' + id + '"]');
      li.hidden = used || (addTarget ? !allowedIn(addTarget, id) : false);
    });
    $$(".tpb-group", panes.elements).forEach(function (h) {
      var ul = h.nextElementSibling;
      h.hidden = !$$(".tpb-pal-item", ul).some(function (li) { return !li.hidden; });
    });
  }

  function addFromPalette(id) {
    var regs = addTarget ? [addTarget] : regionsFor(id);
    if (!regs.length) return;
    if (regs.length === 1) { insertBlock(regs[0], id, null); return; }
    // انتخاب ناحیه
    var li = $('.tpb-pal-item[data-id="' + id + '"]', panes.elements);
    var old = $(".tpb-choose", li);
    if (old) { old.remove(); return; }
    var box = el("div", { class: "tpb-choose" }, [el("small", { text: "به کدام ناحیه؟" })]);
    regs.forEach(function (r) { box.appendChild(el("button", { type: "button", class: "tpb-btn tpb-btn--sm", "data-region": r, text: regionDef(r).label })); });
    box.addEventListener("click", function (e) { var b = e.target.closest("[data-region]"); if (b) insertBlock(b.getAttribute("data-region"), id, null); });
    li.appendChild(box);
  }

  function insertBlock(regionId, blockId, index) {
    var reg = regionsOnPage().filter(function (r) { return r.getAttribute("data-tpb-region") === regionId; })[0];
    if (!reg) return;
    var uid = "new." + (++newCounter);
    settings[uid] = defaultsFor(blockId);
    var node = el("div", { class: "tpb-block tpb-block--new", "data-tpb-id": blockId, "data-tpb-uid": uid, "data-tpb-required": "0" }, [el("div", { class: "tpb-empty-note", text: "در حال افزودن «" + blockDef(blockId).label + "»…" })]);
    var blocks = blocksOf(reg);
    if (index == null || index >= blocks.length) {
      var add = $(".tpb-region__add", reg);
      reg.insertBefore(node, add);
    } else {
      reg.insertBefore(node, blocks[index]);
    }
    setAddTarget("");
    commit(true);
  }

  function dropHints(blockId) {
    regionsOnPage().forEach(function (reg) {
      var rid = reg.getAttribute("data-tpb-region");
      reg.classList.toggle("is-drop-ok", !!blockId && allowedIn(rid, blockId));
      reg.classList.toggle("is-drop-no", !!blockId && !allowedIn(rid, blockId));
    });
    document.documentElement.classList.toggle("tpb-dragging", !!blockId);
  }

  /* ----- تب ویرایش ----- */
  function buildEditPane(block) {
    var pane = panes.edit;
    pane.innerHTML = "";
    if (!block) {
      pane.appendChild(el("p", { class: "tpb-hint", text: "روی یک عنصر در صفحه کلیک کنید تا تنظیماتش اینجا باز شود." }));
      return;
    }
    var id = block.getAttribute("data-tpb-id"), uid = block.getAttribute("data-tpb-uid");
    var d = blockDef(id);
    var required = block.getAttribute("data-tpb-required") === "1";
    settings[uid] = settings[uid] || defaultsFor(id);
    pane.appendChild(el("h3", { class: "tpb-edit__title", text: d.label }));
    pane.appendChild(el("p", { class: "tpb-hint", text: d.desc }));

    var defs = d.settings || {};
    var keys = Object.keys(defs);
    if (!keys.length) pane.appendChild(el("p", { class: "tpb-note", text: "این عنصر تنظیم اضافه‌ای ندارد." }));
    keys.forEach(function (k) { pane.appendChild(fieldFor(defs[k], settings[uid][k], function (v, immediate) { settings[uid][k] = v; if (immediate) commit(true); })); });

    // انتقال به ناحیهٔ دیگر
    var cur = regionOf(block).getAttribute("data-tpb-region");
    var regs = regionsFor(id).filter(function (r) { return r !== cur; });
    if (regs.length && !required) {
      var sel = el("select", { class: "tpb-input" }, [el("option", { value: "", text: "— انتقال به —" })].concat(regs.map(function (r) { return el("option", { value: r, text: regionDef(r).label }); })));
      sel.addEventListener("change", function () {
        if (!sel.value) return;
        var target = regionsOnPage().filter(function (r) { return r.getAttribute("data-tpb-region") === sel.value; })[0];
        if (target) { target.insertBefore(block, $(".tpb-region__add", target)); commit(true); }
      });
      pane.appendChild(el("label", { class: "tpb-field" }, [el("span", { text: "انتقال به ناحیهٔ دیگر" }), sel]));
    }
    var row = el("div", { class: "tpb-edit__actions" });
    if (!required) row.appendChild(el("button", { type: "button", class: "tpb-btn tpb-btn--danger", "data-edit-remove": "1", text: "حذف این عنصر از صفحه" }));
    else row.appendChild(el("p", { class: "tpb-note", text: "این عنصر لازم است و حذف نمی‌شود." }));
    row.addEventListener("click", function (e) { if (e.target.closest("[data-edit-remove]")) removeBlock(block); });
    pane.appendChild(row);
  }

  function fieldFor(def, value, onChange) {
    var label = el("span", { text: def.label });
    var input;
    if (def.type === "toggle") {
      input = el("input", { type: "checkbox" });
      input.checked = !!value;
      input.addEventListener("change", function () { onChange(input.checked, true); });
      return el("label", { class: "tpb-field tpb-field--toggle" }, [input, label]);
    }
    if (def.type === "number") {
      input = el("input", { type: "number", class: "tpb-input", min: def.min, max: def.max, value: value });
      input.addEventListener("change", function () { var n = parseInt(input.value, 10); if (isNaN(n)) n = def.default; onChange(Math.min(def.max, Math.max(def.min, n)), true); });
    } else if (def.type === "select") {
      input = el("select", { class: "tpb-input" });
      Object.keys(def.options).forEach(function (k) { var o = el("option", { value: k, text: def.options[k] }); if (String(value) === k) o.selected = true; input.appendChild(o); });
      input.addEventListener("change", function () { onChange(input.value, true); });
    } else if (def.type === "category") {
      input = el("select", { class: "tpb-input" }, [el("option", { value: "0", text: T.auto })]);
      cfg.categories.forEach(function (c) { var o = el("option", { value: String(c.id), text: c.name }); if (Number(value) === c.id) o.selected = true; input.appendChild(o); });
      input.addEventListener("change", function () { onChange(parseInt(input.value, 10) || 0, true); });
    } else {
      input = el("input", { type: "text", class: "tpb-input", maxlength: 80, value: value == null ? "" : value });
      input.addEventListener("input", function () { onChange(input.value, false); });
      input.addEventListener("change", function () { onChange(input.value, true); }); // با Enter یا خارج شدن از کادر اعمال می‌شود
    }
    return el("label", { class: "tpb-field" }, [label, input]);
  }

  /* ----- تب سبک سایت ----- */
  function buildStylePane() {
    var pane = panes.style;
    pane.innerHTML = "";
    pane.appendChild(el("p", { class: "tpb-hint", text: "این تنظیمات بلافاصله روی صفحه اعمال می‌شوند و با «انتشار» روی کل سایت می‌آیند." }));
    Object.keys(cfg.style.schema).forEach(function (k) {
      var d = cfg.style.schema[k];
      var out = el("output", { class: "tpb-range__out", text: styleVals[k] + d.unit });
      var inp = el("input", { type: "range", min: d.min, max: d.max, step: d.step, value: styleVals[k], "data-style": k });
      inp.addEventListener("input", function () {
        var v = parseInt(inp.value, 10);
        styleVals[k] = v;
        styleChanged[k] = true;
        out.textContent = v + d.unit;
        applyStyle(k, v);
        scheduleSave();
      });
      var reset = el("button", { type: "button", class: "tpb-link", text: "پیش‌فرض" });
      reset.addEventListener("click", function () { inp.value = d.default; inp.dispatchEvent(new Event("input")); });
      pane.appendChild(el("div", { class: "tpb-field tpb-range" }, [el("div", { class: "tpb-range__head" }, [el("span", { text: d.label }), out, reset]), inp]));
    });

    var color = el("input", { type: "color", value: brand, "aria-label": "رنگ برند" });
    var hex = el("input", { type: "text", class: "tpb-input tpb-input--ltr", value: brand, maxlength: 7, dir: "ltr" });
    var applyBrand = function (v) {
      if (!/^#[0-9a-fA-F]{6}$/.test(v)) return;
      brand = v.toLowerCase();
      styleChanged.brand_color = true;
      color.value = brand; hex.value = brand;
      brandPreview();
      scheduleSave();
    };
    color.addEventListener("input", function () { applyBrand(color.value); });
    hex.addEventListener("change", function () { applyBrand(hex.value.trim()); });
    pane.appendChild(el("div", { class: "tpb-field" }, [el("span", { text: "رنگ اصلی برند" }), el("div", { class: "tpb-color" }, [color, hex])]));

    var mode = el("select", { class: "tpb-input" }, [el("option", { value: "white", text: "سفید" }), el("option", { value: "auto", text: "خودکار (سفید یا سرمه‌ای، هر کدام خواناتر)" })]);
    mode.value = btnMode;
    mode.addEventListener("change", function () { btnMode = mode.value; styleChanged.button_text = true; styleChanged.brand_color = true; brandPreview(); scheduleSave(); });
    pane.appendChild(el("label", { class: "tpb-field" }, [el("span", { text: "رنگ متن روی دکمه‌ها" }), mode]));
  }

  function applyStyle(k, v) {
    var root = document.documentElement.style;
    if (k === "fs_scale") root.setProperty("--fs-global", String(v / 100));
    else if (k === "fs_article") root.setProperty("--fs-article", String(v / 100));
    else if (k === "container_w") root.setProperty("--container", v + "px");
    window.dispatchEvent(new Event("resize")); // منوی هدر دوباره اندازه‌گیری شود
  }

  var brandSeq = 0;
  function brandPreview() {
    var seq = ++brandSeq;
    post("tpb_brand_css", { color: brand, mode: btnMode }).then(function (res) {
      if (seq !== brandSeq || !res || !res.success) return;
      var st = $("#tpb-brand-live");
      if (!st) { st = el("style", { id: "tpb-brand-live" }); document.head.appendChild(st); }
      st.textContent = res.data.css;
    });
  }

  /* ----- تب صفحه‌ها ----- */
  function buildPagesPane() {
    var pane = panes.pages;
    pane.innerHTML = "";
    pane.appendChild(el("h4", { class: "tpb-group", text: "صفحه‌ای که ویرایش می‌کنید" }));
    var list = el("ul", { class: "tpb-pages" });
    cfg.targets.forEach(function (t) {
      var current = t.id === page;
      var b = el("button", { type: "button", class: "tpb-page" + (current ? " is-current" : ""), disabled: !t.url, text: t.label + (current ? " ✓" : "") });
      if (t.url && !current) b.addEventListener("click", function () { flush().then(function () { location.href = t.url; }); });
      list.appendChild(el("li", {}, [b, el("small", { text: t.hint })]));
    });
    pane.appendChild(list);

    var keys = Object.keys(cfg.optionDefs || {});
    if (keys.length) {
      pane.appendChild(el("h4", { class: "tpb-group", text: "تنظیمات این صفحه" }));
      keys.forEach(function (k) {
        pane.appendChild(fieldFor(cfg.optionDefs[k], options[k], function (v) { options[k] = v; commit(true); }));
      });
    }
    pane.appendChild(el("p", { class: "tpb-note" }, [
      "چیدمان به‌صورت لیست: ", el("a", { href: cfg.urls.list, text: "چیدمان لیستی" }), " · ", el("a", { href: cfg.urls.settings, text: "تنظیمات و کد تگ منیجر" }), " · ", el("a", { href: cfg.urls.hub, text: "صفحهٔ مجله تکنوپی" })
    ]));
  }

  /* ---------- عناصر داخل صفحه ---------- */
  function decorateBlock(b) {
    if (b.querySelector(":scope > .tpb-bar")) return;
    var id = b.getAttribute("data-tpb-id");
    var required = b.getAttribute("data-tpb-required") === "1";
    var bar = el("div", { class: "tpb-bar" }, [
      el("span", { class: "tpb-bar__drag", title: "برای جابه‌جایی بکشید" }, ["⠿ ", el("b", { text: blockDef(id).label })]),
      el("button", { type: "button", "data-bar": "up", title: "بالا", "aria-label": "انتقال به بالا", text: "↑" }),
      el("button", { type: "button", "data-bar": "down", title: "پایین", "aria-label": "انتقال به پایین", text: "↓" }),
      el("button", { type: "button", "data-bar": "edit", title: "تنظیمات", "aria-label": "تنظیمات", text: "⚙" }),
      required ? null : el("button", { type: "button", "data-bar": "remove", title: "حذف", "aria-label": "حذف", text: "✕" })
    ]);
    b.insertBefore(bar, b.firstChild);
  }

  function toggleEmpty() {
    regionsOnPage().forEach(function (reg) {
      var empty = blocksOf(reg).length === 0;
      reg.classList.toggle("is-empty", empty);
      if (empty) reg.setAttribute("data-tpb-hint", (regionDef(reg.getAttribute("data-tpb-region")) || {}).label + " — " + T.dropHere);
    });
  }

  function select(b) {
    if (selected) selected.classList.remove("is-selected");
    selected = b;
    if (b) { b.classList.add("is-selected"); buildEditPane(b); showTab("edit"); } else { buildEditPane(null); }
  }

  function removeBlock(b) {
    if (b.getAttribute("data-tpb-required") === "1") return;
    if (selected === b) select(null);
    b.parentNode.removeChild(b);
    applyPaletteFilter();
    commit(false);
  }

  function move(b, dir) {
    var reg = regionOf(b), list = blocksOf(reg), i = list.indexOf(b);
    var j = i + dir;
    if (j < 0 || j >= list.length) return;
    if (dir < 0) reg.insertBefore(b, list[j]); else reg.insertBefore(list[j], b);
    b.scrollIntoView({ block: "nearest", behavior: "smooth" });
    commit(false);
  }

  function initRegions() {
    regionsOnPage().forEach(function (reg) {
      blocksOf(reg).forEach(decorateBlock);
      Sortable.create(reg, {
        group: {
          name: "tpb",
          pull: true,
          put: function (to, from, dragEl) { return allowedIn(to.el.getAttribute("data-tpb-region"), dragEl.getAttribute("data-tpb-id") || dragEl.getAttribute("data-id")); }
        },
        draggable: ".tpb-block",
        handle: ".tpb-bar__drag",
        animation: 150,
        ghostClass: "tpb-ghost",
        chosenClass: "tpb-chosen",
        fallbackOnBody: true,
        swapThreshold: 0.6,
        emptyInsertThreshold: 24,
        onStart: function (evt) { dropHints(evt.item.getAttribute("data-tpb-id")); },
        onEnd: function (evt) {
          dropHints(null);
          if (evt.from === evt.to && evt.oldIndex !== evt.newIndex) commit(false);
        },
        onAdd: function (evt) {
          var item = evt.item, to = evt.to;
          if (item.classList.contains("tpb-pal-item")) {
            var id = item.getAttribute("data-id");
            var idx = Array.prototype.filter.call(to.children, function (c) { return c.classList.contains("tpb-block") || c === item; }).indexOf(item);
            item.parentNode.removeChild(item);
            insertBlock(to.getAttribute("data-tpb-region"), id, idx);
          } else {
            commit(true);
          }
        }
      });
    });
    toggleEmpty();
  }

  function initPageEvents() {
    var hover = null;
    document.addEventListener("mouseover", function (e) {
      if (e.target.closest && e.target.closest("#tpb-panel")) return;
      var b = e.target.closest ? e.target.closest(".tpb-block") : null;
      if (b === hover) return;
      if (hover) hover.classList.remove("is-hover");
      hover = b;
      if (hover) hover.classList.add("is-hover");
    });
    document.addEventListener("click", function (e) {
      if (e.target.closest("#tpb-panel")) return;
      var barBtn = e.target.closest(".tpb-bar [data-bar]");
      var addBtn = e.target.closest("[data-tpb-add]");
      var block = e.target.closest(".tpb-block");
      if (barBtn) {
        e.preventDefault(); e.stopPropagation();
        var b = barBtn.closest(".tpb-block"), act = barBtn.getAttribute("data-bar");
        if (act === "up") move(b, -1); else if (act === "down") move(b, 1); else if (act === "edit") select(b); else if (act === "remove") removeBlock(b);
        return;
      }
      if (addBtn) { e.preventDefault(); setAddTarget(addBtn.closest(".tpb-region").getAttribute("data-tpb-region")); return; }
      if (e.target.closest("a")) e.preventDefault(); // جلوگیری از رفتن به صفحهٔ دیگر
      if (block && !e.target.closest(".tpb-bar")) select(block);
    }, true);
    document.addEventListener("submit", function (e) { e.preventDefault(); }, true);
  }

  function restoreState() {
    var raw = null;
    try { raw = sessionStorage.getItem("tpb:" + page); sessionStorage.removeItem("tpb:" + page); } catch (e) { /* ignore */ }
    if (!raw) return;
    try {
      var st = JSON.parse(raw);
      if (st.tab && tabs[st.tab]) showTab(st.tab);
      if (st.sel) {
        var reg = regionsOnPage().filter(function (r) { return r.getAttribute("data-tpb-region") === st.sel.region; })[0];
        var b = reg ? blocksOf(reg)[st.sel.index] : null;
        if (b) { selected = b; b.classList.add("is-selected"); buildEditPane(b); showTab(st.tab && tabs[st.tab] ? st.tab : "edit"); }
      }
      window.scrollTo(0, st.y || 0);
      requestAnimationFrame(function () { window.scrollTo(0, st.y || 0); });
    } catch (e) { /* ignore */ }
  }

  function init() {
    buildPanel();
    initRegions();
    initPageEvents();
    restoreState();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init); else init();
})();
