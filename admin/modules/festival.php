<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/singleton-form.php';

gd_run_singleton([
    'key'    => 'festival',
    'title'  => 'Festival Banner',
    'active' => 'festival',
    'hint'   => 'Shown when the hero style is set to "4 — Festival" (Settings > Hero Layout).',
    'groups' => [
        [
            'label'  => 'Offer Content',
            'fields' => [
                'eyebrow'   => ['label' => 'Eyebrow', 'type' => 'text', 'required' => true, 'maxlen' => 60],
                'title'     => ['label' => 'Title', 'type' => 'text', 'required' => true, 'maxlen' => 80],
                'highlight' => ['label' => 'Highlight (e.g. "Up to 25% OFF")', 'type' => 'text', 'required' => true, 'maxlen' => 40],
                'sub'       => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'maxlen' => 200],
                'coupon'    => ['label' => 'Coupon Code', 'type' => 'text', 'required' => true, 'maxlen' => 30],
                'valid'     => ['label' => 'Validity Text (e.g. "Offer valid till 15 Nov")', 'type' => 'text', 'required' => true, 'maxlen' => 60],
                'image'     => ['label' => 'Banner Image', 'type' => 'image', 'bucket' => 'installations', 'required' => true],
            ],
        ],
        [
            'label'  => 'Primary Button',
            'fields' => [
                'cta.label' => ['label' => 'Button Label', 'type' => 'text', 'required' => true, 'maxlen' => 40],
                'cta.href'  => ['label' => 'Button Link', 'type' => 'url', 'required' => true, 'hint' => 'An internal path like /contact, or a full https:// URL.'],
            ],
        ],
        [
            'label'  => 'Secondary Button',
            'hint'   => 'Leave the link blank to fall back to the site\'s WhatsApp number automatically.',
            'fields' => [
                'cta2.label' => ['label' => 'Button Label', 'type' => 'text', 'required' => true, 'maxlen' => 40],
                'cta2.href'  => ['label' => 'Button Link (optional)', 'type' => 'url'],
            ],
        ],
    ],
]);
