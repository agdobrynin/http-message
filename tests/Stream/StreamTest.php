<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Stream;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\CreateStreamFromStringTrait;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Tests\Kaspi\HttpMessage\StreamAdapter;

use function fclose;
use function filesize;
use function fopen;
use function fread;
use function ftell;
use function fwrite;
use function is_resource;

/**
 * @internal
 */
#[CoversClass(Stream::class)]
#[CoversClass(CreateStreamFromStringTrait::class)]
#[CoversClass(PhpTempStream::class)]
class StreamTest extends TestCase
{
    public function testDestructorUnsetRelatedResource(): void
    {
        $handle = fopen('php://temp', 'r');
        $stream = (new Stream($handle));
        unset($stream);

        self::assertFalse(is_resource($handle));
    }

    public function testClosedResource(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream not defined');

        $handle = fopen('php://memory', 'rb+');
        $stream = new Stream($handle);
        fclose($handle);
        $stream->getContents();
    }

    public function testStreamConstructorParameterIsResource(): void
    {
        $stream = new Stream(fopen('php://memory', 'r+b'));

        self::assertEquals('php://memory', $stream->getMetadata('uri'));
        self::assertTrue($stream->isWritable());
        self::assertTrue($stream->isReadable());
        self::assertTrue($stream->isSeekable());
        self::assertIsArray($stream->getMetadata());
        self::assertFalse($stream->eof());

        $stream->write('привет'); // UTF-8

        self::assertEquals(12, $stream->tell());
        self::assertEquals(12, $stream->getSize());
        self::assertFalse($stream->eof());
        self::assertEquals('привет', (string) $stream);

        $stream->close();

        self::assertIsArray($stream->getMetadata());
        self::assertFalse($stream->isWritable());
        self::assertFalse($stream->isReadable());
        self::assertFalse($stream->isSeekable());
    }

    public function testCreateStreamFromString(): void
    {
        $stream = StreamAdapter::make('Hello world!'.PHP_EOL.'--'.PHP_EOL);

        self::assertTrue($stream->isWritable());
        self::assertTrue($stream->isReadable());
        self::assertTrue($stream->isSeekable());
        self::assertIsArray($stream->getMetadata());
        self::assertFalse($stream->eof());
        self::assertEquals("Hello world!\n--\n", $stream->getContents());
        self::assertTrue($stream->eof());
        self::assertEquals("Hello world!\n--\n", (string) $stream);
        self::assertEquals("Hello world!\n--\n", $stream->__toString());

        $stream->close();
    }

    public function testConvertToStringAndSeekToZeroPosition(): void
    {
        $stream = new Stream(fopen('php://memory', 'r+b'));
        $stream->write('Hello');

        self::assertEquals('Hello', (string) $stream);
        // reset pointer to zero
        self::assertEquals('Hello', (string) $stream);

        $stream->close();
    }

    #[DataProvider('dataProviderInvalidArg')]
    public function testCreateStreamFromNonValidArgument(mixed $arg): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Stream($arg);
    }

    public static function dataProviderInvalidArg(): Generator
    {
        yield 'from object' => [(object) []];

        yield 'from array' => [[]];

        yield 'from std class' => [new stdClass()];

        yield 'from boolean' => [true];

        yield 'from null' => [null];
    }

    public function testSeekRewindEofRead(): void
    {
        $stream = StreamAdapter::make('hello');

        self::assertFalse($stream->eof());
        self::assertEquals('hello', $stream->read(6));
        self::assertTrue($stream->eof());

        $stream->rewind();

        self::assertFalse($stream->eof());

        $stream->seek(2);

        self::assertEquals('llo', $stream->read(100));

        $stream->close();
    }

    public function testSeekAsNegativeValue(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot search for position');

        $stream = StreamAdapter::make('hello');
        $stream->seek(-1);
        $stream->close();
    }

    public function testGetSize(): void
    {
        $size = filesize(__FILE__);
        $handle = fopen(__FILE__, 'rb');

        $stream = new Stream($handle);

        self::assertEquals($size, $stream->getSize());
        // read again size from cached value.
        self::assertEquals($size, $stream->getSize());

        $stream->close();
    }

    public function testRecalculateSize(): void
    {
        $r = fopen('php://temp', 'wb+');
        fwrite($r, 'hello');

        $stream = new Stream($r);

        self::assertEquals(5, $stream->getSize());
        self::assertEquals(5, $stream->write('world'));
        self::assertEquals(10, $stream->getSize());
        self::assertEquals(1, $stream->write('!'));
        self::assertEquals(11, $stream->getSize());

        $stream->close();
    }

    public function testTell(): void
    {
        $r = fopen('php://memory', 'r+b');

        $stream = new Stream($r);

        self::assertEquals(0, $stream->tell());
        self::assertEquals(3, $stream->write('foo'));
        self::assertEquals(3, $stream->tell());

        $stream->seek(1);

        self::assertEquals(1, $stream->tell());
        self::assertEquals(ftell($r), $stream->tell());

        $stream->close();
    }

    public function testDetach(): void
    {
        $stream = StreamAdapter::make('abc');
        $resource = $stream->detach();

        self::assertIsResource($resource);
        self::assertEquals('abc', fread($resource, 3));

        self::assertFalse($stream->isSeekable());
        self::assertFalse($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertNull($stream->getSize());
        self::assertEmpty($stream->getMetadata());
        self::assertNull($stream->detach());

        $stream->close();
    }

    #[DataProvider('dataProviderClosedStream')]
    public function testClosedStream(Stream $stream, string $method, array $args = []): void
    {
        $this->expectException(RuntimeException::class);

        $stream->close();
        $stream->{$method}(...$args);
    }

    public static function dataProviderClosedStream(): Generator
    {
        yield 'tell' => [StreamAdapter::make(''), 'tell'];

        yield 'seek' => [StreamAdapter::make(''), 'seek', [0]];

        yield 'write' => [StreamAdapter::make(''), 'write', ['abc']];

        yield 'read' => [StreamAdapter::make(''), 'read', [1]];

        yield 'getContents' => [StreamAdapter::make(''), 'getContents'];
    }

    public function testStreamIsNonReadable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');
        vfsStream::setup(structure: [
            'file.txt',
        ]);

        $stream = new Stream(fopen(vfsStream::url('root/file.txt'), 'cb'));
        $stream->write('a');

        self::assertFalse($stream->isReadable());

        $stream->read(1);
    }

    public function testStreamIsNonWritable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable');

        vfsStream::setup(structure: [
            'file.txt' => 'foo',
        ]);

        $stream = new Stream(fopen(vfsStream::url('root/file.txt'), 'rb'));
        $stream->write('a');
    }

    public function testCopyToStreamSuccess(): void
    {
        $stream = new Stream(fopen('php://memory', 'rb+'));
        $stream->write('Hello world 🤪');
        $stream->rewind();

        $streamTo = new PhpTempStream();
        $stream->copyToStream($streamTo);

        self::assertEquals('Hello world 🤪', (string) $streamTo);
    }

    public function testCopyToStreamFail(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot copy from');

        $stream = new Stream(fopen('php://memory', 'rb+'));
        $stream->write('Hello world 🤪');
        $stream->rewind();

        $streamTo = new PhpTempStream('rb');
        $stream->copyToStream($streamTo);
    }
}
