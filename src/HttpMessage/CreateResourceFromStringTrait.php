<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

trait CreateResourceFromStringTrait
{
    private static function resourceFromString(string $body)
    {
        $resource = \fopen('php://temp', 'r+b') ?: throw new \RuntimeException('Cannot open stream [php://temp]');
        \fwrite($resource, $body);
        \fseek($resource, 0);

        return $resource;
    }
}
