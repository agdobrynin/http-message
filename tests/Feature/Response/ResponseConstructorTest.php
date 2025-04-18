<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Response;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use Kaspi\HttpMessage\Uri;
use org\bovigo\vfs\vfsStream;

\describe('Constructor of '.Response::class, function () {
    \it('success parameter body', function ($body, $contents) {
        \expect((string) (new Response(body: $body))->getBody())->toBe($contents);
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
        new Response(body: $body);
    })
        ->throws(TypeError::class)
        ->with('message_body_wrong')
    ;

    \it('Protocol version', function ($version) {
        \expect((new Response(protocolVersion: $version))->getProtocolVersion())->toBe($version);
    })
        ->with('protocol_success')
    ;

    \it('Success headers', function ($headers, $expectHeaders) {
        \expect((new Response(headers: $headers))->getHeaders())->toBe($expectHeaders);
    })
        ->with('headers_success')
    ;

    \it('Wrong headers', function ($headers, $exceptionMessage) {
        $this->expectExceptionMessage($exceptionMessage);
        new Response(headers: $headers);
    })
        ->throws(InvalidArgumentException::class)
        ->with('headers_wrong')
    ;

    \it('Normalize reason phrase', function ($reasonPhrase, $expect) {
        \expect((new Response(reasonPhrase: $reasonPhrase))->getReasonPhrase())->toBe($expect);
    })
        ->with('reason_phrase_success')
    ;

    \it('Wrong reason phrase', function ($reasonPhrase) {
        new Response(reasonPhrase: $reasonPhrase);
    })
        ->throws(InvalidArgumentException::class)
        ->with('reason_phrase_fail')
    ;
})
    ->covers(Response::class, Stream::class, Message::class, Uri::class, PhpTempStream::class)
;
