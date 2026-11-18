<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/singleton-form.php';

gd_run_singleton([
    'key'    => 'quote_popup',
    'title'  => 'Quote Popup',
    'active' => 'quote-popup',
    'hint'   => 'The centered "Get a Free Quote" modal shown once per visitor on every page.',
    'groups' => [
        [
            'label'  => 'Popup Content',
            'fields' => [
                'enabled' => ['label' => 'Show the popup', 'type' => 'toggle'],
                'delay'   => ['label' => 'Delay before showing (seconds)', 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 120],
                'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text', 'required' => true, 'maxlen' => 60],
                'title'   => ['label' => 'Title', 'type' => 'text', 'required' => true, 'maxlen' => 80],
                'sub'     => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'maxlen' => 200],
                'cta'     => ['label' => 'Button Label', 'type' => 'text', 'required' => true, 'maxlen' => 40],
            ],
        ],
    ],
]);
