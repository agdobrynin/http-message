<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Stream;
use Tests\Feature\Stream\TestStream;

\describe('Tests for '.Stream::class, function () {
    \it('Destructor unset related resource', function () {
        $handle = \fopen('php://temp', 'r');
        $stream = (new Stream($handle));
        unset($stream);

        \expect(\is_resource($handle))->toBeFalse();
    });

    \it('Stream constructor parameter is resource', function () {
        $stream = new Stream(\fopen('php://memory', 'r+b'));

        \expect($stream->getMetadata('uri'))->toBe('php://memory')
            ->and($stream->isWritable())->toBeTrue()
            ->and($stream->isReadable())->toBeTrue()
            ->and($stream->isSeekable())->toBeTrue()
            ->and($stream->getMetadata())->toBeArray()
            ->and($stream->eof())->toBeFalse()
        ;

        $stream->write('привет'); // UTF-8

        \expect($stream->tell())->toBe(12)
            ->and($stream->getSize())->toBe(12)
            ->and($stream->eof())->toBeFalse()
            ->and((string) $stream)->toBe('привет')
        ;

        $stream->close();

        // When close stream
        \expect($stream->getMetadata())->toBeArray()
            ->and($stream->isWritable())->toBeFalse()
            ->and($stream->isReadable())->toBeFalse()
            ->and($stream->isSeekable())->toBeFalse()
        ;
    });

    \it('Create Stream from string', function () {
        $stream = new Stream('Hello world!'.PHP_EOL.'--'.PHP_EOL);

        \expect($stream->isWritable())->toBeTrue()
            ->and($stream->isReadable())->toBeTrue()
            ->and($stream->isSeekable())->toBeTrue()
            ->and($stream->getMetadata())->toBeArray()
            ->and($stream->eof())->toBeFalse()
            ->and($stream->getMetadata('uri'))->toBe('php://temp')
            ->and($stream->getContents())->toBe("Hello world!\n--\n")
            ->and($stream->eof())->toBeTrue()
        ;

        \expect((string) $stream)->toBe("Hello world!\n--\n")
            ->and($stream->__toString())->toBe("Hello world!\n--\n")
        ;

        $stream->close();
    });

    \it('Convert to string and seek to zero position', function () {
        $stream = new Stream(\fopen('php://memory', 'r+b'));
        $stream->write('Hello');

        \expect((string) $stream)->toBe('Hello')
            ->and((string) $stream)->toBe('Hello')
        ;

        $stream->close();
    });

    \it('Create stream from non valid argument', function (mixed $arg) {
        new Stream($arg);
    })
        ->throws(InvalidArgumentException::class)
        ->with([
            'from object' => ['arg' => (object) []],
            'from array' => ['arg' => []],
            'from std class' => ['arg' => new stdClass()],
            'from boolean' => ['arg' => true],
            'from null' => ['arg' => null],
        ])
    ;

    \it('Stream test methods Seek, Rewind, Eof, Read', function () {
        $stream = new Stream('hello');

        \expect($stream->eof())->toBeFalse()
            ->and($stream->read(6))->toBe('hello')
            ->and($stream->eof())->toBeTrue()
        ;

        $stream->rewind();

        \expect($stream->eof())->toBeFalse();

        $stream->seek(2);

        \expect($stream->read(100))->toBe('llo');

        $stream->close();
    });

    \it('Seek as negative value', function () {
        $stream = new Stream('hello');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot search for position');

        $stream->seek(-1);

        $stream->close();
    });

    \it('Stream method getSize', function () {
        $size = \filesize(__FILE__);
        $handle = \fopen(__FILE__, 'rb');

        $stream = new Stream($handle);

        \expect($stream->getSize())->toBe($size)
            // read again size from cached value.
            ->and($stream->getSize())->toBe($size)
        ;

        $stream->close();
    });

    \it('Stream recalculate size', function () {
        $r = \fopen('php://temp', 'wb+');
        \fwrite($r, 'hello');

        $stream = new Stream($r);

        \expect($stream->getSize())->toBe(5)
            ->and($stream->write('world'))->toBe(5)
            ->and($stream->getSize())->toBe(10)
            ->and($stream->write('!'))->toBe(1)
            ->and($stream->getSize())->toBe(11)
        ;

        $stream->close();
    });

    \it('Stream method Tell', function () {
        $r = \fopen('php://memory', 'r+b');

        $stream = new Stream($r);

        \expect($stream->tell())->toBe(0)
            ->and($stream->write('foo'))->toBe(3)
            ->and($stream->tell())->toBe(3)
        ;

        $stream->seek(1);

        \expect($stream->tell())->toBe(1);
        \expect($stream->tell())->toBe(\ftell($r));

        $stream->close();
    });

    \it('Stream method Detach', function () {
        $stream = new Stream('abc');
        $resource = $stream->detach();

        \expect($resource)->toBeResource()
            ->and(\fread($resource, 3))->toBe('abc')
        ;

        \expect($stream->isSeekable())->toBeFalse()
            ->and($stream->isReadable())->toBeFalse()
            ->and($stream->isWritable())->toBeFalse()
            ->and($stream->getSize())->toBeNull()
            ->and($stream->getMetadata())->toBeEmpty()
            ->and($stream->detach())->toBeNull()
        ;

        $stream->close();
    });

    \it('Stream undefined and has exception', function (Stream $stream, string $method, array $args = []) {
        $stream->close();

        $this->expectException(RuntimeException::class);
        $stream->{$method}(...$args);
    })
        ->throws(RuntimeException::class)
        ->with([
            'tell' => ['stream' => new Stream(''), 'method' => 'tell'],
            'seek' => ['stream' => new Stream(''), 'method' => 'seek', 'args' => [0]],
            'write' => ['stream' => new Stream(''), 'method' => 'write', 'args' => ['abc']],
            'read' => ['stream' => new Stream(''), 'method' => 'read', 'args' => [1]],
            'getContents' => ['stream' => new Stream(''), 'method' => 'getContents'],
        ])
    ;

    \describe('Stream is non readable, non writable', function () {
        \beforeEach(function () {
            $this->tmpFile = \stream_get_meta_data(\tmpfile())['uri'];
            \touch($this->tmpFile);
        });

        \afterEach(function () {
            @\unlink($this->tmpFile);
        });

        \it('Stream is non readable', function () {
            $stream = new Stream(\fopen($this->tmpFile, 'cb'));
            $stream->write('a');

            \expect($stream->isReadable())->toBeFalse();
            $stream->read(1);
        })->throws(RuntimeException::class, 'Stream is not readable');

        \it('Stream is non writable', function () {
            $stream = new Stream(\fopen($this->tmpFile, 'rb'));
            $stream->write('a');
        })->throws(RuntimeException::class, 'Stream is not writable');
    });

    \describe('Custom protocol for Stram', function () {
        \beforeEach(fn () => \stream_wrapper_register('kaspi', TestStream::class));

        \afterEach(fn () => \stream_wrapper_unregister('kaspi'));

        \it('Stream kaspi://', function (string $method, array $args = []) {
            $resource = \fopen('kaspi://', 'wb+');

            $stream = new Stream($resource);
            $stream->{$method}(...$args);
        })
            ->throws(RuntimeException::class)
            ->with([
                'non seekable' => ['method' => 'seek', 'args' => [1]],
                'non writable' => ['method' => 'write', 'args' => ['a']],
                'non readable' => ['method' => 'read', 'args' => [1]],
            ])
        ;
    });
})
    ->covers(Stream::class)
;
