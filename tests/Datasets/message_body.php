<?php

declare(strict_types=1);

use Tests\Kaspi\HttpMessage\StreamAdapter;

\dataset('message_body_success', [
    'from string' => [
        null,
        '',
    ],
    'from StreamInterface' => [
        StreamAdapter::make('welcome to class'),
        'welcome to class',
    ],
]);

\dataset('message_body_wrong', [
    [(object) ['aaaa']],
    [1.234],
    [0xFF],
    [[]],
    [new stdClass()],
]);
