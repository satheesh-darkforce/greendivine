# Green Divine — UI/UX Audit & Premium Elevation Plan

**Prepared for:** Green Divine, Bangalore
**Reviewed:** Home, Products, Category (`/category/artificial-grass`), Product Single, Services, About, Contact — desktop and mobile
**Method:** Live rendered audit of the running site (both viewports), plus a code-level review of `assets/css/style.css`, `assets/js/main.js`, `data.php`, and the page templates.

---

## 1. The one idea that matters most

**A client doesn't average a site's quality across all its sections — they anchor on the worst thing they saw, and that discounts everything else.**

The code architecture underneath this site is genuinely good: a clean, reusable PHP component system, a considered colour and type palette, a real motion language. On a pure engineering rubric this build is a 7 or 8 out of 10. But the moment a visitor hits something that reads as *fake* — a stock headshot standing in for a real teammate, a "completed installation" gallery showing a bathroom instead of a lawn — the whole site retroactively feels fake. Every good decision made elsewhere gets discounted, because trust, once broken, doesn't average back up.

That is why this document is not "redesign the site." It is: **five specific, fixable trust breaks are undoing an otherwise solid foundation**, plus a smaller set of maturity gaps that separate *competent* from *premium*. Fix the breaks first — they are cheaper than they look, and they will move the client's score more than any amount of visual polish.

### Scorecard

| Dimension | Score today | Realistic target after Phase 0–1 | Driven by |
|---|---|---|---|
| Architecture & code | 8 / 10 | 8.5 / 10 | Already solid — minor cleanup only |
| Visual design system | 7 / 10 | 8.5 / 10 | Findings 5, 7 |
| **Content & photo integrity** | **2 / 10** | **8 / 10** | Findings 1, 6 |
| **Trust & credibility signals** | **3 / 10** | **8 / 10** | Finding 2 |
| Interaction & motion polish | 6 / 10 | 8 / 10 | Findings 3, 4 |
| Mobile experience | 5 / 10 | 8 / 10 | Mobile-first pass, §4 |
| **Client-perceived overall** | **2 / 10** | **8.5 / 10** | All of the above |

The two bolded rows are doing almost all of the damage to the client's overall impression — they're first in the findings list below for that reason.

---

## 2. Foundation audit — what's already working

Worth stating explicitly, because none of this needs to be rebuilt:

- **Genuinely reusable architecture.** One data-driven template (`pages/category.php`) renders every category landing page from a plain PHP array in `data/landing/<slug>.php`. Adding a new product line is a content task, not a dev task. This is the right foundation to build a premium layer on top of.
- **A real palette, not defaults.** Deep forest green, a warm ivory canvas, a restrained brass/gold accent used at roughly 5% — all centralised as CSS custom properties in `assets/css/style.css`. The instinct behind the "Organic Luxury" system is correct; it's the execution around it (photography, content) that's inconsistent with it.
- **A considered motion system.** A proper scroll-reveal system (`.gd-reveal` + `IntersectionObserver`), eased number counters, autoplay carousels that pause on hover/focus/tab-hidden, and a `prefers-reduced-motion` fallback. The craft is there — it's aimed at some of the wrong things (see Finding 3).
- **SEO groundwork already in place.** Clean URLs via `.htaccess`, per-page `$pageMeta`, JSON-LD `Organization` / `FAQPage` / `ItemList` markup, semantic HTML throughout. This will pay off once the content it's describing is trustworthy.

---

## 3. Root-cause diagnosis — seven findings, ordered by trust cost

### Finding 1 — CRITICAL — The photography tells a different company's story

**Where:** `/product/artificial-grass-pro-40mm` → "Completed Installations"; the same mismatched set repeats on the Services page ("A Glimpse of Our Installations") and the `/category/artificial-grass` landing page.

**What we saw:** A gallery captioned "Completed Installations" for the Artificial Grass Pro 40mm product shows — in order — a bathroom with two gold-framed mirrors and a vanity, a paint roller sitting in a paint tray, and a stack of decorative dinner plates. None of these are grass, gardens, or landscaping of any kind.

**Why it matters:** This isn't a taste issue, it's a factual one. A prospective client scanning "have they actually done this kind of work before" sees direct evidence that they haven't — or that the site can't be trusted to show what it claims to show. This is very likely the single largest contributor to the 2/10 rating. A non-designer client can't always articulate *why* something feels off, but they react to it immediately and viscerally.

**Fix direction:** Replace every gallery and hero image with real project photography. Even unedited phone photos from past jobs will outperform mismatched stock. If zero real photos exist yet for a given product, treat that as a scheduling problem to solve this week (one site visit with a phone camera covers most of the catalogue) — not a design problem. Until real photos exist for a section, remove the gallery block entirely rather than show the wrong thing.

---

### Finding 2 — CRITICAL — Trust signals that read as fabricated

**Where:** `/about` → "People behind every install" and "Certifications & Recognition"; sitewide phone number.

**What we saw:** Four named team members (Arjun Rao, Meera Iyer, Vikram Shetty, Anika Das) are paired with generic stock headshots that don't read as a consistent real team — different photographic styles, lighting, and framing across all four. Immediately below, a "Certifications & Recognition" section shows "ISO 9001," "IGBC," "Justdial," and "Google" rendered as plain grey text in boxes, with no actual badge artwork from any of those bodies. The phone number across the entire site is still the placeholder `+91 99999 99999`.

**Why it matters:** Individually these are small. Together they form the exact pattern a skeptical visitor — or a client reviewing their own site before sending it to *their* customers — notices fastest: nothing here has been verified as real yet.

**Fix direction:** Swap in real team photos (a five-minute phone photoshoot against a plain wall beats a stock face every time), the real business phone number, and actual certification logos sourced from the issuing bodies. If a credential isn't real yet, remove it from the page — a shorter, honest trust section beats a padded, fake-looking one.

---

### Finding 3 — HIGH — The site interrupts before it's earned attention

**Where:** Homepage and other pages, ~5 seconds after first load (`includes/quote-popup.php`, driven by `assets/js/main.js`).

**What we saw:** A phone-number popup with a full dark scrim fires five seconds after landing, on every page, for every first-time visitor — before they've read a single word of what Green Divine does. It's a pattern borrowed from high-pressure lead-gen sites, and it directly undercuts the "premium, unhurried" tone the rest of the palette is going for. There's also a quirk in the code: for any visitor whose device is set to reduced-motion, the popup fires after 1.5 seconds instead of 5 — even less time to earn the ask.

**Fix direction:** Replace the timed full-screen interstitial with a persistent, low-friction affordance — a slim sticky bar or a single floating action that's always available but never blocks the page. If an interrupt is kept at all, reserve it for a genuine exit-intent signal on desktop only, never on first paint.

---

### Finding 4 — HIGH — Every section on the page can go permanently invisible

**Where:** Sitewide — `assets/css/style.css` (`.gd-reveal { opacity: 0; }`) + `assets/js/main.js` (`IntersectionObserver`).

**What we saw:** Every content block on the homepage — 32 separate elements, from the hero copy to the entire quote form — is styled `opacity: 0` by default and only becomes visible once a single external script runs and its scroll-observer fires the `.in` class. There is no `<noscript>` fallback anywhere in the codebase. In our review this worked correctly under a normal scroll, but it means the whole page's visibility rests on exactly one point of failure: if that one script 404s, gets blocked, or throws an error for any reason — a case-sensitive filename mismatch when deploying from Windows to a Linux host, a flaky network, a browser extension, a corporate content filter — the visitor sees a page that is *structurally complete but visually blank*. That is exactly the kind of failure a client screenshots and rates a 2.

**Fix direction:** Keep the reveal animation, but change its failure mode: default content to **visible**, and use JavaScript only to add the entrance *motion* on top of already-visible content, never to *unlock* visibility in the first place. A one-line CSS change plus a `<noscript>` rule closes this permanently.

---

### Finding 5 — MEDIUM — Every page is built from the same small icon-card grid

**Where:** "Why Us" and "Our 4-Step Process" (Home), "What sets us apart," "Certifications" and "The Team" (About), "Everything Your Space Needs" (Services), "Built-in Benefits" (category pages).

**What we saw:** All of these sections resolve to the same visual unit: a small icon in a rounded box, a bold line, a sentence of grey text — repeated six to eight times in a grid, on nearly every page in the site. It's an efficient template, but it means nothing on the page ever gets to be *the one important thing*. Premium brand sites spend their visual weight unevenly on purpose — one full-bleed photograph, one large pull-quote, one confident statistic — rather than distributing it evenly across a dozen identical tiles.

**Fix direction:** Keep two or three icon-grids maximum per page. Convert the rest into asymmetric, editorial moments — a large photo with a short caption, a single client quote at size, a before/after pair — using the real photography from Finding 1.

---

### Finding 6 — MEDIUM — Two aesthetics are stitched together

**Where:** Homepage festival hero, category tile images, various hero backgrounds.

**What we saw:** The colour and type system is doing genuine "editorial luxury" work — serif display type, a restrained gold accent, warm ivory surfaces. But the imagery choices sit in a completely different, more generic register: the current festival campaign hero (a "Diwali Green Makeover Sale") uses a single small succulent in a teal plastic pot on a plain white background as its lead image, for a company whose actual business is full-scale turf and rooftop landscaping installs. Category tiles lean on generic lifestyle stock. The mismatch is subtle but constant, and it's a large part of why the site reads as templated rather than art-directed, even where the CSS work is genuinely careful.

**Fix direction:** Write a one-page "what a Green Divine photo looks like" brief (lighting, crop, subject — real Bangalore installs, not stock catalogue shots) and hold every hero, category, and gallery image to it. This is a content-sourcing fix, not a code fix.

---

### Finding 7 — MEDIUM — The copy is generic marketing-speak

**Where:** Sitewide taglines and section headings.

**What we saw:** "Sustainable Landscaping for Modern Spaces," "Built On Trust & Craft," "Experience & Excellence" — none of it is wrong, and none of it is *this company's* voice. It could be pasted onto any landscaping site in any city. There's no specific, ownable point of view: no mention of Bangalore's actual climate challenges, no founder story in the founder's own words, nothing a competitor couldn't also claim.

**Fix direction:** Rewrite headline copy from the real specifics already sitting in `data.php` — "8 years, 500+ turf installs, one in-house crew" is more distinctive and more credible than "Built On Trust & Craft." Specific beats polished.

---

## 4. Mobile-first approach

Every finding above is worse on a phone. A local landscaping search is a mobile search — most first impressions of this site will happen on a 6-inch screen, one-handed, on mobile data.

**The core mobile fact:** on a 390px-wide screen, a single image *is* the section — it fills 80–100% of the viewport width. There's no surrounding layout to dilute a mismatched photo the way a wide desktop grid does. Content-integrity fixes (Findings 1, 2, 6) matter roughly twice as much on mobile as on desktop.

| # | Change | Why |
|---|---|---|
| 1 | **Replace the scrim popup with a thumb-zone bar.** | A full-screen interrupt on a phone blocks the entire screen, not a corner of it — it feels far more aggressive than the same popup on desktop. Swap it for a slim bar pinned above the thumb zone: "Free site visit — Chat on WhatsApp," always present, never blocking. |
| 2 | **Cut card-grid density before it reaches mobile.** | Six "Why Us" tiles become six full-width stacked cards — a long, repetitive scroll of near-identical boxes. Cap any icon-grid at 3 items on mobile and move the rest into a swipeable strip or fold them into fewer, denser cards. |
| 3 | **Shorten the quote form on small screens.** | The homepage quote form asks for name, phone, email, interest, and a message — six fields, on a phone keyboard. The popup already gets this right with phone-only. Apply the same logic to the main form on mobile: name + phone up front, everything else after. |
| 4 | **Self-host and compress imagery.** | Hero and category images are currently hot-linked to Unsplash at full resolution with no responsive `srcset`. On mobile data this is pure latency with no visual benefit. Self-host, compress, and serve a phone-sized crop by default. |
| 5 | **Shoot photography vertical-first.** | Once real project photography exists (Finding 1), commission it with a 4:5 or 9:16 crop in mind, not just a wide desktop crop squeezed down. A vertical hero shot of an actual finished lawn or rooftop garden will carry a phone screen far better than a landscape crop forced into a tall box. |
| 6 | **Make the floating actions earn their space.** | The call/WhatsApp floating buttons are the right idea — for an India-local service business, WhatsApp is often the real conversion channel. Keep them, but confirm they never sit under the browser's own bottom chrome or overlap a sticky CTA in the same corner. |

---

## 5. The roadmap — sequenced, not simultaneous

Trust breaks first — they're the cheapest fixes with the largest score movement. Craft and systemization follow.

### Phase 0 — Days 1–3 — *Stop the bleeding*

- [ ] Pull every mismatched gallery image — show fewer real photos rather than more wrong ones (Finding 1).
- [ ] Replace stock team headshots and the placeholder phone number with real ones (Finding 2).
- [ ] Remove or verify certification badges — real logos only; drop the rest for now.
- [ ] Turn the popup into a passive sticky bar — no scrim, no timer (Finding 3).
- [ ] Fix the reveal-animation failure mode — default content visible; animate on top of it, not gated by it (Finding 4).

### Phase 1 — Week 1–2 — *Make it feel premium*

- [ ] Commission a real photography set — one site visit, phone camera is enough to start; shoot to the brief in Finding 6.
- [ ] Rebuild 4–5 repeated icon-grids into editorial moments — large photo + caption, single quote at size, before/after pairs (Finding 5).
- [ ] Rewrite hero and section copy — specific numbers and voice, not generic marketing lines (Finding 7).
- [ ] Re-tune motion timing — calmer entrance easing; dial back the shine/glow effects on the festival ribbon that currently read as "sale site" rather than "premium brand."

### Phase 2 — Week 3–4 — *Systemize + mobile-perfect*

- [ ] Full mobile interaction pass — thumb-zone CTA bar, shortened forms, vertical photography (all of §4).
- [ ] Self-host + compress + responsive images — drop the Unsplash hotlinks; add `srcset` across hero, category, and gallery images.
- [ ] Write the imagery + component brief — a one-page reference so future additions stay on-brand without needing a redesign.
- [ ] Accessibility pass — contrast on gold-on-white text, visible focus states, real alt text tied to the new photography.

### Phase 3 — Ongoing — *Compound the trust*

- [ ] Real testimonials with names, photos, and project links — replace generic review cards over time.
- [ ] Case-study pages for flagship projects — the biggest lever for a corporate/commercial lead, and it reuses the category template already built.
- [ ] Formalize the seasonal-campaign component — the Diwali hero shows the appetite for this already; turn it into a documented, reusable pattern instead of a one-off.

---

## 6. Closing note

None of this requires the framework, the routing, the data model, or the palette to change. It requires making sure every photograph, every named person, and every claimed credential on the site is real — and giving the resulting content room to breathe instead of packing it into another icon-grid. That is a smaller, cheaper, faster project than a rebuild, and it is the one that will actually move the client's number.

Re-score the table in §1 after Phase 0 + 1 before re-presenting to the client.
