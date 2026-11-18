<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

gd_run_simple_list([
    'key'       => 'certifications',
    'title'     => 'Certifications / Trust Badges',
    'active'    => 'certifications',
    'itemLabel' => 'certification',
    'hint'      => 'Empty by design until a real, verifiable credential is ready — an unverified badge is worse than none. Only add one you can back up on request.',
    'columns'   => ['logo' => 'Logo', 'name' => 'Name'],
    'fields'    => [
        'name' => ['label' => 'Certification Name', 'type' => 'text', 'required' => true, 'maxlen' => 60],
        'logo' => ['label' => 'Logo', 'type' => 'image', 'bucket' => '', 'required' => true],
    ],
]);
