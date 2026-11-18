<?php
/**
 * Image field helpers — every image field in the admin offers three ways
 * to set it (7.3 of the plan): pick an existing file already in that
 * bucket's folder, upload a new one, or paste an external URL (so today's
 * Unsplash-URL content keeps working without forcing a re-upload).
 *
 * Buckets mirror the folders gd_img() already resolves against, i.e. the
 * exact bucket strings used across the public templates.
 */

const GD_UPLOAD_MAX_BYTES = 5 * 1024 * 1024; // 5MB
const GD_UPLOAD_MIME_EXT  = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
const GD_MEDIA_BUCKETS = ['' => 'General', 'hero' => 'Hero', 'categories' => 'Categories',
    'products' => 'Products', 'installations' => 'Installations', 'clients' => 'Clients',
    'blog' => 'Blog', 'avatars' => 'Avatars'];

function gd_media_bucket_dir(string $bucket): string {
    $bucket = trim($bucket, '/');
    return __DIR__ . '/../../assets/images/' . ($bucket !== '' ? $bucket . '/' : '');
}

/** Existing image filenames already sitting in a bucket's folder, for the picker grid. */
function gd_media_list(string $bucket): array {
    $dir = gd_media_bucket_dir($bucket);
    if (!is_dir($dir)) return [];
    $files = array_diff(scandir($dir) ?: [], ['.', '..']);
    $files = array_filter($files, fn($f) => is_file($dir . $f) && preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $f));
    sort($files);
    return array_values($files);
}

/**
 * Handle an <input type="file" name="$field"> upload into that bucket's
 * folder. No file chosen (a valid, common case — every image field is
 * optional-to-change on edit) returns ok with name=null, not an error.
 */
function gd_handle_upload(string $field, string $bucket): array {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'name' => null];
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload failed (error code ' . $file['error'] . ').'];
    }
    if ($file['size'] > GD_UPLOAD_MAX_BYTES) {
        return ['ok' => false, 'error' => 'Image is too large — max 5MB.'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(GD_UPLOAD_MIME_EXT[$mime])) {
        return ['ok' => false, 'error' => 'Unsupported image type — use JPG, PNG, WEBP or GIF.'];
    }
    $ext  = GD_UPLOAD_MIME_EXT[$mime];
    $base = gd_slugify(pathinfo($file['name'], PATHINFO_FILENAME)) ?: 'image';
    $name = $base . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;
    $dir  = gd_media_bucket_dir($bucket);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return ['ok' => false, 'error' => 'Could not create the target folder.'];
    }
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return ['ok' => false, 'error' => 'Could not save the uploaded file.'];
    }
    gd_maybe_resize_image($dir . $name);
    return ['ok' => true, 'name' => $name];
}

const GD_UPLOAD_MAX_DIMENSION = 1600; // longest edge, px
const GD_UPLOAD_JPEG_QUALITY  = 82;

/**
 * Downscale an uploaded image in place if it's larger than needed for the
 * web (a phone photo is routinely 4000px+ on the long edge). Silently
 * does nothing if the GD extension isn't available on this host, or if
 * the image is already small enough — this is a size optimisation, never
 * something the upload should fail over.
 */
function gd_maybe_resize_image(string $path): void {
    if (!extension_loaded('gd')) return;
    $info = @getimagesize($path);
    if (!$info) return;
    [$width, $height, $type] = $info;
    if ($width <= GD_UPLOAD_MAX_DIMENSION && $height <= GD_UPLOAD_MAX_DIMENSION) return;

    $ratio     = min(GD_UPLOAD_MAX_DIMENSION / $width, GD_UPLOAD_MAX_DIMENSION / $height);
    $newWidth  = max(1, (int)round($width * $ratio));
    $newHeight = max(1, (int)round($height * $ratio));

    $src = match ($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
        IMAGETYPE_PNG  => @imagecreatefrompng($path),
        IMAGETYPE_GIF  => @imagecreatefromgif($path),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
        default        => false,
    };
    if (!$src) return;

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP || $type === IMAGETYPE_GIF) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    match ($type) {
        IMAGETYPE_JPEG => imagejpeg($dst, $path, GD_UPLOAD_JPEG_QUALITY),
        IMAGETYPE_PNG  => imagepng($dst, $path, 8),
        IMAGETYPE_GIF  => imagegif($dst, $path),
        IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($dst, $path, GD_UPLOAD_JPEG_QUALITY) : null,
        default        => null,
    };

    imagedestroy($src);
    imagedestroy($dst);
}

/**
 * Handle a multi-file <input type="file" name="$field[]" multiple> into a
 * bucket folder. Returns ['ok'=>true,'names'=>string[]] (bad individual
 * files are skipped with their error appended to 'errors', not fatal for
 * the rest of the batch) or ['ok'=>false,'error'=>...] only for a
 * structural problem (e.g. can't create the folder).
 */
function gd_handle_multi_upload(string $field, string $bucket): array {
    if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'] ?? null)) {
        return ['ok' => true, 'names' => [], 'errors' => []];
    }
    $count = count($_FILES[$field]['name']);
    $names = [];
    $errors = [];
    for ($i = 0; $i < $count; $i++) {
        if (($_FILES[$field]['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        $single = [
            'name'     => $_FILES[$field]['name'][$i],
            'type'     => $_FILES[$field]['type'][$i],
            'tmp_name' => $_FILES[$field]['tmp_name'][$i],
            'error'    => $_FILES[$field]['error'][$i],
            'size'     => $_FILES[$field]['size'][$i],
        ];
        $prevFiles = $_FILES;
        $_FILES = [$field => $single];
        $res = gd_handle_upload($field, $bucket);
        $_FILES = $prevFiles;
        if ($res['ok'] && $res['name'] !== null) $names[] = $res['name'];
        elseif (!$res['ok']) $errors[] = $res['error'];
    }
    return ['ok' => true, 'names' => $names, 'errors' => $errors];
}

/**
 * Build a final image-list value (for fields like Product images[] /
 * installations[]) from: which existing entries the admin left checked
 * ("{prefix}_keep[]"), any newly uploaded files ("{prefix}_add[]"), and
 * any pasted URLs ("{prefix}_add_urls", one per line).
 */
function gd_collect_image_list(string $prefix, array $existing, string $bucket): array {
    $keep = array_map('strval', (array)($_POST["{$prefix}_keep"] ?? []));
    $kept = array_values(array_filter($existing, fn($img) => in_array($img, $keep, true)));

    $uploaded = gd_handle_multi_upload("{$prefix}_add", $bucket);
    $urls     = gd_lines_to_list($_POST["{$prefix}_add_urls"] ?? '');

    return array_values(array_merge($kept, $uploaded['names'], $urls));
}

/**
 * Resolve one image field from a submitted form: an uploaded file wins,
 * then an explicitly-picked existing filename ("<field>_existing"), then
 * a pasted URL ("<field>_url"), then whatever the record already had.
 * Returns ['ok'=>bool, 'name'=>string|null, 'error'?=>string].
 */
function gd_resolve_image(string $field, string $bucket, ?string $previous): array {
    $up = gd_handle_upload($field, $bucket);
    if (!$up['ok']) return $up;
    if ($up['name'] !== null) return ['ok' => true, 'name' => $up['name']];

    $url = trim($_POST[$field . '_url'] ?? '');
    if ($url !== '') return ['ok' => true, 'name' => $url];

    $existing = trim($_POST[$field . '_existing'] ?? '');
    if ($existing !== '') return ['ok' => true, 'name' => $existing];

    return ['ok' => true, 'name' => $previous];
}

/**
 * Renders a compact image-field widget: current preview, a bucket-scoped
 * existing-file picker, an upload input, and a URL field — the three
 * input styles from 7.3 in one control. $name is the base field name
 * (e.g. "image", "images[0]"); bucket controls both the picker + upload
 * target folder.
 */
/**
 * Renders a multi-image field: a grid of existing images (each with a
 * "keep" checkbox, checked by default — uncheck to remove), a multi-file
 * upload input to add new ones, and a textarea to paste additional URLs.
 * Pairs with gd_collect_image_list() to read the result back on submit.
 */
function gd_image_list_field(string $prefix, string $bucket, array $current, string $label): void {
    ?>
    <div class="mb-2">
      <label class="form-label"><?= e($label) ?></label>
      <?php if ($current): ?>
        <div class="d-flex flex-wrap gap-2 mb-2">
          <?php foreach ($current as $img): ?>
            <label class="text-center" style="width:88px;">
              <span class="gd-image-preview d-flex mb-1" style="width:88px;height:88px;">
                <img src="<?= e(gd_img($img, $bucket)) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
              </span>
              <input type="checkbox" name="<?= e($prefix) ?>_keep[]" value="<?= e($img) ?>" checked>
              <span class="small text-muted d-block">keep</span>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <input type="file" class="form-control form-control-sm mb-2" name="<?= e($prefix) ?>_add[]" multiple accept="image/jpeg,image/png,image/webp,image/gif">
      <textarea class="form-control form-control-sm" name="<?= e($prefix) ?>_add_urls" rows="2" placeholder="or paste image URLs, one per line, to add more"></textarea>
    </div>
    <?php
}

function gd_image_field(string $name, string $bucket, string $current, string $label): void {
    $files = gd_media_list($bucket);
    $domId = 'img-' . preg_replace('/[^a-z0-9]+/i', '-', $name);
    ?>
    <div class="gd-image-field">
      <label class="form-label"><?= e($label) ?></label>
      <div class="gd-image-field-row">
        <div class="gd-image-preview">
          <?php if ($current !== ''): ?>
            <img src="<?= e(gd_img($current, $bucket)) ?>" alt="" id="<?= e($domId) ?>-preview">
          <?php else: ?>
            <span class="gd-image-preview-empty"><i class="bi bi-image"></i></span>
          <?php endif; ?>
        </div>
        <div class="gd-image-field-controls">
          <?php if ($files): ?>
            <select class="form-select form-select-sm mb-2" name="<?= e($name) ?>_existing">
              <option value="">— Choose existing file —</option>
              <?php foreach ($files as $f): ?>
                <option value="<?= e($f) ?>" <?= $f === $current ? 'selected' : '' ?>><?= e($f) ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
          <input type="file" class="form-control form-control-sm mb-2" name="<?= e($name) ?>" accept="image/jpeg,image/png,image/webp,image/gif">
          <input type="text" class="form-control form-control-sm" name="<?= e($name) ?>_url" placeholder="or paste an image URL"
                 value="<?= e(preg_match('#^https?://#i', $current) ? $current : '') ?>">
        </div>
      </div>
    </div>
    <?php
}
