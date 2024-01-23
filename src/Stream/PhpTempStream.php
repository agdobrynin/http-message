<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage\Stream;

use Kaspi\HttpMessage\Stream;
use RuntimeException;

use function error_get_last;
use function fopen;

class PhpTempStream extends Stream
{
    /**
     * @param int $maxMemory will use a temporary file once the amount of data stored hits a predefined limit (the default is 2 MB)
     */
    public function __construct(string $mode = 'rb+', int $maxMemory = 2097152)
    {
        $src = 'php://temp/maxmemory:'.$maxMemory;

        if (($r = @fopen($src, $mode)) === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Cannot open from '.$src.' ['.(error_get_last()['message'] ?? '').']');
            // @codeCoverageIgnoreEnd
        }

        parent::__construct($r);
    }
}
