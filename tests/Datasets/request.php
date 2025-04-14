<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\dataset(
    'http_factory_request',
    [
        'set #1' => [
            'POST',
            '',
            '',
        ],
        'set #2' => [
            'GET',
            new Uri('https://php.org:443/index.php'),
            'https://php.org/index.php',
        ],
    ]
);

\dataset(
    'http_factory_server_request',
    [
        'set #1' => [
            'POST',
            '',
            [],
            '',
        ],
        'set #2' => [
            'GET',
            new Uri('https://php.org:443/index.php'),
            ['test1', 'test2' => ['list', 'info']],
            'https://php.org/index.php',
        ],
    ]
);
