<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage;

use Generator;
use Kaspi\HttpMessage\Uri;

final class Dataset
{
    public static function httpFactoryRequest(): Generator
    {
        yield 'set #1' => [
            'POST',
            '',
            '',
        ];

        yield 'set #2' => [
            'GET',
            new Uri('https://php.org:443/index.php'),
            'https://php.org/index.php',
        ];
    }

    public static function httpFactoryServerRequest(): Generator
    {
        yield 'set #1' => [
            'POST',
            '',
            [],
            '',
        ];

        yield 'set #2' => [
            'GET',
            new Uri('https://php.org:443/index.php'),
            ['test1', 'test2' => ['list', 'info']],
            'https://php.org/index.php',
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
