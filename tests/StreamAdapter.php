<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage;

use Kaspi\HttpMessage\CreateResourceFromStringTrait;
use Kaspi\HttpMessage\Stream;
use Psr\Http\Message\StreamInterface;

class StreamAdapter
{
    use CreateResourceFromStringTrait;

    public static function make(string $body = ''): StreamInterface
    {
        return new Stream(self::resourceFromString($body, 'php://memory'));
    }
}
