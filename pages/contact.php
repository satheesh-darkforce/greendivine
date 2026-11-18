<?php /* Contact — editorial layout with working Google-Sheets form */
$site = $DATA['site'];
$contactHero = $DATA['page_content']['contact']['hero'];
?>

<!-- ============================ HERO ============================ -->
<section class="gd-subhero" style="background-image:url('<?= e(gd_img('vg-3.jpg')) ?>')">
  <div class="container">
    <span class="gd-eyebrow"><?= e($contactHero['eyebrow']) ?></span>
    <h1><?= e($contactHero['title']) ?> <span><?= e($contactHero['title_accent']) ?></span></h1>
    <p class="lead opacity-90 mb-0"><?= e($contactHero['sub']) ?></p>
  </div>
</section>

<!-- ===================== QUICK CONTACT CARDS ===================== -->
<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-6 col-lg-3 gd-reveal">
        <a class="gd-contact-tile h-100" href="tel:<?= e($site['phone']) ?>">
          <span class="gd-contact-ico"><i class="bi bi-telephone-fill"></i></span>
          <span class="gd-contact-k">Call us</span>
          <span class="gd-contact-v"><?= e($site['phone_label']) ?></span>
        </a>
      </div>
      <div class="col-6 col-lg-3 gd-reveal">
        <a class="gd-contact-tile h-100" href="https://wa.me/<?= e($site['whatsapp']) ?>" target="_blank" rel="noopener">
          <span class="gd-contact-ico gd-contact-ico-wa"><i class="bi bi-whatsapp"></i></span>
          <span class="gd-contact-k">WhatsApp</span>
          <span class="gd-contact-v">Chat instantly</span>
        </a>
      </div>
      <div class="col-6 col-lg-3 gd-reveal">
        <a class="gd-contact-tile h-100" href="mailto:<?= e($site['email']) ?>">
          <span class="gd-contact-ico"><i class="bi bi-envelope-fill"></i></span>
          <span class="gd-contact-k">Email</span>
          <span class="gd-contact-v"><?= e($site['email']) ?></span>
        </a>
      </div>
      <div class="col-6 col-lg-3 gd-reveal">
        <div class="gd-contact-tile h-100">
          <span class="gd-contact-ico"><i class="bi bi-clock-fill"></i></span>
          <span class="gd-contact-k">Hours</span>
          <span class="gd-contact-v"><?= e($site['business_hours']) ?></span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== FORM + INFO/MAP ===================== -->
<section class="pb-5">
  <div class="container">
    <div class="row g-4 align-items-stretch">

      <!-- Form -->
      <div class="col-lg-7 gd-reveal">
        <div class="gd-quote h-100">
          <span class="gd-eyebrow">Request a Free Quote</span>
          <h2 class="gd-section-title mb-2">Tell us about <span>your space</span></h2>
          <p class="gd-quote-lede mb-4">We'll reply within 24 hours with a transparent, written quote — no obligation.</p>

          <form data-gd-form
                data-gs-endpoint="<?= e($DATA['config']['google_sheet_endpoint']) ?>"
                data-source="contact-page"
                data-fallback-email="<?= e($site['email']) ?>"
                novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="c-name">Your name</label>
                <input class="form-control" id="c-name" name="name" type="text"
                       placeholder="e.g. Priya Sharma" autocomplete="name" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="c-phone">Phone</label>
                <input class="form-control" id="c-phone" name="phone" type="tel"
                       placeholder="e.g. 98450 12345" autocomplete="tel"
                       inputmode="tel" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="c-email">Email <span class="text-muted fw-normal">(optional)</span></label>
                <input class="form-control" id="c-email" name="email" type="email"
                       placeholder="you@example.com" autocomplete="email" inputmode="email">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="c-interest">Interested in</label>
                <select class="form-control" id="c-interest" name="interest">
                  <?php foreach ($DATA['categories'] as $c): ?>
                    <option value="<?= e($c['slug']) ?>"><?= e($c['name']) ?></option>
                  <?php endforeach; ?>
                  <option value="other">Something else</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label" for="c-msg">Message</label>
                <textarea class="form-control" id="c-msg" name="message" rows="5" placeholder="Approximate area, location, timeline..."></textarea>
              </div>
              <div class="col-12 d-flex flex-wrap align-items-center gap-2">
                <button class="btn btn-gd-gold" type="submit">Send Message <i class="bi bi-send ms-1"></i></button>
                <span class="ms-1 small text-success d-none" data-gd-success>
                  <i class="bi bi-check-circle-fill"></i> Thanks — we'll be in touch shortly.
                </span>
                <span class="ms-1 small text-danger d-none" data-gd-error>
                  <i class="bi bi-exclamation-triangle-fill"></i> Couldn't send — please call or WhatsApp us.
                </span>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Info + map -->
      <div class="col-lg-5 gd-reveal">
        <div class="gd-contact-info h-100 d-flex flex-column">
          <span class="gd-eyebrow">Visit / Reach Us</span>
          <h2 class="gd-section-title h3 mb-3">Our <span>studio</span></h2>
          <address class="mb-3">
            <i class="bi bi-geo-alt-fill text-success me-2"></i><?= e($site['address']) ?>
          </address>
          <p class="mb-2"><i class="bi bi-telephone-fill text-success me-2"></i><a href="tel:<?= e($site['phone']) ?>"><?= e($site['phone_label']) ?></a></p>
          <p class="mb-2"><i class="bi bi-envelope-fill text-success me-2"></i><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></p>
          <p class="mb-3"><i class="bi bi-clock-fill text-success me-2"></i><?= e($site['business_hours']) ?></p>

          <ul class="list-inline gd-socials mb-3">
            <?php foreach (gd_socials($site['socials']) as $network => $url): ?>
              <li class="list-inline-item"><a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($network)) ?>"><i class="bi bi-<?= e($network) ?>"></i></a></li>
            <?php endforeach; ?>
          </ul>

          <div class="gd-map-embed mt-auto">
            <iframe
              title="Green Divine location — <?= e($site['map_query']) ?>"
              src="https://www.google.com/maps?q=<?= rawurlencode($site['map_query']) ?>&output=embed"
              width="100%" height="240" style="border:0;" loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
