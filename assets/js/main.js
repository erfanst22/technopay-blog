/* TechnoPay Mag — اسکریپت‌های قالب (بدون وابستگی به jQuery) */
(function () {
  "use strict";

  var root = document.documentElement;
  var body = document.body;
  var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

  var faDigits = "۰۱۲۳۴۵۶۷۸۹";
  function toFa(value) {
    return String(value).replace(/\d/g, function (d) { return faDigits[d]; });
  }
  function formatToman(n) {
    return toFa(Math.round(n).toLocaleString("en-US").replace(/,/g, "٬"));
  }

  /* ---------- Theme (light / dark) ---------- */
  function storageGet(key) { try { return localStorage.getItem(key); } catch (e) { return null; } }
  function storageSet(key, val) { try { localStorage.setItem(key, val); } catch (e) { /* ignore */ } }

  var savedTheme = storageGet("tp-theme");
  if (savedTheme === "dark" || savedTheme === "light") root.setAttribute("data-theme", savedTheme);

  $$("[data-theme-toggle]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var current = root.getAttribute("data-theme");
      if (!current) current = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
      var next = current === "dark" ? "light" : "dark";
      root.setAttribute("data-theme", next);
      storageSet("tp-theme", next);
    });
  });

  /* ---------- Sticky header shadow + back to top ---------- */
  var header = $(".site-header");
  var toTop = $(".to-top");
  function onScroll() {
    var y = window.scrollY;
    if (header) header.classList.toggle("is-scrolled", y > 8);
    if (toTop) toTop.classList.toggle("is-visible", y > 600);
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();
  if (toTop) toTop.addEventListener("click", function () { window.scrollTo({ top: 0, behavior: "smooth" }); });

  /* ---------- Mobile drawer ---------- */
  function openDrawer() { body.classList.add("drawer-open"); var c = $(".drawer [data-drawer-close]"); if (c) c.focus(); }
  function closeDrawer() { body.classList.remove("drawer-open"); }
  $$("[data-drawer-open]").forEach(function (b) { b.addEventListener("click", openDrawer); });
  $$("[data-drawer-close]").forEach(function (b) { b.addEventListener("click", closeDrawer); });

  // زیرمنوهای منوی موبایل
  $$(".drawer-menu .menu-item-has-children").forEach(function (li, i) {
    var sub = $(".sub-menu", li);
    if (!sub) return;
    sub.id = sub.id || "drawer-sub-" + i;
    var btn = document.createElement("button");
    btn.type = "button";
    btn.className = "submenu-toggle";
    btn.setAttribute("aria-expanded", "false");
    btn.setAttribute("aria-controls", sub.id);
    btn.setAttribute("aria-label", "زیرمنو");
    btn.innerHTML = '<svg class="icon" aria-hidden="true"><use href="#i-chevron-down"></use></svg>';
    btn.addEventListener("click", function () {
      var open = li.classList.toggle("is-open");
      btn.setAttribute("aria-expanded", open ? "true" : "false");
    });
    li.insertBefore(btn, sub);
    if (li.classList.contains("current-menu-ancestor") || li.classList.contains("current-menu-parent")) {
      li.classList.add("is-open");
      btn.setAttribute("aria-expanded", "true");
    }
  });

  /* ---------- Search overlay ---------- */
  var overlay = $(".search-overlay");
  function openSearch() {
    if (!overlay) return;
    closeDrawer();
    overlay.classList.add("is-open");
    overlay.setAttribute("aria-hidden", "false");
    setTimeout(function () { var i = $("input", overlay); if (i) i.focus(); }, 50);
  }
  function closeSearch() {
    if (!overlay) return;
    overlay.classList.remove("is-open");
    overlay.setAttribute("aria-hidden", "true");
  }
  $$("[data-search-open]").forEach(function (b) { b.addEventListener("click", openSearch); });
  if (overlay) {
    overlay.addEventListener("click", function (e) { if (e.target === overlay) closeSearch(); });
  }
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") { closeSearch(); closeDrawer(); }
    var typing = /input|textarea|select/i.test(e.target.tagName);
    if (e.key === "/" && !typing) { e.preventDefault(); openSearch(); }
  });

  /* ---------- Home: category tabs ---------- */
  $$("[data-tabs]").forEach(function (tablist) {
    var tabs = $$("[role=tab]", tablist);
    function select(tab, focus) {
      tabs.forEach(function (t) {
        var active = t === tab;
        t.classList.toggle("is-active", active);
        t.setAttribute("aria-selected", active ? "true" : "false");
        t.tabIndex = active ? 0 : -1;
        var panel = document.getElementById(t.getAttribute("aria-controls"));
        if (panel) panel.hidden = !active;
      });
      if (focus) tab.focus();
    }
    tabs.forEach(function (tab, i) {
      tab.addEventListener("click", function () { select(tab); });
      tab.addEventListener("keydown", function (e) {
        // در راست‌چین، فلش چپ یعنی «بعدی»
        var next = { ArrowLeft: i + 1, ArrowRight: i - 1, Home: 0, End: tabs.length - 1 }[e.key];
        if (next === undefined) return;
        e.preventDefault();
        select(tabs[(next + tabs.length) % tabs.length], true);
      });
    });
  });

  /* ---------- Archive: list / grid view ---------- */
  $$("[data-view]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var list = $(".post-list");
      if (!list) return;
      list.classList.toggle("is-grid", btn.getAttribute("data-view") === "grid");
      $$("[data-view]").forEach(function (b) {
        b.classList.toggle("is-active", b === btn);
        b.setAttribute("aria-pressed", b === btn ? "true" : "false");
      });
    });
  });

  /* ---------- Installment calculator (estimate) ---------- */
  $$("[data-calc]").forEach(function (calc) {
    var range = $("input[type=range]", calc);
    var amountOut = $("[data-calc-amount]", calc);
    var resultOut = $("[data-calc-result]", calc);
    var rate = parseFloat(calc.getAttribute("data-rate")) || 23; // annual %
    var months = 12;

    function update() {
      var principal = parseInt(range.value, 10) * 1000000;
      var r = rate / 100 / 12;
      var payment = r === 0 ? principal / months : principal * r / (1 - Math.pow(1 + r, -months));
      amountOut.textContent = formatToman(principal) + " تومان";
      resultOut.textContent = formatToman(payment) + " تومان";
    }
    range.addEventListener("input", update);
    $$("[data-months]", calc).forEach(function (b) {
      b.addEventListener("click", function () {
        months = parseInt(b.getAttribute("data-months"), 10);
        $$("[data-months]", calc).forEach(function (x) {
          x.classList.toggle("is-active", x === b);
          x.setAttribute("aria-pressed", x === b ? "true" : "false");
        });
        update();
      });
    });
    update();
  });

  /* ---------- Copy link ---------- */
  $$("[data-copy-link]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var done = function () {
        var label = btn.getAttribute("aria-label");
        btn.setAttribute("aria-label", "کپی شد!");
        btn.classList.add("is-active");
        setTimeout(function () { btn.setAttribute("aria-label", label); btn.classList.remove("is-active"); }, 1500);
      };
      var url = btn.getAttribute("data-copy-link") || location.href;
      if (navigator.clipboard) navigator.clipboard.writeText(url).then(done, done);
      else done();
    });
  });

  /* ---------- Single post: reading progress + TOC ---------- */
  var article = $("[data-article]");
  var bar = $(".progress span");
  if (article && bar) {
    var updateProgress = function () {
      var rect = article.getBoundingClientRect();
      var total = article.offsetHeight - window.innerHeight;
      var done = Math.min(Math.max(-rect.top, 0), Math.max(total, 1));
      bar.style.width = (total > 0 ? (done / total) * 100 : 100) + "%";
    };
    window.addEventListener("scroll", updateProgress, { passive: true });
    window.addEventListener("resize", updateProgress);
    updateProgress();
  }

  // فهرست مطالب داخل مقاله (توسط PHP ساخته می‌شود): در موبایل بسته شروع شود تا صفحه کوتاه‌تر باشد.
  $$(".toc-box").forEach(function (box) {
    if (window.matchMedia("(max-width: 640px)").matches && $$("li", box).length > 6) box.open = false;
  });
})();
