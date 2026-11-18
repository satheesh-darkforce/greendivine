/**
 * Green Divine Admin — shared behaviour for every module screen.
 * Everything here is progressive enhancement: every form works with JS off
 * (reordering is plain POST buttons, not drag-and-drop; repeatable rows
 * render at least one row server-side). This file only makes the common
 * case a little less tedious to use.
 */
(function () {
  'use strict';

  /* ---------------------------------------------------------------------
   * Mobile sidebar drawer
   * ------------------------------------------------------------------- */
  var toggle   = document.querySelector('[data-gd-sidebar-toggle]');
  var sidebar  = document.querySelector('.gd-admin-sidebar');
  var backdrop = document.querySelector('.gd-admin-sidebar-backdrop');

  function closeSidebar() {
    if (sidebar) sidebar.classList.remove('is-open');
    if (backdrop) backdrop.classList.remove('is-open');
  }
  if (toggle && sidebar) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('is-open');
      if (backdrop) backdrop.classList.toggle('is-open');
    });
  }
  if (backdrop) backdrop.addEventListener('click', closeSidebar);

  /* ---------------------------------------------------------------------
   * Flash banners — auto-dismiss after a few seconds
   * ------------------------------------------------------------------- */
  document.querySelectorAll('.gd-admin-flash').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .4s ease';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 450);
    }, 5000);
  });

  /* ---------------------------------------------------------------------
   * Slug auto-suggest: any input[data-gd-slug-source] fills the field
   * named by its data-gd-slug-target selector, but only until the admin
   * types into the slug field themselves (tracked via a "touched" flag).
   * ------------------------------------------------------------------- */
  document.querySelectorAll('[data-gd-slug-source]').forEach(function (source) {
    var target = document.querySelector(source.getAttribute('data-gd-slug-source'));
    if (!target) return;
    var touched = target.value.trim() !== '';
    target.addEventListener('input', function () { touched = true; });
    source.addEventListener('input', function () {
      if (touched) return;
      target.value = source.value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    });
  });

  /* ---------------------------------------------------------------------
   * Generic repeatable rows: a container [data-gd-repeat] holding rows,
   * an <template data-gd-repeat-template> with the blank-row markup, and
   * a button [data-gd-repeat-add] that appends a clone. Every row gets a
   * [data-gd-repeat-remove] button wired to remove just that row.
   * ------------------------------------------------------------------- */
  document.querySelectorAll('[data-gd-repeat]').forEach(function (container) {
    var list = container.querySelector('[data-gd-repeat-list]');
    var tpl  = container.querySelector('template[data-gd-repeat-template]');
    var addBtn = container.querySelector('[data-gd-repeat-add]');

    function wireRemove(row) {
      var btn = row.querySelector('[data-gd-repeat-remove]');
      if (btn) btn.addEventListener('click', function () { row.remove(); });
    }
    if (list) list.querySelectorAll(':scope > .gd-repeat-row').forEach(wireRemove);

    if (addBtn && tpl && list) {
      addBtn.addEventListener('click', function () {
        var frag = tpl.content.cloneNode(true);
        var row = frag.querySelector('.gd-repeat-row');
        list.appendChild(frag);
        if (row) wireRemove(list.lastElementChild);
      });
    }
  });

  /* ---------------------------------------------------------------------
   * Image field live preview (admin/inc/uploads.php: gd_image_field())
   * ------------------------------------------------------------------- */
  document.querySelectorAll('.gd-image-field').forEach(function (field) {
    var preview = field.querySelector('.gd-image-preview');
    var fileInput = field.querySelector('input[type="file"]');
    var urlInput = field.querySelector('input[type="text"][name$="_url"]');
    var existingSelect = field.querySelector('select[name$="_existing"]');

    function setPreview(src) {
      if (!preview || !src) return;
      preview.innerHTML = '<img src="' + src.replace(/"/g, '&quot;') + '" alt="">';
    }
    if (fileInput) {
      fileInput.addEventListener('change', function () {
        var file = fileInput.files && fileInput.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) { setPreview(e.target.result); };
        reader.readAsDataURL(file);
        if (urlInput) urlInput.value = '';
        if (existingSelect) existingSelect.value = '';
      });
    }
    if (urlInput) {
      urlInput.addEventListener('change', function () {
        if (urlInput.value.trim() === '') return;
        setPreview(urlInput.value.trim());
        if (existingSelect) existingSelect.value = '';
      });
    }
  });

  /* ---------------------------------------------------------------------
   * Unsaved-changes warning — only on forms with real fields to lose (skips
   * the tiny one-click move/delete forms on list screens), and only warns
   * on navigating away, never on the form's own legitimate submit.
   * ------------------------------------------------------------------- */
  document.querySelectorAll('form[method="post"]').forEach(function (form) {
    var hasFields = form.querySelector(
      'input[type="text"], input[type="email"], input[type="number"], input[type="password"], ' +
      'input[type="url"], input[type="date"], input[type="tel"], input[type="checkbox"], ' +
      'textarea, select, input[type="file"]'
    );
    if (!hasFields) return;

    var dirty = false;
    var submitting = false;
    form.addEventListener('input', function () { dirty = true; });
    form.addEventListener('change', function () { dirty = true; });
    form.addEventListener('submit', function () { submitting = true; });

    window.addEventListener('beforeunload', function (e) {
      if (!dirty || submitting) return;
      e.preventDefault();
      e.returnValue = '';
    });
  });

  /* ---------------------------------------------------------------------
   * Confirm destructive actions (delete buttons carry data-gd-confirm="...")
   * ------------------------------------------------------------------- */
  document.querySelectorAll('[data-gd-confirm]').forEach(function (el) {
    el.addEventListener('submit', function (e) {
      if (!window.confirm(el.getAttribute('data-gd-confirm'))) e.preventDefault();
    });
  });

  /* ---------------------------------------------------------------------
   * Rich text toolbar for the handful of fields the public site renders
   * as raw HTML (product description, blog body — see each template's
   * "trusted HTML from $DATA" comment). Progressive enhancement: the
   * plain textarea underneath is what actually submits, so a form still
   * works exactly as before with JS off — this only adds a nicer way to
   * fill it in. Uses execCommand: still universally supported, and a
   * hand-rolled Selection/Range implementation would be a lot more code
   * for the same "bold/italic/heading/list/link" feature set.
   * ------------------------------------------------------------------- */
  /* One shared "Insert link" dialog for every rich-text field. Besides the
   * URL it sets the two SEO choices per link: do-follow vs nofollow (passes
   * ranking value or not) and same tab vs new tab. Clicking Link while the
   * cursor is inside an existing link edits it instead. */
  var linkDlg = null;
  function getLinkDialog() {
    if (linkDlg) return linkDlg;
    linkDlg = document.createElement('dialog');
    linkDlg.className = 'gd-link-dialog';
    linkDlg.innerHTML =
      '<form method="dialog">' +
      '  <h3 class="h6 mb-3"><i class="bi bi-link-45deg"></i> <span data-ld-title>Insert link</span></h3>' +
      '  <label class="form-label small mb-1">Link URL</label>' +
      '  <input type="text" class="form-control form-control-sm mb-2" data-ld-url placeholder="/artificial-vertical-garden or https://...">' +
      '  <div class="form-hint mb-2">Our own pages: start with / (e.g. /products/artificial-grass). Other sites: full https:// address.</div>' +
      '  <label class="form-label small mb-1">Link text</label>' +
      '  <input type="text" class="form-control form-control-sm mb-3" data-ld-text placeholder="Words people click on">' +
      '  <div class="gd-link-dialog-opts">' +
      '    <label class="form-check"><input class="form-check-input" type="radio" name="ld-follow" value="follow" data-ld-follow checked>' +
      '      <span class="form-check-label"><strong>Do-follow</strong> <span class="text-muted small">— passes SEO value (use for our pages &amp; trusted sources)</span></span></label>' +
      '    <label class="form-check"><input class="form-check-input" type="radio" name="ld-follow" value="nofollow" data-ld-nofollow>' +
      '      <span class="form-check-label"><strong>Nofollow</strong> <span class="text-muted small">— no SEO value (sponsored, user or untrusted links)</span></span></label>' +
      '    <label class="form-check mt-2"><input class="form-check-input" type="checkbox" data-ld-newtab>' +
      '      <span class="form-check-label">Open in a new tab</span></label>' +
      '  </div>' +
      '  <div class="d-flex gap-2 mt-3">' +
      '    <button type="button" class="btn btn-sm btn-outline-danger me-auto" data-ld-remove>Remove link</button>' +
      '    <button type="button" class="btn btn-sm btn-outline-secondary" data-ld-cancel>Cancel</button>' +
      '    <button type="submit" class="btn btn-sm btn-primary" data-ld-save>Save link</button>' +
      '  </div>' +
      '</form>';
    document.body.appendChild(linkDlg);
    linkDlg.querySelector('[data-ld-cancel]').addEventListener('click', function () { linkDlg.close(); });
    // External URLs default to a new tab; our own pages to the same tab.
    linkDlg.querySelector('[data-ld-url]').addEventListener('input', function () {
      if (linkDlg.dataset.editing !== '1') {
        linkDlg.querySelector('[data-ld-newtab]').checked = /^https?:\/\//i.test(this.value.trim());
      }
    });
    return linkDlg;
  }

  document.querySelectorAll('textarea[data-gd-richtext]').forEach(function (textarea) {
    var wrap = document.createElement('div');
    wrap.className = 'gd-richtext';

    var toolbar = document.createElement('div');
    toolbar.className = 'gd-richtext-toolbar';
    var buttons = [
      ['bold', '<i class="bi bi-type-bold"></i>', 'Bold'],
      ['italic', '<i class="bi bi-type-italic"></i>', 'Italic'],
      ['formatBlock:H2', '<i class="bi bi-type-h2"></i>', 'Section heading (listed in "In this article")'],
      ['formatBlock:H3', '<i class="bi bi-type-h3"></i>', 'Sub-heading'],
      ['formatBlock:H4', '<i class="bi bi-type-h4"></i>', 'Small heading'],
      ['formatBlock:P', '<i class="bi bi-paragraph"></i>', 'Paragraph'],
      ['insertUnorderedList', '<i class="bi bi-list-ul"></i>', 'Bullet list'],
      ['insertOrderedList', '<i class="bi bi-list-ol"></i>', 'Numbered list'],
      ['link', '<i class="bi bi-link-45deg"></i>', 'Insert / edit link (follow, nofollow, new tab)'],
      ['unlink', '<i class="bi bi-link-45deg"></i><i class="bi bi-x gd-richtext-x"></i>', 'Remove link'],
      ['removeFormat', '<i class="bi bi-eraser"></i>', 'Clear formatting'],
      ['source', '<i class="bi bi-code-slash"></i>', 'Edit HTML'],
    ];

    var editable = document.createElement('div');
    editable.className = 'gd-richtext-editable';
    editable.contentEditable = 'true';
    editable.innerHTML = textarea.value;

    var sourceMode = false;
    function sync() { if (!sourceMode) textarea.value = editable.innerHTML; }

    // The selection is lost when focus moves into the dialog, so remember it.
    var savedRange = null;
    function saveRange() {
      var sel = window.getSelection();
      savedRange = (sel.rangeCount && editable.contains(sel.anchorNode)) ? sel.getRangeAt(0).cloneRange() : null;
    }
    function restoreRange() {
      editable.focus();
      if (!savedRange) return;
      var sel = window.getSelection();
      sel.removeAllRanges(); sel.addRange(savedRange);
    }
    function currentLink() {
      var node = savedRange ? savedRange.commonAncestorContainer : null;
      while (node && node !== editable) {
        if (node.nodeType === 1 && node.tagName === 'A') return node;
        node = node.parentNode;
      }
      return null;
    }
    function applyLinkAttrs(a, url, follow, newTab) {
      a.setAttribute('href', url);
      var rel = [];
      if (!follow) rel.push('nofollow');
      if (newTab) { a.setAttribute('target', '_blank'); rel.push('noopener'); }
      else a.removeAttribute('target');
      if (rel.length) a.setAttribute('rel', rel.join(' ')); else a.removeAttribute('rel');
    }

    function openLinkDialog() {
      saveRange();
      var dlg = getLinkDialog();
      var existing = currentLink();
      var selText = savedRange ? savedRange.toString() : '';
      dlg.dataset.editing = existing ? '1' : '0';
      dlg.querySelector('[data-ld-title]').textContent = existing ? 'Edit link' : 'Insert link';
      dlg.querySelector('[data-ld-url]').value = existing ? existing.getAttribute('href') : '';
      dlg.querySelector('[data-ld-text]').value = existing ? existing.textContent : selText;
      var rel = existing ? (existing.getAttribute('rel') || '') : '';
      dlg.querySelector(/\bnofollow\b/.test(rel) ? '[data-ld-nofollow]' : '[data-ld-follow]').checked = true;
      dlg.querySelector('[data-ld-newtab]').checked = existing ? existing.getAttribute('target') === '_blank' : false;
      dlg.querySelector('[data-ld-remove]').style.display = existing ? '' : 'none';

      dlg.querySelector('[data-ld-remove]').onclick = function () {
        if (existing) existing.replaceWith(document.createTextNode(existing.textContent));
        dlg.close(); sync();
      };
      dlg.querySelector('form').onsubmit = function () {
        var url = dlg.querySelector('[data-ld-url]').value.trim();
        var text = dlg.querySelector('[data-ld-text]').value.trim();
        var follow = dlg.querySelector('[data-ld-follow]').checked;
        var newTab = dlg.querySelector('[data-ld-newtab]').checked;
        if (!url) return;
        if (!/^(https?:\/\/|\/|#|mailto:|tel:)/i.test(url)) url = 'https://' + url;
        if (existing) {
          applyLinkAttrs(existing, url, follow, newTab);
          if (text && text !== existing.textContent) existing.textContent = text;
        } else {
          restoreRange();
          var a = document.createElement('a');
          applyLinkAttrs(a, url, follow, newTab);
          a.textContent = text || selText || url;
          if (savedRange) { savedRange.deleteContents(); savedRange.insertNode(a); }
          else editable.appendChild(a);
          // put the caret just after the new link
          var sel = window.getSelection(), r = document.createRange();
          r.setStartAfter(a); r.collapse(true); sel.removeAllRanges(); sel.addRange(r);
        }
        sync();
      };
      dlg.showModal();
      dlg.querySelector('[data-ld-url]').focus();
    }

    var sourceBtn = null;
    buttons.forEach(function (b) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'gd-richtext-btn';
      btn.innerHTML = b[1];
      btn.title = b[2];
      btn.setAttribute('aria-label', b[2]);
      if (b[0] === 'source') sourceBtn = btn;
      btn.addEventListener('mousedown', function (e) { e.preventDefault(); }); // keep the selection
      btn.addEventListener('click', function () {
        if (b[0] === 'source') {
          // Toggle between the visual editor and the raw HTML textarea.
          sourceMode = !sourceMode;
          if (sourceMode) {
            textarea.value = editable.innerHTML;
            textarea.style.display = ''; editable.style.display = 'none';
            textarea.classList.add('gd-richtext-html');
          } else {
            editable.innerHTML = textarea.value;
            textarea.style.display = 'none'; editable.style.display = '';
          }
          btn.classList.toggle('active', sourceMode);
          toolbar.querySelectorAll('.gd-richtext-btn').forEach(function (o) { if (o !== btn) o.disabled = sourceMode; });
          return;
        }
        if (sourceMode) return;
        editable.focus();
        if (b[0] === 'link') { openLinkDialog(); return; }
        if (b[0].indexOf('formatBlock:') === 0) {
          document.execCommand('formatBlock', false, b[0].split(':')[1]);
        } else {
          document.execCommand(b[0], false, null);
        }
        sync();
      });
      toolbar.appendChild(btn);
    });

    editable.addEventListener('input', sync);
    editable.addEventListener('blur', sync);
    // Clicking a link inside the editor opens it for editing.
    editable.addEventListener('dblclick', function (e) {
      if (e.target.closest && e.target.closest('a')) openLinkDialog();
    });

    wrap.appendChild(toolbar);
    wrap.appendChild(editable);
    textarea.insertAdjacentElement('afterend', wrap);
    textarea.classList.add('gd-richtext-source');
    // The HTML view reuses the real textarea, so move it inside the wrapper.
    wrap.appendChild(textarea);

    // Keep the textarea in the DOM (it's what submits) but visually hidden —
    // a JS failure after this point still leaves a working plain textarea,
    // since nothing here removes it.
    textarea.style.display = 'none';

    var form = textarea.closest('form');
    if (form) form.addEventListener('submit', function () {
      if (sourceMode) editable.innerHTML = textarea.value; // HTML view is the source of truth
      else textarea.value = editable.innerHTML;
    });
  });
})();
