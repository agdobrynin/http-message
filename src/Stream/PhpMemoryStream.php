<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage\Stream;

use Kaspi\HttpMessage\Stream;
use RuntimeException;

use function error_get_last;
use function fopen;

class PhpMemoryStream extends Stream
{
    public function __construct(string $mode = 'rb+')
    {
        $src = 'php://memory';

        if (($r = @fopen($src, $mode)) === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Cannot open from '.$src.' ['.(error_get_last()['message'] ?? '').']');
            // @codeCoverageIgnoreEnd
        }

        parent::__construct($r);
    }
}
