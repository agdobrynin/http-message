<?php

declare(strict_types=1);

\dataset('headers_success', [
    'set # 1' => [
        'headers' => ['content-type' => ['plain/text', 'undefined-type']],
        'expectHeaders' => ['content-type' => ['plain/text', 'undefined-type']],
    ],

    'set # 2' => [
        'headers' => ['content-type' => 'undefined-type'],
        'expectHeaders' => ['content-type' => ['undefined-type']],
    ],

    'set # 3' => [
        'headers' => [1234 => 'is-numeric'],
        'expectHeaders' => ['1234' => ['is-numeric']],
    ],

    'set # 4' => [
        'headers' => [0 => 'zero'],
        'expectHeaders' => ['0' => ['zero']],
    ],
]);

\dataset('headers_wrong', [
    'set # 1' => [
        'headers' => ['content type' => ['plain/text', 'undefined-type']],
        'exceptionMessage' => 'Header name must be RFC 7230 compatible',
    ],
    'set # 2' => [
        'headers' => ['❤' => ['plain/text', 'undefined-type']],
        'exceptionMessage' => 'Header name must be RFC 7230 compatible',
    ],
    'set # 3' => [
        'headers' => ['[ok]' => ['plain/text', 'undefined-type']],
        'exceptionMessage' => 'Header name must be RFC 7230 compatible',
    ],
    'set # 4' => [
        'headers' => ['файл' => ['plain/text', 'undefined-type']],
        'exceptionMessage' => 'Header name must be RFC 7230 compatible',
    ],
    'set # 5' => [
        'headers' => ['content-type' => (object) ['v' => 1]],
        'exceptionMessage' => 'Header value must be RFC 7230 compatible',
    ],
    'set # 6' => [
        'headers' => ['content-type' => [['v' => 1]]],
        'exceptionMessage' => 'Header value must be RFC 7230 compatible',
    ],
    'set # 7' => [
        'headers' => ['content-type' => \chr(8)],
        'exceptionMessage' => 'Header value must be RFC 7230 compatible',
    ],
]);

\dataset('headers_with_uri', [
    'set #1 has URI and Host has in headers' => [
        'uri' => 'https://php.org/index.php?q=abc',
        'headers' => [
            'expire' => 'today',
            'cache-control' => ['public', 'max-age=14400'],
            'Host' => 'www.demos.su',
        ],
        'expectHeaders' => [
            'expire' => ['today'],
            'cache-control' => ['public', 'max-age=14400'],
            'Host' => ['www.demos.su'],
        ],
    ],
    'set #2 with empty URI Host header not modified' => [
        'uri' => '',
        'headers' => [
            'expire' => 'today',
            'cache-control' => ['public', 'max-age=14400'],
            'Host' => 'www.demos.su',
        ],
        'expectHeaders' => [
            'expire' => ['today'],
            'cache-control' => ['public', 'max-age=14400'],
            'Host' => ['www.demos.su'],
        ],
    ],
    'set #3 has URI but not has header HOST' => [
        'uri' => 'https://php.org/index.php?q=abc',
        'headers' => [
            'expire' => 'today',
            'cache-control' => ['public', 'max-age=14400'],
        ],
        'expectHeaders' => [
            'Host' => ['php.org'],
            'expire' => ['today'],
            'cache-control' => ['public', 'max-age=14400'],
        ],
    ],
]);
