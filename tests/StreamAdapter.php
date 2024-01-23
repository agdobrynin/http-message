<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage;

use Kaspi\HttpMessage\CreateStreamFromStringTrait;
use Kaspi\HttpMessage\Stream;
use Psr\Http\Message\StreamInterface;

use function fopen;

class StreamAdapter
{
    use CreateStreamFromStringTrait;

    public static function make(string $body = ''): StreamInterface
    {
        return self::streamFromString($body, fn () => new Stream(fopen('php://memory', 'rb+')));
    }
}
