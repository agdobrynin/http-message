<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;

\dataset('message_body_success', [
    'from string' => [
        'body' => 'hello',
        'contents' => 'hello',
    ],
    'from StreamInterface' => [new Stream('welcome to class'), 'welcome to class'],
]);

\dataset('message_body_wrong', [
    'object' => ['body' => (object) ['aaaa']],
    'float' => ['body' => 1.234],
    'int' => ['body' => 0xFF],
    'array' => ['body' => []],
    'class' => ['body' => new Message()],
]);
