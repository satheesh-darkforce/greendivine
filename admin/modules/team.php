<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

gd_run_simple_list([
    'key'       => 'team',
    'title'     => 'Team',
    'active'    => 'team',
    'itemLabel' => 'team member',
    'hint'      => 'The About page team grid. No photo field by design — an initials avatar is shown until real staff photos are ready.',
    'columns'   => ['name' => 'Name', 'role' => 'Role', 'bio' => 'Bio'],
    'fields'    => [
        'name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'maxlen' => 60],
        'role' => ['label' => 'Role', 'type' => 'text', 'required' => true, 'maxlen' => 60],
        'bio'  => ['label' => 'Bio', 'type' => 'textarea', 'required' => true, 'maxlen' => 120],
    ],
]);
