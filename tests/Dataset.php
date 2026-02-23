<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage;

use Generator;
use Kaspi\HttpMessage\Uri;

final class Dataset
{
    public static function httpFactoryRequest(): Generator
    {
        yield __METHOD__.' set #1' => [
            'POST',
            '',
            '',
        ];

        yield __METHOD__.' set #2' => [
            'GET',
            new Uri('https://php.org:443/index.php'),
            'https://php.org/index.php',
        ];
    }
}
