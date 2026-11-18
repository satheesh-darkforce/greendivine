<?php
/**
 * Floating phone + WhatsApp icons.
 * Visible on every page, bottom-right (bottom-left on RTL screens).
 * Call sits on top and is sized larger — it is the primary action; the
 * DOM order matches the visual order so tabbing reaches it first.
 */
$site = $DATA['site'];
$waMsg = rawurlencode("Hi Green Divine, I'd like to know more about your services.");
$waUrl = 'https://wa.me/' . $site['whatsapp'] . '?text=' . $waMsg;
?>
<div class="gd-floating" aria-hidden="false">
  <a href="tel:<?= e($site['phone']) ?>"
     class="gd-float-btn gd-float-call"
     aria-label="Call <?= e($site['name']) ?>">
    <i class="bi bi-telephone-fill"></i>
  </a>
  <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener"
     class="gd-float-btn gd-float-wa"
     aria-label="Chat with us on WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>
</div>
