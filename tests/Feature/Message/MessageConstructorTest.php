<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use org\bovigo\vfs\vfsStream;
use Psr\Http\Message\StreamInterface;

\describe('Message constructor of '.Message::class, function () {
    \it('Empty constructor', function () {
        \expect($m = new Message())
            ->and($m->getBody())->toBeInstanceOf(StreamInterface::class)
            ->and($m->getBody()->getSize())->toBe(0)
            ->and((string) $m->getBody())->toBe('')
            ->and($m->getProtocolVersion())->toBe('1.1')
            ->and($m->getHeaders())->toBe([])
        ;
    });

    \it('Body', function ($body, $contents) {
        \expect((string) (new Message(body: $body))->getBody())->toBe($contents);
    })
        ->with('message_body_success')
        ->with([
            'from resource' => [
                'body' => \fopen(vfsStream::newFile('f')->setContent('Virtual file!')->at(vfsStream::setup())->url(), 'rb'),
                'contents' => 'Virtual file!',
            ],
        ])
    ;

    \it('Body wrong type', function ($body) {
        new Message(body: $body);
    })
        ->throws(InvalidArgumentException::class, 'Argument must be type "resource" or "string"')
        ->with('message_body_wrong')
    ;

    \it('Protocol version', function ($version) {
        \expect((new Message(protocolVersion: $version))->getProtocolVersion())->toBe($version);
    })
        ->with('protocol_success')
    ;

    \it('Protocol version wrong', function ($version) {
        new Message(protocolVersion: $version);
    })
        ->throws(InvalidArgumentException::class, 'Protocol must be implement')
        ->with('protocol_wrong')
    ;

    \it('Headers success', function ($headers, $expectHeaders) {
        \expect((new Message(headers: $headers))->getHeaders())->toBe($expectHeaders);
    })
        ->with('headers_success')
    ;

    \it('Headers wrong', function ($headers, $exceptionMessage) {
        $this->expectExceptionMessage($exceptionMessage);
        new Message(headers: $headers);
    })
        ->throws(InvalidArgumentException::class)
        ->with('headers_wrong')
    ;
})->covers(Message::class, Stream::class);
