<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

gd_run_simple_list([
    'key'       => 'clients',
    'title'     => 'Clients / Marquee',
    'active'    => 'clients',
    'itemLabel' => 'client',
    'hint'      => 'Logos shown in the scrolling "Happy Clients" marquee.',
    'columns'   => ['logo' => 'Logo', 'name' => 'Name'],
    'fields'    => [
        'name' => ['label' => 'Client Name', 'type' => 'text', 'required' => true, 'maxlen' => 60],
        'logo' => ['label' => 'Logo', 'type' => 'image', 'bucket' => 'clients', 'required' => true],
    ],
]);
