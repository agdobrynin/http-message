<?php

declare(strict_types=1);

use Kaspi\HttpMessage\CreateStreamFromStringTrait;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\FileStream;
use Kaspi\HttpMessage\Stream\PhpMemoryStream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use org\bovigo\vfs\vfsStream;
use Psr\Http\Message\StreamInterface;

\describe('Test for '.CreateStreamFromStringTrait::class, function () {
    \it('Success create', function (string $content, callable $streamResolver, $uri) {
        $resolver = new class($streamResolver) {
            use CreateStreamFromStringTrait;

            public function __construct(private $streamResolver) {}

            public function make(string $content): StreamInterface
            {
                return $this->streamFromString($content);
            }
        };

        $stream = $resolver->make($content);
        \expect($stream->getContents())->toBe($content)
            ->and($stream->getMetadata('uri'))->toStartWith($uri)
        ;
    })
        ->with([
            'in php temporary file' => [
                'content' => 'Hello world',
                'streamResolver' => fn () => new PhpTempStream(),
                'uri' => 'php://temp/maxmemory:',
            ],
            'in memory' => [
                'content' => 'Hello world',
                'streamResolver' => fn () => new PhpMemoryStream(),
                'uri' => 'php://memory',
            ],
            'file in virtual file system' => [
                'content' => 'Hello world',
                'streamResolver' => fn () => new FileStream(vfsStream::newFile('f')->at(vfsStream::setup())->url(), 'r+b'),
                'uri' => 'vfs://root/f',
            ],
        ])
    ;

    \it('fail', function (callable $streamResolver) {
        \set_error_handler(static fn () => false);

        $class = new class($streamResolver) {
            use CreateStreamFromStringTrait;

            public function __construct(private $streamResolver) {}

            public function make(): void
            {
                $this->streamFromString('');
            }
        };

        $class->make();
    })
        ->throws(RuntimeException::class)
        ->with([
            'set write to HTTP' => [
                'streamResolver' => fn () => new FileStream('http://0.0.0.0', 'w+'),
            ],
            'set read from undefined file' => [
                'streamResolver' => fn () => new FileStream('/tmp/'.\uniqid('x', true), 'rb'),
            ],
            'set stream resolver as simple class' => [
                'streamResolver' => fn () => new stdClass(),
            ],
            'set stream resolver string' => [
                'streamResolver' => fn () => 'ok',
            ],
        ])
    ;

    \it('Stream resolver not defined', function () {
        $class = new class() {
            use CreateStreamFromStringTrait;

            public function make()
            {
                return $this->streamFromString('');
            }
        };

        \expect($class->make())->toBeInstanceOf(StreamInterface::class);
    });
})
    ->covers(CreateStreamFromStringTrait::class, Stream::class, PhpTempStream::class, PhpMemoryStream::class, FileStream::class)
;
