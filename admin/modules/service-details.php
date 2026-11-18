<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

gd_run_simple_list([
    'key'       => 'service_details',
    'title'     => 'Service Details',
    'active'    => 'services',
    'itemLabel' => 'service detail',
    'hint'      => 'The deep-dive blocks on the Services page ("A Closer Look at Each Service").',
    'columns'   => ['image' => 'Image', 'title' => 'Title', 'slug' => 'Slug'],
    'fields'    => [
        'slug'     => ['label' => 'Slug', 'type' => 'text', 'required' => true, 'maxlen' => 40, 'hint' => 'Internal identifier, lowercase-with-hyphens — not a public route.'],
        'icon'     => ['label' => 'Bootstrap Icon class', 'type' => 'text', 'required' => true, 'maxlen' => 40],
        'title'    => ['label' => 'Title', 'type' => 'text', 'required' => true, 'maxlen' => 60],
        'image'    => ['label' => 'Image', 'type' => 'image', 'bucket' => '', 'required' => true],
        'desc'     => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'maxlen' => 400],
        'features' => ['label' => 'Features', 'type' => 'lines', 'required' => true, 'minCount' => 1],
    ],
    'extraValidate' => function (V $v, array $input) {
        if (($input['slug'] ?? '') !== '' && !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $input['slug'])) {
            $v->fail('slug', 'Slug may only contain lowercase letters, numbers and hyphens.');
        }
        return $v;
    },
]);
