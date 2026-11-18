<?php
/**
 * "Get Quote" modal form — opened by the hero's secondary button on landing
 * pages that set 'hero_quote_form' => true (data/landing/<slug>.php; editable
 * in admin). Uses the site's shared [data-gd-form] handling in main.js:
 * validation, Google Sheet submit, thank-you panel, mailto fallback.
 *
 * Fields (all required): name, phone, location, need (this landing page's
 * use cases). Sheet columns: name, phone, location, interest; source is
 * "quote-form-<slug>".
 *
 * Sheet: the landing page's own 'quote_form_endpoint' (its own Google Sheet,
 * e.g. a sports-only sheet) when set, otherwise the site-wide
 * $DATA['config']['google_sheet_endpoint'].
 *
 * Expects: $landing, $category, $DATA (global).
 */
$needs    = array_values(array_filter(array_column($landing['use_cases'] ?? [], 'title')));
$siteInfo = $DATA['site'];
$endpoint = trim((string)($landing['quote_form_endpoint'] ?? '')) ?: (string)($DATA['config']['google_sheet_endpoint'] ?? '');
?>
<div class="modal fade gd-qmodal" id="gd-quote-modal" tabindex="-1" aria-labelledby="gd-qmodal-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <span class="gd-eyebrow"><?= e($landing['eyebrow'] ?? $category['name']) ?></span>
          <h2 class="modal-title gd-qmodal-title" id="gd-qmodal-title">Get a <span>free quote</span></h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p class="gd-qmodal-lede">We'll call you back with a written quote — usually within a few hours.</p>

        <form data-gd-form
              data-gs-endpoint="<?= e($endpoint) ?>"
              data-source="quote-form-<?= e($category['slug']) ?>"
              data-fallback-email="<?= e($siteInfo['email']) ?>"
              data-thankyou="Thanks! We've got your details — our team will call you shortly with your quote."
              novalidate>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label" for="q-name">Your name</label>
              <input class="form-control" id="q-name" name="name" type="text"
                     placeholder="e.g. Rahul Kumar" autocomplete="name" required>
            </div>
            <div class="col-6">
              <label class="form-label" for="q-phone">Phone</label>
              <input class="form-control" id="q-phone" name="phone" type="tel"
                     placeholder="e.g. 98450 12345" autocomplete="tel" inputmode="tel" required>
            </div>

            <div class="col-6">
              <label class="form-label" for="q-location">Location</label>
              <input class="form-control" id="q-location" name="location" type="text"
                     placeholder="e.g. Whitefield" autocomplete="address-level2" required>
            </div>
            <div class="col-6">
              <label class="form-label" for="q-need">What do you need?</label>
              <select class="form-select" id="q-need" name="interest" required>
                <option value="" selected disabled>Choose one</option>
                <?php foreach ($needs as $n): ?>
                  <option value="<?= e($n) ?>"><?= e($n) ?></option>
                <?php endforeach; ?>
                <option value="Something else">Something else</option>
              </select>
            </div>

            <div class="col-12">
              <button class="btn btn-gd-gold w-100 gd-qmodal-submit" type="submit">
                Get My Quote <i class="bi bi-send ms-1"></i>
              </button>
              <p class="small text-danger text-center mt-2 mb-0 d-none" data-gd-error>
                <i class="bi bi-exclamation-triangle-fill"></i> Couldn't send — please call
                <a href="tel:<?= e($siteInfo['phone']) ?>"><?= e($siteInfo['phone_label']) ?></a> or WhatsApp us.
              </p>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
