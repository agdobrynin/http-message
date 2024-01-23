<?php

declare(strict_types=1);

use Tests\Kaspi\HttpMessage\StreamAdapter;

\dataset('message_body_success', [
    'from string' => [
        'body' => null,
        'contents' => '',
    ],
    'from StreamInterface' => [
        'body' => StreamAdapter::make('welcome to class'),
        'contents' => 'welcome to class',
    ],
]);

\dataset('message_body_wrong', [
    'object' => ['body' => (object) ['aaaa']],
    'float' => ['body' => 1.234],
    'int' => ['body' => 0xFF],
    'array' => ['body' => []],
    'class' => ['body' => new stdClass()],
]);
