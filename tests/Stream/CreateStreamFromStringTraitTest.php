<?php

declare(strict_types=1);

namespace Stream;

use Kaspi\HttpMessage\CreateStreamFromStringTrait;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpMemoryStream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use stdClass;

/**
 * @internal
 */
#[CoversClass(CreateStreamFromStringTrait::class)]
#[CoversClass(Stream::class)]
#[CoversClass(PhpTempStream::class)]
#[CoversClass(PhpMemoryStream::class)]
class CreateStreamFromStringTraitTest extends TestCase
{
    public function testDefaultStream(): void
    {
        $r = new class {
            use CreateStreamFromStringTrait;
        };

        /** @var StreamInterface $s */
        $s = $r->streamFromString('hello world');

        self::assertEquals('hello world', (string) $s);
        self::assertStringStartsWith('php://temp/maxmemory:', $s->getMetadata('uri'));
    }

    public function testStreamMemory(): void
    {
        $r = new class {
            use CreateStreamFromStringTrait;
        };
        $r->setStreamResolver(static fn () => new PhpMemoryStream());

        /** @var StreamInterface $s */
        $s = $r->streamFromString('hello world');

        self::assertEquals('hello world', (string) $s);
        self::assertEquals('php://memory', $s->getMetadata('uri'));
    }

    public function testStreamResolverNotSupport(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream resolver must be implement '.StreamInterface::class);

        $r = new class {
            use CreateStreamFromStringTrait;
        };
        $r->setStreamResolver(static fn () => new stdClass());

        $r->streamFromString('hello world');
    }
}
