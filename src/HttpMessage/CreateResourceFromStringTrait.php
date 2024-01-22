<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use RuntimeException;

use function error_get_last;
use function fopen;
use function fseek;
use function fwrite;

trait CreateResourceFromStringTrait
{
    /**
     * @return resource
     */
    private static function resourceFromString(string $body, string $fileName = 'php://temp', string $mode = 'r+b')
    {
        $resource = ($r = @fopen($fileName, $mode)) !== false
            ? $r
            : throw new RuntimeException('Cannot open from '.$fileName.' ['.(error_get_last()['message'] ?? '').']');
        fwrite($resource, $body);
        fseek($resource, 0);

        return $resource;
    }
}
