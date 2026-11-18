/* =====================================================================
   Green Divine — main.js
   Vanilla JS only. Animations, counters, reveal-on-scroll, marquee dup.
   ===================================================================== */
(function () {
  'use strict';


  /* ---------- deploy guard: is Bootstrap 5 really here? ----------
     Production once served a leftover Bootstrap *4* bootstrap.min.css at the
     same path as our v5 build. Every request returned 200, so nothing looked
     broken server-side, but .offcanvas / .visually-hidden-focusable /
     .form-select don't exist in v4 — the mobile nav rendered inline in the
     header, the skip link sat visible on the page and selects were clipped.

     Vendor files now live under version-stamped folders and
     tools/asset-check.php verifies them on the server, but a browser can
     still be handed a stale copy from its own cache or a CDN edge. So we
     feature-detect here as the last line of defence: shout in the console,
     mark <html> so the problem is greppable in a bug report, and inject just
     enough CSS that visitors see a plain-but-sane page instead of a broken
     one while somebody fixes the upload. */
  (function bootstrapGuard() {
    const bs = window.bootstrap;
    const jsOk = !!(bs && bs.Offcanvas && String(bs.Offcanvas.VERSION || '5').charAt(0) === '5');

    // .offcanvas is position:fixed;visibility:hidden in v5 and absent in v4,
    // so a detached probe tells us whether the v5 stylesheet actually applied.
    const probe = document.createElement('div');
    probe.className = 'offcanvas offcanvas-end';
    probe.setAttribute('aria-hidden', 'true');
    document.body.appendChild(probe);
    const cssOk = getComputedStyle(probe).position === 'fixed';
    probe.remove();

    if (jsOk && cssOk) return;

    const problems = [];
    if (!jsOk)  problems.push('bootstrap.bundle.min.js did not load (or is not v5)');
    if (!cssOk) problems.push('bootstrap.min.css did not load (or is not v5)');

    document.documentElement.setAttribute('data-gd-broken-assets', problems.length);
    console.error(
      '[Green Divine] Bootstrap 5 is not fully loaded:\n  • ' + problems.join('\n  • ') +
      '\nHard-reload, then run tools/asset-check.php on the server and purge the CDN cache.'
    );

    if (cssOk) return;   // JS-only failure needs no cosmetic patch

    // Minimal stand-ins for the v5 rules this page's layout depends on.
    // Deliberately not a Bootstrap shim — just enough to keep the page
    // readable and free of stray UI until the real stylesheet is restored.
    const patch = document.createElement('style');
    patch.id = 'gd-asset-fallback';
    patch.textContent = [
      '.offcanvas:not(.show){position:absolute!important;left:-9999px!important;visibility:hidden!important}',
      '@media(min-width:992px){.navbar-expand-lg .offcanvas{position:static!important;left:auto!important;visibility:visible!important;display:flex!important}',
      '.navbar-expand-lg .offcanvas .offcanvas-header{display:none!important}}',
      '.visually-hidden-focusable:not(:focus):not(:focus-within){position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important}',
      '.form-select{display:block;width:100%;padding:.5rem 2rem .5rem .75rem;line-height:1.5;border:1px solid #ced4da;border-radius:.375rem;background:#fff}',
      '.modal:not(.show){display:none!important}'
    ].join('');
    document.head.appendChild(patch);
  })();
  /* ---------- sticky-header shadow on scroll ---------- */
  const header = document.querySelector('.gd-header');
  if (header) {
    const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 8);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- mobile nav: sync hamburger + aria-expanded with the offcanvas ----------
     Bootstrap's Offcanvas (unlike Collapse) doesn't update the trigger
     button's aria-expanded or give us a hook for the "X" animation, so we
     mirror its show/hide events onto the toggler ourselves. */
  const gdNav = document.getElementById('gdNav');
  const gdNavToggle = document.querySelector('[data-bs-target="#gdNav"]');
  if (gdNav && gdNavToggle) {
    gdNav.addEventListener('show.bs.offcanvas', () => {
      gdNavToggle.setAttribute('aria-expanded', 'true');
      document.body.classList.add('gd-nav-open'); // hides the floating call/WhatsApp buttons so they can't overlap the panel
    });
    gdNav.addEventListener('hidden.bs.offcanvas', () => {
      gdNavToggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('gd-nav-open');
    });
  }

  /* ---------- "How It Works" tabbed stepper ----------
     Without this, .gd-steps-panel has no "display:none" gate (see CSS) so
     every step just renders stacked — content never depends on the script
     actually running. Once it does, add gd-steps-js to arm the tab/panel
     switching, same pattern as gd-js on <html> for the reveal animation. */
  document.querySelectorAll('[data-gd-steps]').forEach((root) => {
    const tabs = root.querySelectorAll('[data-gd-step-tab]');
    const panels = root.querySelectorAll('[data-gd-step-panel]');
    if (!tabs.length || !panels.length) return;
    root.classList.add('gd-steps-js');
    tabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        const idx = tab.dataset.gdStepTab;
        tabs.forEach((t) => {
          const isActive = t === tab;
          t.classList.toggle('active', isActive);
          t.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
        panels.forEach((p) => p.classList.toggle('active', p.dataset.gdStepPanel === idx));
      });
    });
  });

  /* ---------- reveal-on-scroll (AOS-lite) ---------- */
  const reveals = document.querySelectorAll('.gd-reveal');
  if (reveals.length && 'IntersectionObserver' in window) {
    // A ratio threshold alone can never fire for an element taller than the
    // screen (a long blog body can't ever be 12% visible), which left such
    // content invisible. Watch from 0 too, and reveal tall elements as soon
    // as any part of them is on screen.
    const io = new IntersectionObserver((entries) => {
      entries.forEach(en => {
        if (!en.isIntersecting) return;
        const tall = en.boundingClientRect.height > window.innerHeight * 0.6;
        if (tall || en.intersectionRatio >= 0.12) {
          en.target.classList.add('in');
          io.unobserve(en.target);
        }
      });
    }, { threshold: [0, 0.12], rootMargin: '0px 0px -8% 0px' });
    reveals.forEach(el => io.observe(el));
  } else {
    reveals.forEach(el => el.classList.add('in'));
  }

  /* ---------- animated number counters ---------- */
  const counters = document.querySelectorAll('[data-counter]');
  if (counters.length && 'IntersectionObserver' in window) {
    const animate = (el) => {
      const target = parseInt(el.dataset.counter, 10) || 0;
      const dur    = 1600;
      const start  = performance.now();
      const step = (now) => {
        const p = Math.min((now - start) / dur, 1);
        const eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
        el.textContent = Math.floor(eased * target).toLocaleString();
        if (p < 1) requestAnimationFrame(step);
        else el.textContent = target.toLocaleString();
      };
      requestAnimationFrame(step);
    };
    const io = new IntersectionObserver((entries) => {
      entries.forEach(en => {
        if (en.isIntersecting) {
          animate(en.target);
          io.unobserve(en.target);
        }
      });
    }, { threshold: 0.4 });
    counters.forEach(el => io.observe(el));
  }

  /* ---------- marquee: duplicate children for seamless loop ---------- */
  document.querySelectorAll('.gd-marquee-track').forEach((track) => {
    track.innerHTML += track.innerHTML;
  });

  /* ---------- products page filter + search ---------- */
  const filterBar    = document.querySelector('[data-filter-bar]');
  const searchInput  = document.querySelector('[data-product-search]');
  const emptyNotice  = document.querySelector('[data-product-empty]');
  if (filterBar || searchInput) {
    const cards = Array.from(document.querySelectorAll('[data-category]'));

    const applyFilters = () => {
      const activePill = filterBar && filterBar.querySelector('[data-filter].active');
      const cat   = activePill ? activePill.dataset.filter : 'all';
      const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
      let visibleCount = 0;

      cards.forEach((card) => {
        const matchesCategory = cat === 'all' || card.dataset.category === cat;
        const matchesSearch    = !query || card.textContent.toLowerCase().includes(query);
        const show = matchesCategory && matchesSearch;
        card.parentElement.style.display = show ? '' : 'none';
        if (show) visibleCount++;
      });

      if (emptyNotice) emptyNotice.classList.toggle('d-none', visibleCount > 0);
    };

    if (filterBar) {
      filterBar.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-filter]');
        if (!btn) return;
        filterBar.querySelectorAll('[data-filter]').forEach(b => {
          b.classList.remove('active');
          b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
        applyFilters();
        // Mirror the pill in the URL (/products or /products/<slug>) so the
        // filtered view can be shared, reloaded or reached with Back.
        if (/\/products(\/[^/]*)?\/?$/.test(location.pathname)) {
          const slug = btn.dataset.filter;
          const path = location.pathname.replace(/\/products(\/[^/]*)?\/?$/,
            slug === 'all' ? '/products' : '/products/' + encodeURIComponent(slug));
          history.replaceState(null, '', path + location.hash);
        }
        // Keep the active pill in view (matters when it's wrapped in a
        // horizontal scroller on mobile).
        btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
      });

      // On load, scroll the already-active pill into view (e.g. arriving via
      // /products/<slug> where the server marked one pill .active).
      const active = filterBar.querySelector('[data-filter].active');
      if (active) {
        requestAnimationFrame(() => {
          active.scrollIntoView({ behavior: 'auto', inline: 'center', block: 'nearest' });
        });
      }
    }

    if (searchInput) {
      searchInput.addEventListener('input', applyFilters);
    }
  }

  /* ---------- Google Sheets form submission ----------
     Forms tagged with [data-gd-form] POST to a Google Apps Script Web App.
     - Endpoint URL is read from the form's data-gs-endpoint attribute.
     - If the endpoint is empty / fails, fall back to mailto: when
       data-fallback-email is set, otherwise just show a success message.
     - Apps Script setup: see docs/google-sheets-setup.md
  ----------------------------------------------------- */
  const showFor = (el, ms = 5000) => {
    el.classList.remove('d-none');
    setTimeout(() => el.classList.add('d-none'), ms);
  };

  // Sheet timestamp in Indian time, e.g. "28-Oct-2026 03:45:30 PM" — always
  // IST whatever the visitor's own time zone, and readable in the Sheet.
  // Month names are our own: browsers disagree on "Sep" vs "Sept".
  const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  const istTimestamp = (d = new Date()) => {
    const p = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: 'Asia/Kolkata', day: '2-digit', month: 'numeric', year: 'numeric',
      hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23',
    }).formatToParts(d).forEach(({ type, value }) => { p[type] = value; });
    const h24 = +p.hour % 24;
    const h12 = String(h24 % 12 || 12).padStart(2, '0');
    return `${p.day}-${MONTHS[+p.month - 1]}-${p.year} ${h12}:${p.minute}:${p.second} ${h24 < 12 ? 'AM' : 'PM'}`;
  };

  const collectForm = (form) => {
    const data = {};
    new FormData(form).forEach((v, k) => { data[k] = v; });
    data.source    = form.dataset.source || (location.pathname || '/');
    data.timestamp = istTimestamp();
    data.page_url  = location.href;
    return data;
  };

  const postToSheet = async (endpoint, payload) => {
    // Apps Script Web Apps accept text/plain to avoid CORS preflight;
    // the script reads e.postData.contents and JSON.parse() it server-side.
    const res = await fetch(endpoint, {
      method: 'POST',
      mode: 'cors',
      headers: { 'Content-Type': 'text/plain;charset=utf-8' },
      body: JSON.stringify(payload),
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res;
  };

  /* ---------- client-side validation ---------- */
  const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const phoneRe = /^[+()\d][\d\s\-()]{7,17}$/;

  const setInvalid = (field, msg) => {
    field.classList.add('is-invalid');
    field.setAttribute('aria-invalid', 'true');
    let fb = field.parentElement.querySelector('.invalid-feedback[data-gd-fb]');
    if (!fb) {
      fb = document.createElement('div');
      fb.className = 'invalid-feedback';
      fb.setAttribute('data-gd-fb', '');
      field.insertAdjacentElement('afterend', fb);
    }
    fb.textContent = msg;
  };
  const clearInvalid = (field) => {
    field.classList.remove('is-invalid');
    field.removeAttribute('aria-invalid');
    const fb = field.parentElement.querySelector('.invalid-feedback[data-gd-fb]');
    if (fb) fb.textContent = '';
  };
  const validateForm = (form) => {
    let firstBad = null;
    form.querySelectorAll('input, textarea, select').forEach((f) => {
      if (f.type === 'hidden' || f.disabled || f.type === 'submit') return;
      clearInvalid(f);
      const v = (f.value || '').trim();
      let msg = '';
      if (f.hasAttribute('required') && !v) {
        msg = 'Please fill this in.';
      } else if (v && f.type === 'email' && !emailRe.test(v)) {
        msg = 'Please enter a valid email address.';
      } else if (v && f.type === 'tel' && !phoneRe.test(v)) {
        msg = 'Please enter a valid phone number.';
      }
      if (msg) { setInvalid(f, msg); if (!firstBad) firstBad = f; }
    });
    // data-gd-one-of="phone,email": at least one of these fields must be filled.
    if (form.dataset.gdOneOf) {
      const group = form.dataset.gdOneOf.split(',')
        .map((n) => form.querySelector(`[name="${n.trim()}"]`)).filter(Boolean);
      if (group.length && group.every((f) => !(f.value || '').trim())) {
        // highlight every field in the group, but say it once (under the last one)
        group.forEach((f, i) => setInvalid(f, i === group.length - 1 ? 'Please enter your phone number or email.' : ''));
        if (!firstBad) firstBad = group[0];
      }
    }
    if (firstBad) firstBad.focus();
    return !firstBad;
  };

  // Replace a submitted form with a styled "thank you" panel.
  const showThankYou = (form) => {
    const msg = form.getAttribute('data-thankyou') ||
      "We've received your details — our team will reach out within 24 hours. No spam, promised.";
    let panel = form.parentElement.querySelector('[data-gd-thanks]');
    if (!panel) {
      panel = document.createElement('div');
      panel.className = 'gd-form-thanks';
      panel.setAttribute('data-gd-thanks', '');
      panel.innerHTML =
        '<div class="gd-form-thanks-ico"><i class="bi bi-check-lg"></i></div>' +
        '<h3 class="gd-form-thanks-title">Thank you!</h3>' +
        '<p class="gd-form-thanks-msg"></p>';
      panel.querySelector('.gd-form-thanks-msg').textContent = msg;
      form.insertAdjacentElement('afterend', panel);
    }
    form.classList.add('d-none');
    panel.classList.remove('d-none');
    // retrigger entrance
    panel.classList.remove('in');
    requestAnimationFrame(() => panel.classList.add('in'));
  };

  document.querySelectorAll('[data-gd-form]').forEach((form) => {
    // clear a field's error as soon as the user edits it
    form.querySelectorAll('input, textarea, select').forEach((f) => {
      const clear = () => {
        clearInvalid(f);
        // typing in one "phone or email" field clears the error on the other too
        if (form.dataset.gdOneOf && form.dataset.gdOneOf.split(',').map((n) => n.trim()).includes(f.name)) {
          form.dataset.gdOneOf.split(',').forEach((n) => {
            const other = form.querySelector(`[name="${n.trim()}"]`);
            if (other && other !== f) clearInvalid(other);
          });
        }
      };
      f.addEventListener('input', clear);
      f.addEventListener('change', clear);
    });

    // Form inside a Bootstrap modal (hero "Get Quote"): after a successful
    // send, reopening the modal shows a fresh form instead of the thank-you.
    const bsModal = form.closest('.modal');
    if (bsModal) {
      bsModal.addEventListener('hidden.bs.modal', () => {
        const thanks = form.parentElement.querySelector('[data-gd-thanks]');
        if (thanks && !thanks.classList.contains('d-none')) {
          thanks.classList.add('d-none');
          form.classList.remove('d-none');
        }
      });
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      // Validate before doing anything else — bail out if invalid.
      if (!validateForm(form)) return;

      const btn = form.querySelector('button[type="submit"]');
      const ok  = form.querySelector('[data-gd-success]');
      const err = form.querySelector('[data-gd-error]');
      ok && ok.classList.add('d-none');
      err && err.classList.add('d-none');

      if (btn) { btn.disabled = true; btn.dataset._label = btn.innerHTML; btn.innerHTML = 'Sending…'; }

      const endpoint = form.dataset.gsEndpoint || '';
      const payload  = collectForm(form);

      const finish = (success) => {
        if (btn) { btn.disabled = false; btn.innerHTML = btn.dataset._label || 'Send'; }
        if (success) {
          form.reset();
          showThankYou(form);
          // If this form lives inside the quote popup, auto-close it (it's
          // already marked "seen" in localStorage the moment it appeared).
          const modal = form.closest('[data-gd-quote-modal]');
          if (modal) {
            setTimeout(() => {
              modal.classList.remove('show');
              document.body.classList.remove('gd-quote-modal-open');
            }, 3800);
          }
        } else {
          err && showFor(err, 7000);
        }
      };

      // No endpoint configured -> fall back to mailto: if this form has one.
      // Never claim success when nothing was actually sent anywhere — a
      // form with no endpoint AND no fallback can't deliver the lead, and
      // telling the visitor otherwise just hides a real problem.
      if (!endpoint) {
        const mail = form.dataset.fallbackEmail;
        if (!mail) { finish(false); return; }
        const subject = encodeURIComponent('Website enquiry: ' + (payload.product || payload.source));
        const body    = encodeURIComponent(
          Object.entries(payload).map(([k, v]) => `${k}: ${v}`).join('\n')
        );
        window.location.href = `mailto:${mail}?subject=${subject}&body=${body}`;
        finish(true);
        return;
      }

      try {
        await postToSheet(endpoint, payload);
        finish(true);
      } catch (e1) {
        console.warn('Sheet submit failed:', e1);
        finish(false);
      }
    });
  });

  /* ---------- reviews carousel ---------- */
  document.querySelectorAll('[data-gd-reviews]').forEach((root) => {
    const track = root.querySelector('[data-gd-rev-track]');
    const prev  = root.querySelector('[data-gd-rev-prev]');
    const next  = root.querySelector('[data-gd-rev-next]');
    const dots  = root.querySelector('[data-gd-rev-dots]');
    if (!track) return;

    const cards = Array.from(track.children);
    if (!cards.length) return;

    const stepSize = () => {
      const a = cards[0];
      const b = cards[1];
      if (b) return b.getBoundingClientRect().left - a.getBoundingClientRect().left;
      return a.getBoundingClientRect().width;
    };
    const perView = () => {
      const step = stepSize();
      return step > 0 ? Math.max(1, Math.round(track.clientWidth / step)) : 1;
    };
    const pageCount = () => Math.max(1, Math.ceil(cards.length / perView()));
    const currentPage = () => {
      const step = stepSize();
      if (step <= 0) return 0;
      return Math.round(track.scrollLeft / (step * perView()));
    };

    const renderDots = () => {
      if (!dots) return;
      const n = pageCount();
      dots.innerHTML = '';
      if (n <= 1) return;
      for (let i = 0; i < n; i++) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'gd-rev-dot' + (i === currentPage() ? ' active' : '');
        b.setAttribute('aria-label', 'Go to review page ' + (i + 1));
        b.addEventListener('click', () => {
          track.scrollTo({ left: i * stepSize() * perView(), behavior: 'smooth' });
        });
        dots.appendChild(b);
      }
    };

    const updateState = () => {
      const max = track.scrollWidth - track.clientWidth - 1;
      if (prev) prev.disabled = track.scrollLeft <= 1;
      if (next) next.disabled = track.scrollLeft >= max;
      if (dots) {
        const cur = currentPage();
        dots.querySelectorAll('.gd-rev-dot').forEach((d, i) => {
          d.classList.toggle('active', i === cur);
        });
      }
    };

    const scrollByPage = (dir) => {
      const step = stepSize() * perView();
      const max  = track.scrollWidth - track.clientWidth;
      let target = track.scrollLeft + dir * step;
      if (dir > 0 && track.scrollLeft >= max - 2) target = 0;          // loop
      if (dir < 0 && track.scrollLeft <= 2)        target = max;       // loop
      track.scrollTo({ left: target, behavior: 'smooth' });
    };

    prev && prev.addEventListener('click', () => scrollByPage(-1));
    next && next.addEventListener('click', () => scrollByPage(1));
    track.addEventListener('scroll', () => { requestAnimationFrame(updateState); }, { passive: true });
    window.addEventListener('resize', () => { renderDots(); updateState(); });

    renderDots();
    updateState();

    /* autoplay (pause on hover / focus / hidden tab / reduced motion) */
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduceMotion && cards.length > perView()) {
      let timer = null;
      const start = () => { stop(); timer = setInterval(() => scrollByPage(1), 5500); };
      const stop  = () => { if (timer) { clearInterval(timer); timer = null; } };
      root.addEventListener('mouseenter', stop);
      root.addEventListener('mouseleave', start);
      root.addEventListener('focusin', stop);
      root.addEventListener('focusout', start);
      document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
      start();
    }
  });

  /* ---------- close mobile menu on link click ---------- */
  document.querySelectorAll('#gdNav .nav-link').forEach((link) => {
    link.addEventListener('click', () => {
      const nav = document.getElementById('gdNav');
      if (nav && nav.classList.contains('show') && window.bootstrap) {
        window.bootstrap.Offcanvas.getInstance(nav)?.hide();
      }
    });
  });

  /* ---------- quote popup (phone-only lead — centered modal, shown once ever) ---------- */
  document.querySelectorAll('[data-gd-quote-modal]').forEach((modal) => {
    const SEEN = 'gd_quote_modal_seen';
    const delay = (parseInt(modal.dataset.delay, 10) || 10) * 1000;
    const show = () => {
      modal.classList.add('show');
      document.body.classList.add('gd-quote-modal-open');
      // Mark as seen the moment it appears — "shown once" means once
      // ever, not just once dismissed, so a reload mid-display (or one
      // the visitor never interacts with) still won't show it again.
      try { localStorage.setItem(SEEN, '1'); } catch (e) {}
    };
    const dismiss = () => {
      modal.classList.remove('show');
      document.body.classList.remove('gd-quote-modal-open');
    };
    modal.querySelectorAll('[data-gd-quote-modal-close]').forEach(el => el.addEventListener('click', dismiss));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('show')) dismiss();
    });
    let seen = false;
    try { seen = localStorage.getItem(SEEN) === '1'; } catch (e) {}
    if (!seen) setTimeout(show, delay);
  });

  /* ---------- share: copy link to clipboard ---------- */
  document.querySelectorAll('[data-gd-copy]').forEach((btn) => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const url = btn.dataset.gdCopy || location.href;
      try {
        await navigator.clipboard.writeText(url);
      } catch (_) {
        const ta = document.createElement('textarea');
        ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); } catch (e2) {}
        document.body.removeChild(ta);
      }
      const label = btn.querySelector('[data-gd-copy-label]');
      const prev  = label ? label.textContent : null;
      if (label) label.textContent = 'Copied!';
      btn.classList.add('copied');
      setTimeout(() => {
        if (label && prev !== null) label.textContent = prev;
        btn.classList.remove('copied');
      }, 1800);
    });
  });

  /* ---------- install videos: play muted while on screen ----------
     Cards are preload="none", so nothing downloads until one scrolls into
     view. Reduced-motion users (and browsers without IntersectionObserver)
     get native controls and tap-to-play instead of autoplay. */
  const gdVideos = document.querySelectorAll('[data-gd-video]');
  if (gdVideos.length) {
    const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (calm || !('IntersectionObserver' in window)) {
      gdVideos.forEach((v) => { v.controls = true; });
    } else {
      const io = new IntersectionObserver((entries) => {
        entries.forEach(({ target, isIntersecting }) => {
          if (isIntersecting) {
            const p = target.play();
            if (p && p.catch) p.catch(() => { target.controls = true; });
          } else {
            target.pause();
          }
        });
      }, { threshold: 0.5 });
      gdVideos.forEach((v) => io.observe(v));
    }
  }

  /* ---------- YouTube Shorts: load the player only when tapped ----------
     Cards are plain thumbnail links (includes/youtube-shorts.php); each real
     player is ~1 MB of YouTube script, so it's created on demand. Starting
     another Short removes the previous player so only one plays at a time. */
  let gdYtPlaying = null;   // { frame, link } of the Short currently playing
  document.querySelectorAll('[data-gd-yt]').forEach((link) => {
    link.addEventListener('click', (ev) => {
      if (ev.ctrlKey || ev.metaKey || ev.shiftKey) return;   // let "open in new tab" work
      ev.preventDefault();
      if (gdYtPlaying) {   // put the previous thumbnail (and its listener) back
        gdYtPlaying.frame.closest('.gd-yt-card').classList.remove('is-playing');
        gdYtPlaying.frame.replaceWith(gdYtPlaying.link);
      }
      const frame = document.createElement('iframe');
      frame.src = 'https://www.youtube-nocookie.com/embed/' + link.dataset.gdYt + '?autoplay=1&playsinline=1&rel=0';
      frame.title = link.getAttribute('aria-label') || 'YouTube video';
      frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
      frame.allowFullscreen = true;
      link.closest('.gd-yt-card').classList.add('is-playing');
      link.replaceWith(frame);
      gdYtPlaying = { frame, link };
    });
  });

})();
