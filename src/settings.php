<?php
return [
    'environment' => getenv('ENVIRONMENT'),
    'displayErrorDetails' => getenv('DISPLAY_ERROR_DETAILS'),

    // Renderer settings
    'renderer' => [
        'template_path' => __DIR__ . '/../templates/',
    ],

    // Monolog settings
    'logger' => [
        'name' => 'jackmarchant',
        'path' => __DIR__ . '/../logs/app.log',
    ],
];
