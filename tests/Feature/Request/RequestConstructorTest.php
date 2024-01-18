<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Uri;
use org\bovigo\vfs\vfsStream;

\describe('Constructor of '.Request::class, function () {
    \it('success parameter body', function ($body, $contents) {
        \expect((string) (new Request(body: $body))->getBody())->toBe($contents);
    })
        ->with('message_body_success')
        ->with([
            'from resource' => [
                'body' => \fopen(vfsStream::newFile('f')->setContent('Virtual file!')->at(vfsStream::setup())->url(), 'rb'),
                'contents' => 'Virtual file!',
            ],
        ])
    ;

    \it('wrong parameter body', function ($body) {
        new Request(body: $body);
    })
        ->throws(InvalidArgumentException::class, 'Argument must be type "resource" or "string"')
        ->with('message_body_wrong')
    ;

    \it('Protocol version', function ($version) {
        \expect((new Request(protocolVersion: $version))->getProtocolVersion())->toBe($version);
    })
        ->with('protocol_success')
    ;

    \it('Protocol version wrong', function ($version) {
        new Request(protocolVersion: $version);
    })
        ->throws(InvalidArgumentException::class, 'Protocol must be implement')
        ->with('protocol_wrong')
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
})
    ->covers(Request::class, Stream::class, Message::class, Uri::class);
