<?php

declare(strict_types=1);

\dataset('headers_success', [
    'set # 1' => [
        ['content-type' => ['plain/text', 'undefined-type']],
        ['content-type' => ['plain/text', 'undefined-type']],
    ],

    'set # 2' => [
        ['content-type' => 'undefined-type'],
        ['content-type' => ['undefined-type']],
    ],

    'set # 3' => [
        [1234 => 'is-numeric'],
        ['1234' => ['is-numeric']],
    ],

    'set # 4' => [
        [0 => 'zero'],
        ['0' => ['zero']],
    ],
]);

\dataset('headers_wrong', [
    'set # 1' => [
        ['content type' => ['plain/text', 'undefined-type']],
        'Header name must be RFC 7230 compatible',
    ],
    'set # 2' => [
        ['❤' => ['plain/text', 'undefined-type']],
        'Header name must be RFC 7230 compatible',
    ],
    'set # 3' => [
        ['[ok]' => ['plain/text', 'undefined-type']],
        'Header name must be RFC 7230 compatible',
    ],
    'set # 4' => [
        ['файл' => ['plain/text', 'undefined-type']],
        'Header name must be RFC 7230 compatible',
    ],
    'set # 5' => [
        ['content-type' => (object) ['v' => 1]],
        'Header value must be RFC 7230 compatible',
    ],
    'set # 6' => [
        ['content-type' => [['v' => 1]]],
        'Header value must be RFC 7230 compatible',
    ],
    'set # 7' => [
        ['content-type' => \chr(8)],
        'Header value must be RFC 7230 compatible',
    ],
]);

\dataset('headers_with_uri', [
    'set #1 has URI and Host has in headers' => [
        'https://php.org/index.php?q=abc',
        [
            'expire' => 'today',
            'cache-control' => ['public', 'max-age=14400'],
            'Host' => 'www.demos.su',
        ],
        [
            'expire' => ['today'],
            'cache-control' => ['public', 'max-age=14400'],
            'Host' => ['www.demos.su'],
        ],
    ],
    'set #2 with empty URI Host header not modified' => [
        '',
        [
            'expire' => 'today',
            'cache-control' => ['public', 'max-age=14400'],
            'Host' => 'www.demos.su',
        ],
        [
            'expire' => ['today'],
            'cache-control' => ['public', 'max-age=14400'],
            'Host' => ['www.demos.su'],
        ],
    ],
    'set #3 has URI but not has header HOST' => [
        'https://php.org/index.php?q=abc',
        [
            'expire' => 'today',
            'cache-control' => ['public', 'max-age=14400'],
        ],
        [
            'Host' => ['php.org'],
            'expire' => ['today'],
            'cache-control' => ['public', 'max-age=14400'],
        ],
    ],
]);
