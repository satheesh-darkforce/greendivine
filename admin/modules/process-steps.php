<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

gd_run_simple_list([
    'key'        => 'process',
    'title'      => 'Process Steps',
    'active'     => 'process-steps',
    'itemLabel'  => 'step',
    'hint'       => 'The shared "How It Works" 4-step stepper (Home, About, Services). Always exactly 4 steps — the layout is built for 4.',
    'fixedCount' => 4,
    'columns'    => ['icon' => 'Icon', 'title' => 'Title', 'desc' => 'Description'],
    'fields'     => [
        'icon'  => ['label' => 'Bootstrap Icon class', 'type' => 'text', 'required' => true, 'maxlen' => 40, 'hint' => 'e.g. bi-telephone-fill'],
        'title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'maxlen' => 30],
        'desc'  => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'maxlen' => 100],
    ],
]);
