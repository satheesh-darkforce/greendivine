<?php
/**
 * Phone-only "Get a Quote" popup — centered modal dialog.
 * Renders ONLY when $DATA['quote_popup']['enabled'] === true.
 *
 * Appears once per visitor: after a delay (data-delay, seconds) it fades
 * in centered over a dimmed scrim. Closing it (X, backdrop click, Escape,
 * or a successful submit) marks it seen in localStorage so it never
 * shows again on any later visit. Submissions post to the shared Google
 * Sheet endpoint ($DATA['config']['google_sheet_endpoint']), tagged
 * "quote-bar".
 */
$qp = $DATA['quote_popup'] ?? [];
if (empty($qp['enabled'])) return;
$site = $DATA['site'];
?>
<div class="gd-quote-modal" data-gd-quote-modal data-delay="<?= (int)($qp['delay'] ?? 10) ?>" aria-hidden="true">
  <div class="gd-quote-modal-backdrop" data-gd-quote-modal-close></div>

  <div class="gd-quote-modal-dialog" role="dialog" aria-modal="true" aria-label="<?= e($qp['title'] ?? 'Get a Free Quote') ?>">
    <button type="button" class="gd-quote-modal-x" data-gd-quote-modal-close aria-label="Close">
      <svg viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
        <path d="M3 3 L13 13 M13 3 L3 13"/>
      </svg>
    </button>

    <div class="gd-quote-modal-ico"><i class="bi bi-telephone-outbound-fill"></i></div>

    <span class="gd-quote-modal-eyebrow"><?= e($qp['eyebrow'] ?? 'Free Site Visit') ?></span>
    <h3 class="gd-quote-modal-title"><?= e($qp['title'] ?? 'Get a Free Quote') ?></h3>
    <p class="gd-quote-modal-sub"><?= e($qp['sub'] ?? '') ?></p>

    <form class="gd-quote-modal-form" data-gd-form
          data-gs-endpoint="<?= e($DATA['config']['google_sheet_endpoint']) ?>"
          data-source="quote-bar"
          data-fallback-email="<?= e($site['email']) ?>"
          data-thankyou="Thanks! We'll call you back within 24 hours with your free quote."
          novalidate>
      <input class="form-control gd-quote-modal-input" type="tel" name="phone" inputmode="tel"
             placeholder="+91 98450 12345" autocomplete="tel" required>
      <button class="btn btn-gd-gold w-100" type="submit">
        <?= e($qp['cta'] ?? 'Request a Callback') ?>
      </button>
    </form>
    <p class="gd-quote-modal-fine"><i class="bi bi-shield-check"></i> No spam — we call once, that's it.</p>
  </div>
</div>
