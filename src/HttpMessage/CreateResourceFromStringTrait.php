<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

trait CreateResourceFromStringTrait
{
    private static function resourceFromString(string $body, string $fileName = 'php://temp', string $mode = 'r+b')
    {
        $resource = \fopen($fileName, $mode) ?: throw new \RuntimeException("Cannot open stream [{$fileName}]");
        \fwrite($resource, $body);
        \fseek($resource, 0);

        return $resource;
    }
}
