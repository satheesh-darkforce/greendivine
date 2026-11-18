<?php
/**
 * Deploy verifier — run this on the SERVER right after every upload.
 *
 *   Browser : https://greendivine.in/tools/asset-check.php?key=<your key>
 *   SSH/CLI : php tools/asset-check.php
 *
 * It SHA-256s every file in tools/asset-list.php against the shipped
 * data/asset-manifest.php and prints OK / MISSING / WRONG FILE per asset.
 * Exit code is 0 when everything matches and 1 otherwise, so it can also
 * be wired into a deploy script.
 *
 * The browser key lives in tools/check-key.php (not in git — copy
 * tools/check-key.example.php and put a long random value in it). Without
 * that file the browser check stays off (404); the CLI check always works.
 */
declare(strict_types=1);

$keyFile = __DIR__ . '/check-key.php';
$checkKey = is_file($keyFile) ? trim((string)require $keyFile) : '';

$isCli = PHP_SAPI === 'cli';
if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    if ($checkKey === '' || !hash_equals($checkKey, (string)($_GET['key'] ?? ''))) {
        http_response_code(404);
        exit("Not found\n");
    }
}

$root     = dirname(__DIR__);
$paths    = require __DIR__ . '/asset-list.php';
$manifestFile = $root . '/data/asset-manifest.php';

echo "Green Divine — deploy asset check\n";
echo str_repeat('=', 64) . "\n";

if (!is_file($manifestFile)) {
    echo "FAIL  data/asset-manifest.php is missing — upload it, or run\n";
    echo "      'php tools/build-asset-manifest.php' locally and deploy the result.\n";
    if (!$isCli) http_response_code(500);
    exit(1);
}

$manifest = (array)require $manifestFile;
$failed   = 0;

foreach ($paths as $rel) {
    $file = $root . '/' . $rel;

    if (!is_file($file)) {
        printf("MISSING     %s  — not uploaded\n", $rel);
        $failed++;
        continue;
    }
    if (!isset($manifest[$rel])) {
        printf("NO-MANIFEST %s  — rebuild the manifest locally and redeploy it\n", $rel);
        $failed++;
        continue;
    }

    $size = filesize($file);
    $hash = hash_file('sha256', $file);
    $want = $manifest[$rel];

    if ($hash === $want['sha256']) {
        printf("OK          %s  (%d bytes)\n", $rel, $size);
        continue;
    }

    printf(
        "WRONG FILE  %s\n            expected %d bytes / %s\n            found    %d bytes / %s\n",
        $rel, (int)$want['size'], substr((string)$want['sha256'], 0, 16),
        $size, substr($hash, 0, 16)
    );
    $failed++;
}

echo str_repeat('-', 64) . "\n";
if ($failed === 0) {
    echo "PASS — all " . count($paths) . " assets match the shipped build.\n";
    exit(0);
}

echo "FAIL — {$failed} of " . count($paths) . " assets are wrong or missing.\n";
echo "Re-upload the files listed above (overwrite, do not 'skip existing'),\n";
echo "then purge the Cloudflare cache and run this check again.\n";
if (!$isCli) http_response_code(500);
exit(1);
