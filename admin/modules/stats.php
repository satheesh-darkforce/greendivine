<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

gd_run_simple_list([
    'key'        => 'stats',
    'title'      => 'Stats / Counters',
    'active'     => 'stats',
    'itemLabel'  => 'stat',
    'hint'       => 'The animated counters on Home + About. Always exactly 4 — the layout is a 2x2 / 4-across grid.',
    'fixedCount' => 4,
    'columns'    => ['number' => 'Number', 'suffix' => 'Suffix', 'label' => 'Label'],
    'fields'     => [
        'number' => ['label' => 'Number', 'type' => 'number', 'required' => true, 'min' => 0, 'max' => 1000000],
        'suffix' => ['label' => 'Suffix', 'type' => 'text', 'maxlen' => 3, 'hint' => 'e.g. + or yr — 3 characters max'],
        'label'  => ['label' => 'Label', 'type' => 'text', 'required' => true, 'maxlen' => 30],
    ],
]);
