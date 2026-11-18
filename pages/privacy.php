<?php
/**
 * Privacy Policy — template content (Section P0-4 of docs/update.md).
 *
 * This is a genuine starting draft, not filler text, but it is still a
 * TEMPLATE: review it (ideally with a lawyer) before relying on it,
 * especially the data-collection list below if the forms or analytics
 * setup ever change. Update $updated whenever the policy itself changes.
 */
$site = $DATA['site'];
$updated = '19 July 2026';
?>
<section class="gd-subhero" style="background-image:url('<?= e(gd_img('vg-4.jpg')) ?>')">
  <div class="container">
    <span class="gd-eyebrow">Legal</span>
    <h1>Privacy <span>Policy</span></h1>
    <p class="lead opacity-90 mb-0">Last updated: <?= e($updated) ?></p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="gd-blog-body gd-reveal">
          <p class="lead">
            <?= e($site['name']) ?> ("we", "us") respects your privacy. This
            page explains what personal information we collect through
            <?= e($site['domain']) ?>, why we collect it, and how you can
            ask us to correct or delete it.
          </p>

          <h2>What we collect</h2>
          <p>When you submit a quote request, contact form, or newsletter
          sign-up on this site, we collect only what you type in: your
          name, phone number, email address (where asked), and any message
          or project details you choose to add. We do not ask for payment
          details, government ID numbers, or any other sensitive
          information through this website.</p>

          <h2>How we use it</h2>
          <p>We use this information for exactly one purpose: to contact
          you back about the enquiry you raised — a call, WhatsApp message,
          or email with a quote, a scheduling confirmation, or an answer to
          your question. We do not sell, rent, or share your contact
          details with third parties for their own marketing.</p>

          <h2>Where it's stored</h2>
          <p>Form submissions on this site are sent either to a private
          Google Sheet that only our team can access, or directly to our
          business email address as a fallback. Neither is publicly
          accessible.</p>

          <h2>Cookies &amp; local storage</h2>
          <p>This site does not use tracking cookies. It uses your
          browser's local storage for one small, functional purpose: to
          remember that you've already seen the "Get a Free Quote" popup,
          so it doesn't show again on your next visit. This is stored only
          on your own device and is never sent to us.</p>

          <h2>Your rights</h2>
          <p>You can ask us at any time to tell you what information we
          hold about you, to correct it, or to delete it entirely. Just
          reach out using the contact details below and we'll action it
          promptly.</p>

          <h2>Changes to this policy</h2>
          <p>If this policy changes, we'll update the date at the top of
          this page. Continuing to use the site after a change means you
          accept the updated policy.</p>

          <h2>Contact us</h2>
          <p>
            Questions about this policy or your data?<br>
            Email: <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a><br>
            Phone: <a href="tel:<?= e($site['phone']) ?>"><?= e($site['phone_label']) ?></a><br>
            Address: <?= e($site['address']) ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</section>
