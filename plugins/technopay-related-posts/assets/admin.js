/* TechnoPay Smart Related Posts — رابط ویرایشگر و صفحه تنظیمات (بدون وابستگی). */
(function () {
  'use strict';

  var cfg = window.TPRP || {};
  var t = cfg.i18n || {};
  var DIGITS = '۰۱۲۳۴۵۶۷۸۹';

  function fa(n) {
    return String(n).replace(/\d/g, function (d) { return DIGITS.charAt(d); });
  }

  function format(str, args) {
    var i = 0;
    return str.replace(/%(\d+)\$s|%s/g, function (m, pos) { return args[pos ? pos - 1 : i++]; });
  }

  function el(tag, attrs, children) {
    var node = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (k) {
      var v = attrs[k];
      if (k === 'class') node.className = v;
      else if (k === 'text') node.textContent = v;
      else if (k.slice(0, 2) === 'on' && typeof v === 'function') node.addEventListener(k.slice(2), v);
      else if (v !== false && v !== null && v !== undefined) node.setAttribute(k, v === true ? '' : v);
    });
    (children || []).forEach(function (c) {
      if (c) node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
    });
    return node;
  }

  function api(url, body) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
      body: JSON.stringify(body)
    }).then(function (res) {
      return res.json().then(function (data) {
        if (!res.ok) throw new Error((data && data.message) || res.statusText);
        return data;
      });
    });
  }

  /* متن فعلی ویرایشگر (ذخیره‌نشده) در صورت وجود؛ وگرنه null و سرور از نسخه ذخیره‌شده استفاده می‌کند. */
  function getEditorContent() {
    try {
      if (window.wp && wp.data && wp.data.select) {
        var ed = wp.data.select('core/editor');
        if (ed && ed.getEditedPostContent) return ed.getEditedPostContent();
      }
    } catch (e) { /* ignore */ }
    try {
      if (window.tinymce && tinymce.get('content') && !tinymce.get('content').isHidden()) return tinymce.get('content').getContent();
    } catch (e2) { /* ignore */ }
    var ta = document.getElementById('content');
    return ta ? ta.value : null;
  }

  /* ---------------------------------------------------------------------- */
  /* جعبه ویرایشگر نوشته                                                    */
  /* ---------------------------------------------------------------------- */
  function initPost(root) {
    var postId = parseInt(root.getAttribute('data-post-id'), 10);
    var state = { paragraphs: [], stale: [], disabled: false, placement: 'section_end', hasSaved: false };

    var status = el('p', { 'class': 'tprp-status', role: 'status', 'aria-live': 'polite' });
    var list = el('div', { 'class': 'tprp-list' });
    var staleBox = el('div', { 'class': 'tprp-stale' });
    var disableInput = el('input', { type: 'checkbox', id: 'tprp-disable' });
    var btnAnalyze = el('button', { type: 'button', 'class': 'button button-primary', text: t.analyze });
    var btnTop = el('button', { type: 'button', 'class': 'button', text: t.selectTop });
    var btnClear = el('button', { type: 'button', 'class': 'button', text: t.clearAll });
    var btnSave = el('button', { type: 'button', 'class': 'button button-primary', text: t.save });
    var actions = el('div', { 'class': 'tprp-actions' }, [btnTop, btnClear, btnSave]);
    actions.hidden = true;

    root.textContent = '';
    root.appendChild(el('div', { 'class': 'tprp-toolbar' }, [btnAnalyze]));
    root.appendChild(status);
    root.appendChild(actions);
    root.appendChild(list);
    root.appendChild(staleBox);
    root.appendChild(el('label', { 'class': 'tprp-disable', 'for': 'tprp-disable' }, [disableInput, ' ' + t.disable]));

    function setStatus(text, isError) {
      status.textContent = text || '';
      status.className = 'tprp-status' + (isError ? ' is-error' : '');
    }

    function candidateIndexById(p, id) {
      for (var i = 0; i < p.candidates.length; i++) if (p.candidates[i].id === id) return i;
      return -1;
    }

    function load(data) {
      state.disabled = !!data.disabled;
      state.placement = data.placement || 'section_end';
      state.stale = data.stale || [];
      state.hasSaved = data.paragraphs.some(function (p) { return !!p.approved; }) || state.stale.length > 0;
      disableInput.checked = state.disabled;

      state.paragraphs = data.paragraphs.map(function (p) {
        var chosen = p.recommended;
        var approved = false;
        var placement = state.placement;
        if (p.approved) {
          approved = true;
          placement = p.approved.placement;
          chosen = candidateIndexById(p, p.approved.target);
          if (chosen < 0) {
            p.candidates.push({ id: p.approved.target, title: p.approved.info.title, url: p.approved.info.url, thumb: p.approved.info.thumb, score: 0, keywords: [], valid: p.approved.info.valid, saved: true });
            chosen = p.candidates.length - 1;
          }
        } else if (!state.hasSaved && p.recommended >= 0) {
          approved = true; // اولین بار: پیشنهاد اصلی هر بخش از پیش علامت خورده (تا ذخیره نشود اثری ندارد).
        }
        return { data: p, chosen: chosen, approved: approved && chosen >= 0, placement: placement };
      });

      var withSuggestions = data.paragraphs.length;
      setStatus(format(t.summary, [fa(data.total), fa(withSuggestions), fa(data.corpus)]) + ' — ' + (data.source === 'editor' ? t.fromEditor : t.fromSaved));
      render();
    }

    function sectionLabel(n) {
      return n === 0 ? t.intro : t.section + ' ' + fa(n);
    }

    function render() {
      list.textContent = '';
      actions.hidden = false;

      if (!state.paragraphs.length) {
        list.appendChild(el('p', { 'class': 'tprp-empty', text: t.noSuggestions }));
      }

      state.paragraphs.forEach(function (item, idx) {
        var p = item.data;
        var groupName = 'tprp-c-' + idx;
        var cb = el('input', { type: 'checkbox', checked: item.approved });
        cb.checked = item.approved;
        cb.addEventListener('change', function () {
          item.approved = cb.checked;
          if (cb.checked && item.chosen < 0 && p.candidates.length) { item.chosen = 0; render(); }
          wrap.classList.toggle('is-approved', item.approved);
        });

        var cands = p.candidates.map(function (c, ci) {
          var radio = el('input', { type: 'radio', name: groupName, value: String(ci) });
          radio.checked = item.chosen === ci;
          radio.addEventListener('change', function () {
            item.chosen = ci;
            item.approved = true;
            cb.checked = true;
            wrap.classList.add('is-approved');
          });
          var thumb = c.thumb ? el('img', { src: c.thumb, alt: '', 'class': 'tprp-cand__thumb', loading: 'lazy' }) : el('span', { 'class': 'tprp-cand__thumb tprp-cand__thumb--empty', 'aria-hidden': 'true' });
          var meta = [];
          if (c.score) meta.push(el('span', { 'class': 'tprp-cand__score', text: t.match + ' ' + fa(c.score) + '٪' }));
          if (c.valid === false) meta.push(el('span', { 'class': 'tprp-cand__warn', text: t.unpublished }));
          return el('label', { 'class': 'tprp-cand' }, [
            radio, thumb,
            el('span', { 'class': 'tprp-cand__body' }, [
              el('a', { 'class': 'tprp-cand__title', href: c.url, target: '_blank', rel: 'noopener', text: c.title }),
              el('span', { 'class': 'tprp-cand__meta' }, meta),
              c.keywords && c.keywords.length ? el('span', { 'class': 'tprp-cand__kw', text: t.keywords + ' ' + c.keywords.join('، ') }) : null
            ])
          ]);
        });

        var none = el('label', { 'class': 'tprp-cand tprp-cand--none' }, [
          (function () {
            var r = el('input', { type: 'radio', name: groupName, value: '-1' });
            r.checked = item.chosen < 0;
            r.addEventListener('change', function () { item.chosen = -1; item.approved = false; cb.checked = false; wrap.classList.remove('is-approved'); });
            return r;
          })(),
          el('span', { 'class': 'tprp-cand__body', text: t.none })
        ]);

        var placementSel = el('select', {}, [
          el('option', { value: 'section_end', text: t.sectionEnd }),
          el('option', { value: 'after_paragraph', text: t.afterPara })
        ]);
        placementSel.value = item.placement;
        placementSel.addEventListener('change', function () { item.placement = placementSel.value; });

        var wrap = el('section', { 'class': 'tprp-item' + (item.approved ? ' is-approved' : '') }, [
          el('header', { 'class': 'tprp-item__head' }, [
            el('label', { 'class': 'tprp-item__approve' }, [cb, ' ' + t.approve]),
            el('span', { 'class': 'tprp-item__section', text: sectionLabel(p.section) })
          ]),
          el('blockquote', { 'class': 'tprp-item__excerpt', text: p.excerpt }),
          el('div', { 'class': 'tprp-cands' }, cands.concat([none])),
          el('div', { 'class': 'tprp-item__foot' }, [el('label', {}, [t.placement + ' ', placementSel])])
        ]);
        list.appendChild(wrap);
      });

      staleBox.textContent = '';
      if (state.stale.length) {
        staleBox.appendChild(el('p', { 'class': 'tprp-stale__title', text: t.stale }));
        var ul = el('ul');
        state.stale.forEach(function (s) {
          ul.appendChild(el('li', { text: (s.info ? s.info.title : '#' + s.target) + (s.excerpt ? ' — «' + s.excerpt + '»' : '') }));
        });
        staleBox.appendChild(ul);
      }
    }

    btnAnalyze.addEventListener('click', function () {
      btnAnalyze.disabled = true;
      setStatus(t.analyzing);
      api(cfg.endpoints.analyze, { post_id: postId, content: getEditorContent() || '' })
        .then(load)
        .catch(function (err) { setStatus(t.error + ' ' + err.message, true); })
        .then(function () { btnAnalyze.disabled = false; });
    });

    btnTop.addEventListener('click', function () {
      state.paragraphs.forEach(function (item) {
        var rec = item.data.recommended;
        item.approved = rec >= 0;
        item.chosen = rec >= 0 ? rec : item.chosen;
      });
      render();
    });

    btnClear.addEventListener('click', function () {
      state.paragraphs.forEach(function (item) { item.approved = false; });
      render();
    });

    btnSave.addEventListener('click', function () {
      var items = [];
      state.paragraphs.forEach(function (item) {
        if (!item.approved || item.chosen < 0) return;
        var c = item.data.candidates[item.chosen];
        items.push({ hash: item.data.hash, occurrence: item.data.occurrence, target: c.id, placement: item.placement, excerpt: item.data.excerpt });
      });
      btnSave.disabled = true;
      setStatus(t.saving);
      api(cfg.endpoints.approve, { post_id: postId, items: items, disabled: disableInput.checked })
        .then(function (res) {
          state.stale = [];
          staleBox.textContent = '';
          setStatus((res.saved ? t.saved + ' (' + fa(res.saved) + ')' : t.savedNone));
          if (res.permalink) {
            status.appendChild(document.createTextNode(' '));
            status.appendChild(el('a', { href: res.preview || res.permalink, target: '_blank', rel: 'noopener', text: t.preview }));
          }
        })
        .catch(function (err) { setStatus(t.error + ' ' + err.message, true); })
        .then(function () { btnSave.disabled = false; });
    });

    // وضعیت ذخیره‌شده فعلی را بدون تحلیل مجدد نشان بده.
    disableInput.addEventListener('change', function () { state.disabled = disableInput.checked; });
  }

  /* ---------------------------------------------------------------------- */
  /* صفحه تنظیمات: بازسازی ایندکس                                            */
  /* ---------------------------------------------------------------------- */
  function initSettings(btn) {
    var out = document.getElementById('tprp-reindex-status');
    btn.addEventListener('click', function () {
      btn.disabled = true;
      var offset = 0;
      (function step() {
        out.textContent = t.working;
        api(cfg.endpoints.reindex, { offset: offset })
          .then(function (r) {
            out.textContent = fa(r.done) + ' / ' + fa(r.total);
            if (r.finished) {
              out.textContent += ' — ' + t.done;
              btn.disabled = false;
            } else {
              offset = r.done;
              step();
            }
          })
          .catch(function (err) { out.textContent = t.error + ' ' + err.message; btn.disabled = false; });
      })();
    });
  }

  function boot() {
    var app = document.getElementById('tprp-app');
    if (app) initPost(app);
    var re = document.querySelector('[data-tprp-reindex]');
    if (re) initSettings(re);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
