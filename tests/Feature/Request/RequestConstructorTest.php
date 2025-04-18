<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use Kaspi\HttpMessage\Uri;
use org\bovigo\vfs\vfsStream;

\describe('Constructor of '.Request::class, function () {
    \it('success parameter body', function ($body, $contents) {
        \expect((string) (new Request(body: $body))->getBody())->toBe($contents);
    })
        ->with('message_body_success')
        ->with([
            'from resource' => [
                new Stream(\fopen(vfsStream::newFile('f')->setContent('Virtual file!')->at(vfsStream::setup())->url(), 'rb')),
                'Virtual file!',
            ],
        ])
    ;

    \it('wrong parameter body', function ($body) {
        new Request(body: $body);
    })
        ->throws(TypeError::class)
        ->with('message_body_wrong')
    ;

    \it('Protocol version', function ($version) {
        \expect((new Request(protocolVersion: $version))->getProtocolVersion())->toBe($version);
    })
        ->with('protocol_success')
    ;

    \it('Success headers', function ($headers, $expectHeaders) {
        \expect((new Request(headers: $headers))->getHeaders())->toBe($expectHeaders);
    })
        ->with('headers_success')
    ;

    \it('Wrong headers', function ($headers, $exceptionMessage) {
        $this->expectExceptionMessage($exceptionMessage);
        new Request(headers: $headers);
    })
        ->throws(InvalidArgumentException::class)
        ->with('headers_wrong')
    ;

    \it('Success parameters Uri', function ($uri, $uriExpect) {
        \expect((string) (new Request(uri: $uri))->getUri())->toBe($uriExpect);
    })
        ->with('uri_success')
    ;
})
    ->covers(Request::class, Stream::class, Message::class, Uri::class, PhpTempStream::class)
;
