<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/singleton-form.php';

$heroOptions = [
    '1' => '1 — Split (text + image, side-by-side)',
    '2' => '2 — Showcase (banner + overlapping card)',
    '3' => '3 — Immersive (full-bleed + frosted-glass card)',
    '4' => '4 — Festival (celebratory offer hero)',
];

gd_run_singleton([
    'key'    => 'site',
    'title'  => 'Site Settings',
    'active' => 'settings',
    'hint'   => 'Brand info, contact details, socials and homepage layout — used across every page.',
    'groups' => [
        [
            'label'  => 'Business Info',
            'fields' => [
                'name'           => ['label' => 'Site Name', 'type' => 'text', 'required' => true, 'maxlen' => 60],
                'tagline'        => ['label' => 'Tagline', 'type' => 'text', 'required' => true, 'maxlen' => 120],
                'location'       => ['label' => 'Location', 'type' => 'text', 'required' => true, 'maxlen' => 60],
                'phone'          => ['label' => 'Phone (tel: link, e.g. +919999999999)', 'type' => 'text', 'required' => true, 'maxlen' => 20],
                'phone_label'    => ['label' => 'Phone (display, e.g. +91 99999 99999)', 'type' => 'text', 'required' => true, 'maxlen' => 25],
                'whatsapp'       => ['label' => 'WhatsApp number (digits only, e.g. 919999999999)', 'type' => 'text', 'required' => true, 'maxlen' => 15],
                'email'          => ['label' => 'Email', 'type' => 'email', 'required' => true, 'maxlen' => 80],
                'address'        => ['label' => 'Address', 'type' => 'textarea', 'required' => true, 'maxlen' => 200],
                'business_hours' => ['label' => 'Business Hours', 'type' => 'text', 'required' => true, 'maxlen' => 60],
                'map_query'      => ['label' => 'Map Search Query', 'type' => 'text', 'required' => true, 'maxlen' => 100, 'hint' => 'Used to build the Google Maps embed on the Contact page, e.g. "Indiranagar, Bangalore".'],
            ],
        ],
        [
            'label'  => 'Social Links',
            'hint'   => 'Use # to hide a link visually while keeping the icon in place.',
            'fields' => [
                'socials.facebook'  => ['label' => 'Facebook URL', 'type' => 'url', 'maxlen' => 200],
                'socials.instagram' => ['label' => 'Instagram URL', 'type' => 'url', 'maxlen' => 200],
                'socials.youtube'   => ['label' => 'YouTube URL', 'type' => 'url', 'maxlen' => 200],
                'socials.linkedin'  => ['label' => 'LinkedIn URL', 'type' => 'url', 'maxlen' => 200],
            ],
        ],
        [
            'label'  => 'Brand & SEO',
            'key'    => 'config',
            'fields' => [
                'logo'                  => ['label' => 'Site Logo', 'type' => 'image', 'bucket' => '', 'required' => true],
                'og_default'            => ['label' => 'Default Social-Share Image', 'type' => 'image', 'bucket' => 'installations'],
                'google_sheet_endpoint' => ['label' => 'Google Sheet Webhook URL', 'type' => 'url', 'maxlen' => 300, 'hint' => 'Apps Script Web App URL that receives form submissions — see docs/google-sheets-setup.md. Leave blank to fall back to a mailto: link.'],
            ],
        ],
        [
            'label'  => 'Hero Layout',
            'key'    => 'config',
            'fields' => [
                'home_hero_variant'     => ['label' => 'Home Page Hero Style', 'type' => 'select', 'options' => $heroOptions, 'required' => true],
                'category_hero_variant' => ['label' => 'Category Page Hero Style (default)', 'type' => 'select', 'options' => $heroOptions, 'required' => true],
            ],
        ],
        [
            'label'  => 'Analytics',
            'key'    => 'config',
            'hint'   => 'Both optional — leave blank to keep the site tracking-free. Nothing loads unless an ID is set here.',
            'fields' => [
                'google_analytics_id' => ['label' => 'Google Analytics 4 Measurement ID', 'type' => 'text', 'maxlen' => 20, 'hint' => 'Looks like G-XXXXXXXXXX, from your GA4 property\'s Data Streams settings.'],
                'meta_pixel_id'       => ['label' => 'Meta (Facebook) Pixel ID', 'type' => 'text', 'maxlen' => 30, 'hint' => 'A numeric ID from Meta Events Manager.'],
            ],
        ],
    ],
    'extraValidate' => function (V $v, array $inputByKey) {
        $site = $inputByKey['site'] ?? [];
        if (!empty($site['whatsapp']) && !preg_match('/^[0-9]{10,15}$/', $site['whatsapp'])) {
            $v->fail('whatsapp', 'WhatsApp number must be 10-15 digits, no + or spaces.');
        }
        if (!empty($site['phone']) && !preg_match('/^\+?[0-9 ]{8,16}$/', $site['phone'])) {
            $v->fail('phone', 'Phone must contain only digits, spaces and an optional leading +.');
        }
        $config = $inputByKey['config'] ?? [];
        if (!empty($config['google_analytics_id']) && !preg_match('/^G-[A-Z0-9]{6,12}$/', $config['google_analytics_id'])) {
            $v->fail('google_analytics_id', 'Should look like G-XXXXXXXXXX.');
        }
        if (!empty($config['meta_pixel_id']) && !preg_match('/^\d{10,20}$/', $config['meta_pixel_id'])) {
            $v->fail('meta_pixel_id', 'Should be a numeric Pixel ID (10-20 digits).');
        }
        return $v;
    },
]);
