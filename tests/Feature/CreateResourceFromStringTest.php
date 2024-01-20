<?php

declare(strict_types=1);

use Kaspi\HttpMessage\CreateResourceFromStringTrait;
use Kaspi\HttpMessage\Stream;
use org\bovigo\vfs\vfsStream;

\describe('Test for '.CreateResourceFromStringTrait::class, function () {
    \beforeEach(function () {
        $this->mockClass = new class() {
            use CreateResourceFromStringTrait;

            public function make(string $content, string $file, string $mode)
            {
                return self::resourceFromString($content, $file, $mode);
            }
        };
    });

    \it('Success create', function (string $content, string $file, string $mode) {
        $stream = new Stream($this->mockClass->make($content, $file, $mode));

        \expect($stream->getContents())->toBe($content)
            ->and($stream->getMetadata('uri'))->toBe($file)
        ;
    })
        ->with([
            'in php temporary file' => [
                'content' => 'Hello world',
                'file' => 'php://temp',
                'mode' => 'rb+',
            ],
            'in memory' => [
                'content' => 'Hello world',
                'file' => 'php://memory',
                'mode' => 'rb+',
            ],
            'file in virtual file system' => [
                'content' => 'Hello world',
                'file' => vfsStream::newFile('f')->at(vfsStream::setup())->url(),
                'mode' => 'rb+',
            ],
        ])
    ;

    \it('fail', function ($file, $mode) {
        \set_error_handler(static fn () => false);

        new class($file, $mode) {
            use CreateResourceFromStringTrait;

            public function __construct($file, $mode)
            {
                \var_dump(self::resourceFromString('ok', $file, $mode));
            }
        };
    })
        ->throws(RuntimeException::class)
        ->with([
            'set write to HTTP' => [
                'file' => 'http://0.0.0.0',
                'mode' => 'w+',
            ],
            'set read from undefined file' => [
                'file' => '/tmp/'.\uniqid('x', true),
                'mode' => 'rb',
            ],
        ])
    ;
})
    ->covers(CreateResourceFromStringTrait::class, Stream::class);
