<?php
/**
 * Reusable FAQ Accordion — now page-aware.
 *
 * Source selection (highest priority first):
 *   1. $GLOBALS['gd_page_faqs']        — explicit override set by a template
 *   2. $DATA['page_faqs'][$active]     — route-keyed FAQ block (home, products, ...)
 *   3. $DATA['faqs']                   — site-wide fallback
 *
 * Optional overrides (also globals):
 *   $GLOBALS['gd_page_faqs_title']     — section heading suffix
 *   $GLOBALS['gd_page_faqs_eyebrow']   — eyebrow label
 *   $GLOBALS['gd_page_faqs_sub']       — sub-heading line
 *   $GLOBALS['gd_skip_faq']            — set true to skip this section entirely
 */

if (!empty($GLOBALS['gd_skip_faq'])) return;

$active = $active ?? '';

$faqs = $GLOBALS['gd_page_faqs']
     ?? ($DATA['page_faqs'][$active] ?? null)
     ?? $DATA['faqs']
     ?? [];

if (!$faqs) return;

$eyebrow = $GLOBALS['gd_page_faqs_eyebrow'] ?? 'FAQ';
$title   = $GLOBALS['gd_page_faqs_title']   ?? 'Frequently Asked <span>Questions</span>';
$sub     = $GLOBALS['gd_page_faqs_sub']     ?? 'Everything you need to know before going green.';
?>
<section class="gd-faq-section py-5" aria-labelledby="gd-faq-title">
  <div class="container">

    <div class="text-center mb-5">
      <span class="gd-eyebrow"><?= e($eyebrow) ?></span>
      <h2 id="gd-faq-title" class="gd-section-title"><?= $title /* trusted markup */ ?></h2>
      <p class="text-muted mb-0"><?= e($sub) ?></p>
    </div>

    <div class="accordion gd-accordion mx-auto" id="gdFaqAccordion" style="max-width: 880px;">
      <?php foreach ($faqs as $i => $faq): ?>
        <?php $id = 'gdFaq' . $i; ?>
        <div class="accordion-item">
          <h3 class="accordion-header" id="<?= e($id) ?>-h">
            <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>" type="button"
                    data-bs-toggle="collapse" data-bs-target="#<?= e($id) ?>-c"
                    aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="<?= e($id) ?>-c">
              <i class="bi bi-patch-question-fill text-success me-2"></i>
              <?= e($faq['q']) ?>
            </button>
          </h3>
          <div id="<?= e($id) ?>-c" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
               aria-labelledby="<?= e($id) ?>-h" data-bs-parent="#gdFaqAccordion">
            <div class="accordion-body text-muted"><?= e($faq['a']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <p class="text-center text-muted mt-4 small">
      Still curious? <a href="<?= e(gd_url('/contact')) ?>" class="link-success">Get in touch</a>
      &nbsp;or&nbsp;
      <a href="https://wa.me/<?= e($DATA['site']['whatsapp']) ?>" class="link-success" target="_blank" rel="noopener">
        <i class="bi bi-whatsapp"></i> WhatsApp us
      </a>
    </p>

  </div>
</section>
