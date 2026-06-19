<?php

declare(strict_types=1);

return [
    'pagination' => [
        'default' => 10,
        'options' => [10, 25, 50, 100],
        'max' => 100,
    ],

    'search' => [
        'debounce' => 300,
    ],

    'cache' => [
        'stats_ttl' => 300,
    ],

    'uploads' => [
        'client' => 'clients',
        'documents' => 'documents',
    ],
];
