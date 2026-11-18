<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

gd_run_simple_list([
    'key'       => 'services',
    'title'     => 'Services (overview cards)',
    'active'    => 'services',
    'itemLabel' => 'service',
    'hint'      => 'The short service cards on the Services page overview.',
    'columns'   => ['icon' => 'Icon', 'title' => 'Title', 'desc' => 'Description'],
    'fields'    => [
        'icon'  => ['label' => 'Bootstrap Icon class', 'type' => 'text', 'required' => true, 'maxlen' => 40, 'hint' => 'e.g. bi-tools — see icons.getbootstrap.com'],
        'title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'maxlen' => 40],
        'desc'  => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'maxlen' => 120],
    ],
]);
