<?php
/**
 * Terms of Service — template content (Section P0-4 of docs/update.md).
 * Same note as pages/privacy.php: a real starting draft, still a template —
 * have it reviewed before relying on it. Update $updated when it changes.
 */
$site = $DATA['site'];
$updated = '19 July 2026';
?>
<section class="gd-subhero" style="background-image:url('<?= e(gd_img('grass-5.jpg')) ?>')">
  <div class="container">
    <span class="gd-eyebrow">Legal</span>
    <h1>Terms of <span>Service</span></h1>
    <p class="lead opacity-90 mb-0">Last updated: <?= e($updated) ?></p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="gd-blog-body gd-reveal">
          <p class="lead">
            These terms cover your use of the <?= e($site['domain']) ?>
            website. By browsing this site or submitting a form on it, you
            agree to the points below.
          </p>

          <h2>Website content</h2>
          <p>Product information, pricing labels and imagery on this site
          are for guidance and are subject to change without notice. A
          final, binding quote is always confirmed in writing after a site
          visit — prices shown on the website are indicative starting
          points, not a fixed offer.</p>

          <h2>Enquiries &amp; quotes</h2>
          <p>Submitting a quote request or contact form does not create a
          contract between you and <?= e($site['name']) ?>. A service
          agreement only begins once both sides confirm the scope, price
          and timeline in writing (email, WhatsApp, or a signed quote).</p>

          <h2>Intellectual property</h2>
          <p>The text, photography, and design of this website belong to
          <?= e($site['name']) ?> unless otherwise credited, and may not be
          copied or reused without permission.</p>

          <h2>Third-party links</h2>
          <p>This site may link out to third-party services (for example,
          WhatsApp or Google Maps). We aren't responsible for the content
          or privacy practices of sites we don't operate.</p>

          <h2>Liability</h2>
          <p>We make reasonable efforts to keep this website accurate and
          available, but it's provided "as is" without guarantees of
          uninterrupted access. Installed products and completed work are
          covered separately by the written warranty terms included with
          your specific project quote — not by this website disclaimer.</p>

          <h2>Changes to these terms</h2>
          <p>We may update these terms occasionally; the date at the top
          of this page always reflects the latest version.</p>

          <h2>Contact us</h2>
          <p>
            Questions about these terms?<br>
            Email: <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a><br>
            Phone: <a href="tel:<?= e($site['phone']) ?>"><?= e($site['phone_label']) ?></a>
          </p>
        </div>
      </div>
    </div>
  </div>
</section>
