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
    private static string $defaultResourceFilename = 'php://temp';
    private static string $defaultResourceModeOpen = 'r+b';

    /**
     * @return resource
     */
    private static function resourceFromString(string $body, ?string $fileName = null, ?string $mode = null)
    {
        $resource = ($r = @fopen($fileName ?: self::$defaultResourceFilename, $mode ?: self::$defaultResourceModeOpen)) !== false
            ? $r
            : throw new RuntimeException('Cannot open from '.$fileName.' ['.(error_get_last()['message'] ?? '').']');
        fwrite($resource, $body);
        fseek($resource, 0);

        return $resource;
    }
}
