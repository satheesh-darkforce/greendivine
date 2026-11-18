<?php
/**
 * Single source of truth for "which files must be byte-identical in
 * production". Both tools/build-asset-manifest.php (writes the manifest
 * from this dev copy) and tools/asset-check.php (verifies a deployed copy
 * against it) read this list, so the two can never drift apart.
 *
 * Add every new CSS/JS/font asset here. Images are deliberately excluded:
 * they are admin-uploaded at runtime and legitimately differ per server.
 */
return [
    'assets/vendor/bootstrap-5.3.3/bootstrap.min.css',
    'assets/vendor/bootstrap-5.3.3/bootstrap.bundle.min.js',
    'assets/vendor/bootstrap-icons-1.11.3/bootstrap-icons.css',
    'assets/vendor/bootstrap-icons-1.11.3/fonts/bootstrap-icons.woff2',
    'assets/vendor/bootstrap-icons-1.11.3/fonts/bootstrap-icons.woff',
    'assets/css/style.css',
    'assets/js/main.js',
    'admin/assets/css/admin-core.css',
    'admin/assets/css/login.css',
    'admin/assets/js/admin-core.js',
];
