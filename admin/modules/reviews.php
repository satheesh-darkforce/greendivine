<?php
require __DIR__ . '/../inc/guard.php';
require __DIR__ . '/../inc/simple-list.php';

$productOptions = ['' => '— General / site-wide —'];
foreach ($DATA['products'] as $p) $productOptions[$p['slug']] = $p['name'];

gd_run_simple_list([
    'key'       => 'reviews',
    'title'     => 'Reviews',
    'active'    => 'reviews',
    'itemLabel' => 'review',
    'hint'      => 'Testimonials shown site-wide and on matching product pages. Leave Product blank for a general/site-wide review.',
    'columns'   => ['name' => 'Name', 'location' => 'Location', 'rating' => 'Rating', 'product' => 'Product', 'date' => 'Date'],
    'fields'    => [
        'name'     => ['label' => 'Name', 'type' => 'text', 'required' => true, 'maxlen' => 60],
        'location' => ['label' => 'Location', 'type' => 'text', 'required' => true, 'maxlen' => 60],
        'rating'   => ['label' => 'Rating (1-5)', 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 5],
        'product'  => ['label' => 'Product', 'type' => 'select', 'options' => $productOptions],
        'date'     => ['label' => 'Date', 'type' => 'text', 'required' => true, 'hint' => 'YYYY-MM-DD'],
        'avatar'   => ['label' => 'Avatar', 'type' => 'image', 'bucket' => 'avatars', 'hint' => 'Optional — falls back to an initials circle if left blank.'],
        'body'     => ['label' => 'Review Text', 'type' => 'textarea', 'required' => true, 'maxlen' => 400],
    ],
    'extraValidate' => function (V $v, array $input) {
        if (!empty($input['date']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['date'])) {
            $v->fail('date', 'Date must be in YYYY-MM-DD format.');
        } elseif (!empty($input['date']) && $input['date'] > date('Y-m-d')) {
            $v->fail('date', 'Date cannot be in the future.');
        }
        return $v;
    },
]);
