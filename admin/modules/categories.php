<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

$reserved = ['products', 'contact', 'services', 'about', 'blogs', 'home', 'admin', 'product', 'blog', 'category'];

gd_run_simple_list([
    'key'       => 'categories',
    'title'     => 'Categories',
    'active'    => 'categories',
    'itemLabel' => 'category',
    'hint'      => 'Product categories — each becomes a public URL at /&lt;slug&gt;. Manage the matching landing page under Catalogue > Landing Pages.',
    'columns'   => ['image' => 'Image', 'name' => 'Name', 'slug' => 'Slug', 'desc' => 'Description'],
    'fields'    => [
        'slug'  => ['label' => 'Slug (URL)', 'type' => 'text', 'required' => true, 'maxlen' => 40, 'hint' => 'Lowercase letters, numbers and hyphens only, e.g. artificial-grass. Becomes the public URL /artificial-grass.'],
        'name'  => ['label' => 'Name', 'type' => 'text', 'required' => true, 'maxlen' => 40],
        'icon'  => ['label' => 'Bootstrap Icon class', 'type' => 'text', 'required' => true, 'maxlen' => 40, 'hint' => 'e.g. bi-grid-3x3 — see icons.getbootstrap.com'],
        'image' => ['label' => 'Image', 'type' => 'image', 'bucket' => 'categories', 'required' => true],
        'desc'  => ['label' => 'Short Description', 'type' => 'textarea', 'required' => true, 'maxlen' => 140, 'hint' => 'Keep it to one short line — shown on the category card.'],
    ],
    'extraValidate' => function (V $v, array $input) use ($reserved) {
        global $DATA;
        $slug = $input['slug'] ?? '';
        if ($slug === '') return $v;
        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
            $v->fail('slug', 'Slug may only contain lowercase letters, numbers and hyphens.');
            return $v;
        }
        if (in_array($slug, $reserved, true)) {
            $v->fail('slug', "\"$slug\" is a reserved word and can't be used as a category slug.");
            return $v;
        }
        $editingIndex = isset($_POST['gd_index']) && $_POST['gd_index'] !== 'new' ? (int)$_POST['gd_index'] : null;
        $others = [];
        foreach ($DATA['categories'] as $i => $c) {
            if ($i !== $editingIndex) $others[] = $c['slug'];
        }
        if (in_array($slug, $others, true)) {
            $v->fail('slug', "Slug \"$slug\" is already used by another category.");
        }
        return $v;
    },
    'canDelete' => function (array $record) {
        $count = count(gd_products_by_category($record['slug']));
        if ($count > 0) {
            return "{$count} product" . ($count === 1 ? '' : 's') . " still use this category — reassign them first (Catalogue > Products).";
        }
        return null;
    },
]);
