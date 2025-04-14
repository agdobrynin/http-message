<?php

declare(strict_types=1);

use Kaspi\HttpMessage\HttpFactory;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\Response;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\FileStream;
use Kaspi\HttpMessage\Stream\PhpMemoryStream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use Kaspi\HttpMessage\UploadedFile;
use Kaspi\HttpMessage\Uri;
use org\bovigo\vfs\vfsStream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Tests\Kaspi\HttpMessage\StreamAdapter;

\describe('Test for '.HttpFactory::class, function () {
    \describe('createRequest', function () {
        \it('method and URI', function ($method, $uri, $expectUri) {
            \expect($r = (new HttpFactory())->createRequest($method, $uri))
                ->toBeInstanceOf(RequestInterface::class)
                ->and($r->getMethod())->toBe($method)
                ->and((string) $r->getUri())->toBe($expectUri)
            ;
        })
            ->with('http_factory_request')
        ;
    })
        ->covers(HttpFactory::class, Message::class, Request::class, Stream::class, Uri::class)
    ;

    \describe('createResponse', function () {
        \it('http status code and response phrase', function (array $args, $expectCode, $expectPhrase) {
            \expect($r = (new HttpFactory())->createResponse(...$args))->toBeInstanceOf(ResponseInterface::class)
                ->and($r->getStatusCode())->toBe($expectCode)
                ->and($r->getReasonPhrase())->toBe($expectPhrase)
            ;
        })->with([
            'all default' => [
                [],
                200,
                'OK',
            ],
            'standard http status 404' => [
                [404],
                404,
                'Not Found',
            ],
            'standard http status 599' => [
                [599],
                599,
                '',
            ],
            'standard http status 511' => [
                [511],
                511,
                'Network Authentication Required',
            ],
            'standard http status and custom response phrase' => [
                [201, 'Account created success. You can login now.'],
                201,
                'Account created success. You can login now.',
            ],
        ]);
    })
        ->covers(Response::class)
    ;

    \describe('createServerRequest', function () {
        \it('with', function ($method, $uri, $srvParams, $expectUri) {
            \expect($s = (new HttpFactory())->createServerRequest($method, $uri, $srvParams))->toBeInstanceOf(ServerRequestInterface::class)
                ->and((string) $s->getUri())->toBe($expectUri)
                ->and($s->getMethod())->toBe($method)
                ->and($s->getServerParams())->toBe($srvParams)
            ;
        })->with('http_factory_server_request');
    })
        ->covers(ServerRequest::class)
    ;

    \it('createStream', function () {
        \expect($s = (new HttpFactory())->createStream('hello world'))->toBeInstanceOf(StreamInterface::class)
            ->and($s->getContents())->toBe('hello world')
        ;
    });

    \it('createStreamFromResource', function () {
        $f = vfsStream::newFile('i')->withContent('hello world')->at(vfsStream::setup());

        \expect($s = (new HttpFactory())->createStreamFromResource(\fopen($f->url(), 'rb')))->toBeInstanceOf(StreamInterface::class)
            ->and($s->getContents())->toBe('hello world')
        ;
    });

    \it('createUri', function ($uri, $expectUri) {
        \expect($u = (new HttpFactory())->createUri($uri))->toBeInstanceOf(UriInterface::class)
            ->and((string) $u)->toBe($expectUri)
        ;
    })
        ->with('uri_as_string')
    ;

    \describe('createStreamFromFile', function () {
        \it('success', function () {
            $f = vfsStream::newFile('i')->withContent('hello world')->at(vfsStream::setup());

            \expect($s = (new HttpFactory())->createStreamFromFile($f->url()))->toBeInstanceOf(StreamInterface::class)
                ->and((string) $s)->toBe('hello world')
            ;
        });

        \describe('fails', function () {
            \beforeEach(function () {
                $this->root = vfsStream::setup();
            });

            \afterEach(function () {
                \restore_error_handler();
            });

            \it('fail for read', function (string $file, string $mode, string $message) {
                \set_error_handler(static fn () => false);

                $this->expectExceptionMessage($message);

                (new HttpFactory())->createStreamFromFile($file, $mode);
            })
                ->throws(RuntimeException::class)
                ->with([
                    'empty name' => [
                        fn () => '',
                        'r',
                        'Path cannot',
                    ],
                    'file not found' => [
                        fn () => __DIR__.DIRECTORY_SEPARATOR.\uniqid('test'),
                        'rb',
                        'No such file or directory',
                    ],
                    'fail mode' => [
                        fn () => vfsStream::newFile('my.txt')->at($this->root)->url(),
                        'uyuyuyuyu',
                        'Failed to open stream',
                    ],
                    'mode cannot read stream' => [
                        fn () => vfsStream::newFile('my.txt', 0222)->at($this->root)->url(),
                        'rb',
                        'Failed to open stream',
                    ],
                    'mode write' => [
                        fn () => vfsStream::newFile('my.txt', 0444)->at($this->root)->url(),
                        'wb',
                        'Failed to open stream',
                    ],
                ])
            ;
        });
    })
        ->covers(Stream::class, PhpTempStream::class, FileStream::class, PhpMemoryStream::class)
    ;

    \it('createUploadedFile without size file', function ($stream, $size, $expectSize) {
        $uf = (new HttpFactory())->createUploadedFile(stream: $stream, size: $size);

        \expect($uf->getSize())->toBe($expectSize);
    })
        ->with([
            'null init size' => [
                StreamAdapter::make('file'),
                null,
                4,
            ],
            'init size' => [
                StreamAdapter::make('file'),
                10,
                10,
            ],
        ])
        ->covers(UploadedFile::class)
    ;
});
