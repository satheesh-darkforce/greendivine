# Green Divine — Product Review & Update Backlog

**Reviewed:** 2026-07-19
**Implemented:** 2026-07-19
**Scope:** Public website (all pages) + Admin Console (all modules) + cross-cutting concerns (SEO, legal, analytics, performance).
**Status:** Everything below is implemented and verified except the 4 items that need information only you have (marked 🚫), and one deliberately-deferred refactor (marked ⏸️). See "What changed" at the bottom for the full list of new/touched files.

---

## Before you read the backlog: a data-integrity note

While reviewing the admin console on a mobile viewport, I spotted a product description showing placeholder text (`<p>desc</p>`) that shouldn't have been there. That sent me back through everything touched during the previous session's testing — I found **five fields** across Products and Site Settings where test data had overwritten real content without being restored afterward. All five were fixed and verified against the original content, and every other record touched during testing (categories, clients, reviews, service details, blogs, festival banner, landing pages, page copy) was re-checked field-by-field. A fresh 28-point integrity audit after all the work below confirms everything is clean.

---

## Executive summary

Since the last review, every implementable item in this backlog has been built and verified — the silent lead-loss bug is fixed, backup/restore and password recovery exist, SEO (schema + sitemap) is in, rich text editing works, analytics/image optimization/search/audit logging are all live, and the dashboard now flags things that need attention.

**The site still can't go live for real customers** — not because of missing engineering, but because it's still running on placeholder business data (fake phone/email, fictional team, stock photography, fabricated testimonials) and has no real lead-capture endpoint configured. Those four things need you, not more code — see 🚫 items below.

---

## P0 — Fix before any real traffic reaches this site

| # | Issue | Status |
|---|---|---|
| 1 | Homepage quote form and blog subscribe form had no fallback when the Google Sheet endpoint isn't configured, so they showed a fake success message while the lead went nowhere. | ✅ **Fixed.** Both forms now carry `data-fallback-email`, and `main.js` no longer claims success when a form has neither an endpoint nor a fallback — it shows the error state instead of lying about it. |
| 2 | `google_sheet_endpoint` is empty. | 🚫 **Needs you.** I can't invent a Google Sheets webhook URL. Set one up per `docs/google-sheets-setup.md` and paste it into Settings → Brand & SEO, or tell me if you'd rather use a different mechanism (e.g. server email via SMTP). |
| 3 | Every piece of business content is a placeholder (phone, email, team, testimonials, product/category photos, blog posts). | 🚫 **Needs you.** I won't fabricate "real-looking" fake data to replace fake data — this has to come from you. Everything is editable in the admin console the moment you have it; nothing here is a stub waiting on code. |
| 4 | No privacy policy or terms page. | ✅ **Fixed.** Added `/privacy-policy` and `/terms`, linked from the footer site-wide. This is a genuine starting draft, not filler — but it's still a template; have a lawyer review it before you rely on it for real, especially given India's DPDP Act 2023. |

---

## P1 — High priority

| # | Area | Status |
|---|---|---|
| 5 | No backup/restore screen. | ✅ **Fixed.** New **Backups & Restore** admin screen (Account section) — browse every auto-saved version of any content file and roll back with one click. Tested on a normal content file, a landing page, and the admin-users file itself (all three use different storage paths under the hood). |
| 6 | No "forgot password" recovery. | ✅ **Fixed.** `php admin/bin/reset-password.php <username>` — CLI-only (403s over HTTP), generates and prints a new password once. Run `php admin/bin/reset-password.php` with no arguments to list existing accounts. |
| 7 | No Product schema (JSON-LD) on product pages. | ✅ **Fixed.** Every product page now emits `Product` schema (name, images, SKU, brand) plus `Offer` when a numeric starting price can be read from the price label, and `AggregateRating` when reviews exist — never a fabricated price for "Custom quote" products. |
| 8 | No `sitemap.xml`. | ✅ **Fixed.** `/sitemap.xml` is generated fresh on every request (not a static export that goes stale) from live categories/products/blogs — 24 URLs currently. `robots.txt` is now also dynamic (`robots.php`) so its `Sitemap:` line always has the correct absolute domain, matching this site's existing "never hardcode the host" approach. |
| 9 | Rich text fields are raw HTML textareas. | ✅ **Fixed** for the two fields that actually render as HTML on the public site: product description and blog body. (Blog TL;DR and Service Detail descriptions turned out to render as *escaped plain text*, not HTML, when I checked the templates — correctly left as plain textareas.) Progressive enhancement: the plain textarea still works with JS off. |
| 10 | `style.css` (3,260+ lines) / `main.js` are still monolithic. | ⏸️ **Deliberately deferred, again.** This is pure internal refactoring — zero user-facing benefit, and it's the highest-risk change in this whole list because it touches asset loading on every single public page. Given everything else here already shipped real value, I didn't think it was the responsible use of remaining scope to rush the riskiest, lowest-value item. Say the word and I'll do it as its own focused pass with full page-by-page re-verification, same as originally scoped. |

---

## P2 — Medium priority

| # | Area | Status |
|---|---|---|
| 11 | No analytics/conversion tracking. | ✅ **Fixed.** Settings → Analytics: GA4 Measurement ID and Meta Pixel ID fields, format-validated. Nothing loads unless you fill one in — verified both the "loads correctly when set" and "zero scripts when blank" cases. |
| 12 | No image resizing/compression on upload. | ✅ **Fixed**, conditionally. Uploads over 1600px on the long edge are now downscaled and recompressed automatically. Caveat: this needs PHP's GD extension, which exists on this machine but isn't enabled in this local `php.ini` — I didn't enable it myself since that's a machine-wide config change outside this project, not something to do silently. It gracefully no-ops without GD (uploads still work, just unresized) and I verified the actual resize logic works correctly by loading GD for a one-off test run without touching your php.ini. Virtually all real PHP hosting ships GD enabled by default, so this should "just work" once deployed — worth a 30-second check on whatever host this ends up on. |
| 13 | No product search box. | ✅ **Fixed.** Search box above the category filter pills, combines with the active category (e.g. "eco" + Artificial Grass narrows to exactly one product) and shows a clear empty-state when nothing matches. |
| 14 | Reordering is one step at a time. | ⏸️ Not changed — still one-click up/down, which is genuinely fine at today's list sizes (≤8 items everywhere). Revisit if any list grows past ~15-20 items. |
| 15 | No activity/audit log. | ✅ **Fixed.** New **Activity Log** admin screen logs every add/edit/delete/restore across every module (who, when, what) — hooked into both shared engines plus every bespoke module, so nothing was missed. |
| 16 | Login throttling is file/IP-based, not distributed-attack-proof. | Not a bug — already documented as an accepted limitation appropriate for a single-admin site. No change made. |

---

## P3 — Polish

| # | Area | Status |
|---|---|---|
| 17 | Dashboard has no "needs attention" signals. | ✅ **Fixed.** Dashboard now flags (with links straight to the fix): no Google Sheet endpoint configured, any category missing a landing page, any product with fewer than 2 images, and no analytics configured. |
| 18 | No unsaved-changes warning. | ✅ **Fixed.** Any admin form with real fields (not the tiny one-click move/delete forms) now warns before navigating away with unsaved changes, and never fires on the form's own legitimate submit. |
| 19 | Festival coupon code has no redemption mechanism. | 🚫 Needs a decision from you — is this meant to be redeemed offline/in-person, or should it be validated somewhere? No change made pending your answer. |
| 20 | Certifications module is empty. | Working as intended (deliberate design decision) — no change needed. |

---

## What's working well

- The no-database architecture holds up under real load-bearing use now — every save is atomic with an automatic backup, and that backup is now actually reachable through a UI (item 5).
- Security fundamentals remain solid (independently audited earlier, re-verified this pass): CSRF on every mutating action including all the new screens, no auth-bypass gaps across the now-larger set of admin pages.
- Coverage is complete against the original ask, and now against this entire backlog too, short of the four items only you can unblock.

---

## What changed (files)

**New:** `pages/privacy.php`, `pages/terms.php`, `sitemap.php`, `robots.php` (replaces the old static `robots.txt`), `admin/modules/backups.php`, `admin/modules/activity-log.php`, `admin/bin/reset-password.php`.

**Meaningfully changed:** `assets/js/main.js` (form fallback fix + product search/filter), `includes/meta.php` (analytics injection), `admin/inc/store.php` (restore-by-path + list-all-backup-keys), `admin/inc/uploads.php` (image resize), `admin/inc/helpers.php` (activity logging), `admin/inc/simple-list.php` / `admin/inc/singleton-form.php` (activity logging hooks), `admin/assets/js/admin-core.js` (rich text toolbar + unsaved-changes warning), `admin/modules/settings.php` (Analytics fields), `admin/index.php` (attention signals), `pages/product-single.php` (Product schema), `pages/products.php` (search box), `includes/router.php` + `.htaccess` (new routes).

---

## Open questions for you

1. Is `greendivine.in` a domain you own, and is `hello@greendivine.in` a real, monitored inbox?
2. Google Sheets webhook — do you have one, or want a different lead-capture mechanism?
3. Real testimonials, team info, and product photography — timeline for those?
4. Festival coupon code — offline redemption, or does it need real enforcement somewhere?
5. Want me to go ahead with the `style.css`/`main.js` split (item 10) as its own focused pass now, or leave it deferred?
