<?php
use Illuminate\Support\Str;

return[
    'award' => [
        'max_file_size' => 1024,
        'accepted_file_types' => 'png',
        'save_width' => 200,
        'save_height' => 200,
        'save_as' => 'png',
        'save_path' => '/awards/',
        'disk' => 'public'
    ],

    'user' => [
        'max_file_size' => 1024,
        'accepted_file_types' => ["png", "jpg", "jpeg"],
        'save_width' => 400,
        'save_height' => 400,
        'save_as' => 'jpeg',
        'save_path' => '/users/',
        'disk' => 'public'
    ]
];
