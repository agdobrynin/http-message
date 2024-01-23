<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage\Stream;

use Kaspi\HttpMessage\Stream;
use RuntimeException;
use Throwable;

use function error_get_last;
use function fopen;

class FileStream extends Stream
{
    public function __construct(string $filename, string $mode)
    {
        try {
            if (($resource = @fopen($filename, $mode)) === false) {
                throw new RuntimeException(error_get_last()['message'] ?? '');
            }

            parent::__construct($resource);
        } catch (Throwable $error) {
            throw new RuntimeException("Cannot create stream from {$filename} [{$error}]");
        }
    }
}
