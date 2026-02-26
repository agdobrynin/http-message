<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Stream;

use Generator;
use Kaspi\HttpMessage\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Kaspi\HttpMessage\Stream\Fixtures\TestStream;

use function fopen;
use function stream_wrapper_register;
use function stream_wrapper_unregister;

/**
 * @internal
 */
#[CoversClass(Stream::class)]
class CustomProtocolForStreamTest extends TestCase
{
    protected function setUp(): void
    {
        stream_wrapper_register('kaspi', TestStream::class);
    }

    public function tearDown(): void
    {
        stream_wrapper_unregister('kaspi');
    }

    #[DataProvider('dataCustomProtocolForStream')]
    public function testCustomProtocolForStream(string $method, array $args = []): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream(fopen('kaspi://', 'wb+'));
        $stream->{$method}(...$args);
    }

    public static function dataCustomProtocolForStream(): Generator
    {
        yield 'non seekable' => ['seek', [1]];

        yield 'non writable' => ['write', ['a']];

        yield 'non readable' => ['read', [1]];
    }
}
