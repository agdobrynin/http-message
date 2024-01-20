<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\dataset(
    'http_factory_request',
    [
        'set #1' => [
            'method' => 'POST',
            'uri' => '',
            'expectUri' => '',
        ],
        'set #2' => [
            'method' => 'GET',
            'uri' => new Uri('https://php.org:443/index.php'),
            'expectUri' => 'https://php.org/index.php',
        ],
    ]
);

\dataset(
    'http_factory_server_request',
    [
        'set #1' => [
            'method' => 'POST',
            'uri' => '',
            'srvParams' => [],
            'expectUri' => '',
        ],
        'set #2' => [
            'method' => 'GET',
            'uri' => new Uri('https://php.org:443/index.php'),
            'srvParams' => ['test1', 'test2' => ['list', 'info']],
            'expectUri' => 'https://php.org/index.php',
        ],
    ]
);
