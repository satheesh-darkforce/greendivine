<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

gd_run_simple_list([
    'key'       => 'why_us',
    'title'     => 'Why Us (feature rows)',
    'active'    => 'why-us',
    'itemLabel' => 'feature',
    'hint'      => 'The About page "What sets us apart" rows (first 4 also appear in the Home page editorial section).',
    'columns'   => ['icon' => 'Icon', 'title' => 'Title', 'desc' => 'Description'],
    'fields'    => [
        'icon'  => ['label' => 'Bootstrap Icon class', 'type' => 'text', 'required' => true, 'maxlen' => 40, 'hint' => 'e.g. bi-award-fill'],
        'title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'maxlen' => 30],
        'desc'  => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'maxlen' => 100],
    ],
]);
