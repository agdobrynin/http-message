<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage;

use Generator;

use function chr;

class DatasetHeaders
{
    public static function headersSuccess(): Generator
    {
        yield 'set # 1' => [
            ['content-type' => ['plain/text', 'undefined-type']],
            ['content-type' => ['plain/text', 'undefined-type']],
        ];

        yield 'set # 2' => [
            ['content-type' => 'undefined-type'],
            ['content-type' => ['undefined-type']],
        ];

        yield 'set # 3' => [
            [1234 => 'is-numeric'],
            ['1234' => ['is-numeric']],
        ];

        yield 'set # 4' => [
            [0 => 'zero'],
            ['0' => ['zero']],
        ];
    }

    public static function headersWrong(): Generator
    {
        yield 'set # 1' => [
            ['content type' => ['plain/text', 'undefined-type']],
            'Header name must be RFC 7230 compatible',
        ];

        yield 'set # 2' => [
            ['❤' => ['plain/text', 'undefined-type']],
            'Header name must be RFC 7230 compatible',
        ];

        yield 'set # 3' => [
            ['[ok]' => ['plain/text', 'undefined-type']],
            'Header name must be RFC 7230 compatible',
        ];

        yield 'set # 4' => [
            ['файл' => ['plain/text', 'undefined-type']],
            'Header name must be RFC 7230 compatible',
        ];

        yield 'set # 5' => [
            ['content-type' => (object) ['v' => 1]],
            'Header value must be RFC 7230 compatible',
        ];

        yield 'set # 6' => [
            ['content-type' => [['v' => 1]]],
            'Header value must be RFC 7230 compatible',
        ];

        yield 'set # 7' => [
            ['content-type' => chr(8)],
            'Header value must be RFC 7230 compatible',
        ];
    }

    public static function headersWithUri(): Generator
    {
        yield 'set #1 has URI and Host has in headers' => [
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
        ];

        yield 'set #2 with empty URI Host header not modified' => [
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
        ];

        yield 'set #3 has URI but not has header HOST' => [
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
        ];
    }

    public static function notCompatibleRFC7230(): Generator
    {
        yield 'header non ascii' => ['привет', null];

        yield 'header as emoji' => ['💛', ['ok']];

        yield 'value non valid x00' => ['h', [chr(0)]];

        yield 'value with ESC symbol' => ['h', chr(27)];

        yield 'value with bell symbol' => ['h', chr(07)];
    }

    public static function emptyValue(): Generator
    {
        yield 'empty header name' => ['', ['ok']];

        yield 'empty array in values' => ['ok', []];
    }
}
