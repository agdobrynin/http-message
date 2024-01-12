<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Unit\Stream;

use Kaspi\HttpMessage\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Stream::class)]
class StreamTest extends TestCase
{
    public function testDestructor(): void
    {
        $handle = \fopen('php://temp', 'r');
        $stream = (new Stream($handle));
        unset($stream);

        $this->assertFalse(\is_resource($handle));
    }

    public function testStreamConstructorParameterIsResource(): void
    {
        $stream = new Stream(\fopen('php://memory', 'r+b'));

        $this->assertEquals('php://memory', $stream->getMetadata('uri'));
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isSeekable());
        $this->assertIsArray($stream->getMetadata());
        $this->assertFalse($stream->eof());

        $stream->write('привет'); // UTF-8

        $this->assertEquals(12, $stream->tell());
        $this->assertEquals(12, $stream->getSize());
        $this->assertFalse($stream->eof());
        $this->assertEquals('привет', (string) $stream);

        $stream->close();

        // When close stream
        $this->assertEquals([], $stream->getMetadata());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isSeekable());
    }

    public function testStreamFromString(): void
    {
        $stream = new Stream('Hello world!'.PHP_EOL.'--'.PHP_EOL);

        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));

        $this->assertEquals("Hello world!\n--\n", $stream->getContents());
        $this->assertTrue($stream->eof());

        $this->assertEquals("Hello world!\n--\n", (string) $stream);
        $this->assertEquals("Hello world!\n--\n", $stream->__toString());

        $stream->close();
    }

    public function testToStringAndSeekMoveToZero(): void
    {
        $stream = new Stream(\fopen('php://memory', 'r+b'));
        $stream->write('Hello');

        $this->assertEquals('Hello', (string) $stream);
        $this->assertEquals('Hello', (string) $stream);

        $stream->close();
    }

    public static function dataStreamFromNonValidArgument(): \Generator
    {
        yield 'from object' => ['arg' => (object) []];

        yield 'from array' => ['arg' => []];

        yield 'from std class' => ['arg' => new \stdClass()];

        yield 'from boolean' => ['arg' => true];

        yield 'from null' => ['arg' => null];
    }

    /**
     * @dataProvider dataStreamFromNonValidArgument
     */
    public function testStreamFromNonValidArgument(mixed $arg): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Stream($arg);
    }

    public function testSeekRewindEofRead(): void
    {
        $stream = new Stream('hello');

        $this->assertFalse($stream->eof());

        $this->assertEquals('hello', $stream->read(6));
        $this->assertTrue($stream->eof());

        $stream->rewind();

        $this->assertFalse($stream->eof());

        $stream->seek(2);

        $this->assertEquals('llo', $stream->read(100));

        $stream->close();
    }

    public function testSeekNegative(): void
    {
        $stream = new Stream('hello');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot search for position');

        $stream->seek(-1);

        $stream->close();
    }

    public function testGetSize(): void
    {
        $size = \filesize(__FILE__);
        $handle = \fopen(__FILE__, 'rb');

        $stream = new Stream($handle);
        $this->assertEquals($size, $stream->getSize());

        $stream->close();
    }

    public function testReCalcSize(): void
    {
        $r = \fopen('php://temp', 'wb+');
        \fwrite($r, 'hello');

        $stream = new Stream($r);

        $this->assertEquals(5, $stream->getSize());

        $this->assertEquals(5, $stream->write('world'));

        $this->assertEquals(10, $stream->getSize());

        $this->assertEquals(1, $stream->write('!'));

        $this->assertEquals(11, $stream->getSize());

        $stream->close();
    }

    public function testTell(): void
    {
        $r = \fopen('php://memory', 'r+b');

        $stream = new Stream($r);

        $this->assertEquals(0, $stream->tell());

        $stream->write('foo');

        $this->assertEquals(3, $stream->tell());

        $stream->seek(1);

        $this->assertEquals(1, $stream->tell());

        $this->assertSame(\ftell($r), $stream->tell());

        $stream->close();
    }

    public function testDetach(): void
    {
        $stream = new Stream('abc');
        $resource = $stream->detach();

        $this->assertIsResource($resource);
        $this->assertEquals('abc', \fread($resource, 3));

        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertNull($stream->getSize());
        $this->assertEmpty($stream->getMetadata());

        $this->assertNull($stream->detach());

        $stream->close();
    }

    public static function dataExceptionStreamUndefined(): \Generator
    {
        yield 'tell' => ['stream' => new Stream(''), 'method' => 'tell'];

        yield 'seek' => ['stream' => new Stream(''), 'method' => 'seek', 'args' => [0]];

        yield 'write' => ['stream' => new Stream(''), 'method' => 'write', 'args' => ['abc']];

        yield 'read' => ['stream' => new Stream(''), 'method' => 'read', 'args' => [1]];

        yield 'getContents' => ['stream' => new Stream(''), 'method' => 'getContents'];
    }

    /**
     * @dataProvider dataExceptionStreamUndefined
     */
    public function testExceptionStreamUndefined(Stream $stream, string $method, array $args = []): void
    {
        $stream->close();

        $this->expectException(\RuntimeException::class);
        $stream->{$method}(...$args);

        $stream->close();
    }

    public function testReadableDeny(): void
    {
        $r = \fopen(\tempnam('x', 'streamTest'), 'cb');
        $stream = new Stream($r);
        $stream->write('a');

        $this->assertFalse($stream->isReadable());
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');

        $this->assertFalse($stream->read(1));

        $stream->close();
    }

    public function testWritableDeny(): void
    {
        $r = \fopen(\tempnam('x', 'streamTest'), 'rb');
        $stream = new Stream($r);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable');

        $stream->write('a');

        $stream->close();
    }

    public static function dataTellSeekWriteReadIsFalse(): \Generator
    {
        yield 'seek' => ['method' => 'seek', 'args' => [1]];

        yield 'write' => ['method' => 'write', 'args' => ['a']];

        yield 'read' => ['method' => 'read', 'args' => [1]];
    }

    /**
     * @dataProvider dataTellSeekWriteReadIsFalse
     */
    public function testTellSeekWriteReadIsFalse(string $method, array $args = []): void
    {
        \stream_wrapper_register('kaspi', TestStream::class);
        $resource = \fopen('kaspi://', 'wb+');

        $stream = new Stream($resource);
        $this->expectException(\RuntimeException::class);

        $stream->{$method}(...$args);

        \stream_wrapper_unregister('kaspi');
    }
}
