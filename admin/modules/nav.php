<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

$validRoutes = array_merge(
    ['home', 'products', 'services', 'about', 'blogs', 'contact'],
    array_column($DATA['categories'], 'slug')
);

gd_run_simple_list([
    'key'       => 'nav',
    'title'     => 'Navigation Menu',
    'active'    => 'nav',
    'itemLabel' => 'menu item',
    'hint'      => 'The header + mobile menu links, in order.',
    'columns'   => ['label' => 'Label', 'href' => 'URL Path', 'route' => 'Route Key'],
    'fields'    => [
        'label' => ['label' => 'Label', 'type' => 'text', 'required' => true, 'maxlen' => 20],
        'href'  => ['label' => 'URL Path', 'type' => 'text', 'required' => true, 'hint' => 'Must start with /, e.g. /products'],
        'route' => ['label' => 'Route Key', 'type' => 'text', 'required' => true,
                     'hint' => 'One of: ' . implode(', ', $validRoutes)],
    ],
    'extraValidate' => function (V $v, array $input) use ($validRoutes) {
        if (($input['href'] ?? '') !== '' && !str_starts_with($input['href'], '/')) {
            $v->fail('href', 'URL Path must start with /.');
        }
        if (($input['route'] ?? '') !== '' && !in_array($input['route'], $validRoutes, true)) {
            $v->fail('route', 'Route Key must match an existing route.');
        }
        return $v;
    },
]);
