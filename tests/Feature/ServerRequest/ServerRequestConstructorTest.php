<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use Kaspi\HttpMessage\Uri;

\describe('Constructor of '.ServerRequest::class, function () {
    \it('URI', function ($uri, $uriExpect) {
        \expect((string) (new ServerRequest(uri: $uri))->getUri())->toBe($uriExpect);
    })
        ->with('uri_success')
    ;

    \it('success body', function ($body, $contents) {
        \expect((string) (new ServerRequest(body: $body))->getBody())->toBe($contents);
    })
        ->with('message_body_success')
    ;

    \it('fail body', function ($body) {
        new ServerRequest(body: $body);
    })
        ->throws(TypeError::class)
        ->with('message_body_wrong')
    ;

    \it('Headers success', function ($headers, $expectHeaders) {
        \expect((new ServerRequest(headers: $headers))->getHeaders())->toBe($expectHeaders);
    })
        ->with('headers_success')
    ;

    \it('Headers wrong', function ($headers, $exceptionMessage) {
        $this->expectExceptionMessage($exceptionMessage);
        new ServerRequest(headers: $headers);
    })
        ->throws(InvalidArgumentException::class)
        ->with('headers_wrong')
    ;

    \it('Headers with URI test Host header', function ($uri, $headers, $expectHeaders) {
        \expect((new ServerRequest(uri: $uri, headers: $headers))->getHeaders())->toBe($expectHeaders);
    })
        ->with('headers_with_uri')
    ;

    \it('Default protocol', function () {
        \expect((new ServerRequest())->getProtocolVersion())->toBe('1.1');
    });

    \it('Success protocol', function ($protocolVersion) {
        \expect((new ServerRequest(protocolVersion: $protocolVersion))->getProtocolVersion())->toBe($protocolVersion);
    })
        ->with('protocol_success')
    ;

    \it('Server parameters', function (array $params) {
        \expect((new ServerRequest(serverParams: $params))->getServerParams())->toBe($params);
    })
        ->with([
            'empty array' => ['params' => []],
            'has items' => ['params' => ['first' => 'aaa']],
        ])
    ;
})
    ->covers(ServerRequest::class, Message::class, Request::class, Stream::class, Uri::class, PhpTempStream::class)
;
