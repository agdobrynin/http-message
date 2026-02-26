<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage;

use Generator;
use Kaspi\HttpMessage\Uri;

class DatasetUri
{
    public static function uriSuccess(): Generator
    {
        yield 'from string' => [
            'https://www.php.org/index.php?q=list&order=desc#fig1-6.1',
            'https://www.php.org/index.php?q=list&order=desc#fig1-6.1',
        ];

        yield 'from Uri' => [
            new Uri('https://www.php.org/index.php?q=list&order=desc#fig1-6.1'),
            'https://www.php.org/index.php?q=list&order=desc#fig1-6.1',
        ];

        yield 'from Uri components' => [
            (new Uri('/'))->withQuery('abc=2&lis[m')->withHost('php.net')->withPort(8080),
            '//php.net:8080/?abc=2&lis%5Bm',
        ];
    }

    public static function uriAsString(): Generator
    {
        yield 'set #1' => [
            'https://www.php.org/index.php?q=list&order=desc#fig1-6.1',
            'https://www.php.org/index.php?q=list&order=desc#fig1-6.1',
        ];

        yield 'set #2' => [
            'http://www.php.net:80/ind ex.php?q=list&abc=2&lis[m#fig1-6.1',
            'http://www.php.net/ind%20ex.php?q=list&abc=2&lis%5Bm#fig1-6.1',
        ];
    }
}
