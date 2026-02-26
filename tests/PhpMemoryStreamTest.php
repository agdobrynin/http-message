<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage;

use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpMemoryStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PhpMemoryStream::class)]
#[CoversClass(Stream::class)]
class PhpMemoryStreamTest extends TestCase
{
    public function testConstructor(): void
    {
        $stream = new PhpMemoryStream();

        self::assertEquals('php://memory', $stream->getMetadata('uri'));
    }
}
