# Deploying Green Divine to Hostinger

## The incident this process exists to prevent

The live site rendered with a broken header (mobile nav drawer stuck inline,
duplicate logo, a visible "Skip to main content" link) and clipped form
selects, while localhost was fine.

Cause: `public_html/assets/css/bootstrap.min.css` on the server was
**Bootstrap 4.5.0** — a leftover from an earlier site at that path. The FTP
sync never overwrote it. Every asset returned HTTP 200, so nothing looked
wrong; the markup simply used v5-only classes (`.offcanvas`,
`.visually-hidden-focusable`, `.form-select`) that v4 has no rules for.

Three guards now make that failure loud instead of silent:

| Guard | Where | Catches |
|---|---|---|
| Version-stamped vendor folders | `assets/vendor/bootstrap-5.3.3/` | A stale file can't sit at a new build's path — a missed upload 404s |
| `?v=<mtime>-<size>` fingerprints | `gd_asset()` in `data.php` | Browsers/Cloudflare replaying a cached old copy after a fix |
| Size + SHA-256 manifest | `data/asset-manifest.php` | Wrong or partially-uploaded files |

---

## Deploy checklist

### 1. Rebuild the asset manifest (locally, every time CSS/JS changed)

```bash
php tools/build-asset-manifest.php
```

This rewrites `data/asset-manifest.php`. **Skipping this after editing
`style.css` or `main.js` makes the deploy check fail on correct files** —
it is a build step, not an optional extra.

### 2. Upload

Upload the whole project to `public_html/`, including:

- `assets/` (the `vendor/` folder especially)
- `admin/` — **the entire folder, including `admin/assets/`**
- `includes/`, `pages/`, `data/`, `tools/`
- `data/asset-manifest.php`
- `.htaccess` (root **and** the nested ones in `admin/`, `admin/config/`,
  `admin/inc/`, `data/`, `assets/images/`, `tools/`)

In FileZilla set **Transfer → Overwrite → "Overwrite"**, never
*"Overwrite if newer"* or *"Skip"*. Hostinger's File Manager unzip also
skips existing files by default — extract to an empty folder and swap, or
delete the target folder first.

Do not upload `data/backups/` or `data/security/`.

### 3. Verify on the server — before telling anyone it's live

```
https://greendivine.in/tools/asset-check.php?key=<your key>
```

Expected last line: `PASS — all 10 assets match the shipped build.`

Any `MISSING` / `WRONG FILE` line names the exact file to re-upload.
Over SSH the same check runs as `php tools/asset-check.php` and exits
non-zero on failure, so it can be chained into a deploy script.

### 4. Purge the Cloudflare cache

The site sits behind Cloudflare (`cf-cache-status: HIT` on assets).
**Cloudflare dashboard → Caching → Configuration → Purge Everything.**

Fingerprinted URLs make this mostly unnecessary for CSS/JS, but HTML and
images are still edge-cached.

While you are there, confirm these stay **off** — each one rewrites assets
in flight and can reintroduce exactly this class of bug:
Speed → Optimization → **Rocket Loader**, **Auto Minify (JS/CSS/HTML)**,
and **Mirage**.

### 5. Confirm in a browser

Open the site in a **private window**, then DevTools → Console. You should
see no errors. If the deploy is broken you will see either:

- `[Green Divine] Broken deploy — these assets are missing or stale on the
  server:` — the server knows the file is wrong (also in the page source as
  an `HTML DEPLOY WARNING` comment, and in the PHP error log), or
- `[Green Divine] Bootstrap 5 is not fully loaded:` — the *browser* got a
  bad copy. Hard-reload, then purge Cloudflare.

Also check `/admin/login.php` loads styled — it shares the same Bootstrap
files, so it breaks in exactly the same way.

---

## Known outstanding gap (as of this fix)

`admin/` was never uploaded to production: `/admin/login.php` and every
`/admin/assets/*` file return 404. The admin console is not reachable on
the live site until that folder is deployed.

---

## Where things live

| File | Purpose |
|---|---|
| `tools/asset-list.php` | The list of files that must be byte-identical in production |
| `tools/build-asset-manifest.php` | **Local only.** Regenerates the manifest from this working copy |
| `tools/asset-check.php` | **Server.** Verifies a deployment; `?key=` gated, CLI-friendly |
| `data/asset-manifest.php` | Generated. Size + SHA-256 per asset |
| `gd_asset()` in `data.php` | Emits fingerprinted URLs, flags size mismatches per request |
| `bootstrapGuard()` in `assets/js/main.js` | Browser-side feature detection + emergency CSS fallback |

## Rotating the check key

The browser check is gated by the key in `tools/check-key.php`. That file is
**not in git** (the repository is public), so create it on the server by
copying `tools/check-key.example.php` and putting a long random value in it
(`php -r "echo bin2hex(random_bytes(16));"`). To rotate the key, change that
value. Without the file the browser check returns 404; `php tools/asset-check.php`
over SSH always works.
